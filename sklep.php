<?php
/**
 * sklep.php
 * Testowy sklep (0 zł) integrowany z VIshop/VLshop (symulacja) + RCON do nadawania rangi (LuckPerms addtemp)
 * 
 * Instrukcja:
 * - W tym pliku uzupełnij sekcję CONFIG (najniżej) danymi: rcon_host, rcon_port, rcon_password, vlshop_api_key
 * - Dla produkcji przenieś dane do config.php i nie trzymaj ich publicznie (/.htaccess / dostęp tylko z domeny itp.).
 * - Ten skrypt dla cen 0 zł omija bramkę płatności (pomocne do testów) i od razu nadaje rangę przez RCON.
 * - Jeśli chcesz aktywować prawdziwe płatności: zaimplementuj createVlshopPayment() zgodnie z dokumentacją twojego itemshopu.
 *
 * Bezpieczeństwo: sprawdź firewall i ustawienia RCON - serwer musi akceptować połączenia RCON z hosta, na którym stoi strona.
 *
 * Źródła / referencje: RCON (Source RCON protocol) - https://developer.valvesoftware.com/wiki/Source_RCON_Protocol
 * VIshop (przykładowy itemshop) - https://wiki.vishop.pl/pay/
 */

session_start();

// -----------------------------
// --- KONFIGURACJA (Uzupełnij) ---
// -----------------------------
// Dla bezpieczeństwa przenieś te ustawienia do config.php lub zmiennych środowiskowych.
$rcon_host = '83.168.106.251';        // <- TUTAJ WPISZ SWÓJ IP (lub host)
$rcon_port = 25575;                   // <- PORT RCON
$rcon_password = 'CreaMC99';// <- TU HASŁO RCON
$vlshop_api_key = 'X625eoARGh1fT3Bq1vZREJZXeN1Hh1HX5sxUPt6SFBGyb1uZ'; // <- TU KLUCZ API VL/VIshop jeżeli chcesz korzystać z rzeczywistej bramki
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$transactions_file = __DIR__ . '/data/transactions.json';

// Upewnij się, że katalog data/ istnieje i jest zapisywalny przez PHP (chmod 755/775 zależnie od hostingu)
if (!is_dir(__DIR__ . '/data')) {
    @mkdir(__DIR__ . '/data', 0755, true);
}
if (!file_exists($transactions_file)) {
    file_put_contents($transactions_file, json_encode([]));
}

// -----------------------------
// --- PROSTE FUNKCJE POMOCNICZE ---
// -----------------------------
function save_transaction($obj) {
    global $transactions_file;
    $all = json_decode(file_get_contents($transactions_file), true);
    if (!is_array($all)) $all = [];
    $all[] = $obj;
    file_put_contents($transactions_file, json_encode($all, JSON_PRETTY_PRINT));
}

function validate_nick($nick) {
    // Prostą walidację nicku Minecraft (albo dopasuj do swoich reguł)
    return preg_match('/^[A-Za-z0-9_]{2,16}$/', $nick);
}

// -----------------------------
// --- RCON - klasa (Source RCON) ---
// -----------------------------
class RconClient {
    private $socket;
    private $host;
    private $port;
    private $password;
    private $timeout;
    const SERVERDATA_AUTH = 3;
    const SERVERDATA_EXECCOMMAND = 2;
    const SERVERDATA_RESPONSE_VALUE = 0;

    public function __construct($host, $port, $password, $timeout = 3) {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
        $this->timeout = $timeout;
    }

    public function connect() {
        $this->socket = @fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
        if (!$this->socket) {
            throw new Exception("RCON: nie udało się połączyć: {$errstr} ({$errno})");
        }
        stream_set_timeout($this->socket, $this->timeout);
        stream_set_blocking($this->socket, true);

        // autoryzacja
        $this->sendPacket(1, self::SERVERDATA_AUTH, $this->password);
        // serwer wysyła zwykle pusty response value, a potem auth response
        $this->readPacket();
        $auth = $this->readPacket();
        if ($auth === false) throw new Exception('RCON: brak odpowiedzi podczas autoryzacji');
        if ($auth['id'] == -1) throw new Exception('RCON: autoryzacja nieudana (złe hasło)');
        return true;
    }

    private function sendPacket($id, $type, $payload) {
        // Packet: size (int32 little-endian), id (int32 LE), type (int32 LE), payload (ASCIIZ), empty string (ASCIIZ)
        $payload = (string)$payload;
        $packet = pack('V', strlen($payload) + 10) . pack('V', $id) . pack('V', $type) . $payload . "\x00\x00";
        fwrite($this->socket, $packet);
    }

    private function readPacket() {
        $len_raw = fread($this->socket, 4);
        if ($len_raw === false || strlen($len_raw) < 4) return false;
        $arr = unpack('Vsize', $len_raw);
        $size = $arr['size'];
        $data = '';
        $read = 0;
        while ($read < $size) {
            $chunk = fread($this->socket, $size - $read);
            if ($chunk === false || $chunk === '') break;
            $data .= $chunk;
            $read += strlen($chunk);
        }
        if (strlen($data) < 8) return false;
        $header = substr($data, 0, 8);
        $body = substr($data, 8, $size - 10); // minus two null bytes
        $h = unpack('Vid/Vtype', $header);
        return ['id' => $h['id'], 'type' => $h['type'], 'body' => $body];
    }

    public function sendCommand($cmd) {
        $this->sendPacket(2, self::SERVERDATA_EXECCOMMAND, $cmd);
        // odpowiedź może przyjść w kilku paczkach - prosty odczyt pierwszej
        $res = $this->readPacket();
        return $res ? $res['body'] : false;
    }

    public function close() {
        if (is_resource($this->socket)) fclose($this->socket);
        $this->socket = null;
    }
}

// -----------------------------
// --- Obsługa POST (kupno) ---
// -----------------------------
$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'buy') {
    $nick = isset($_POST['nick']) ? trim($_POST['nick']) : '';
    $duration = isset($_POST['duration']) ? intval($_POST['duration']) : 0; // dni

    $allowed = [7, 14, 31];
    if (!in_array($duration, $allowed)) {
        $flash = 'Nieprawidłowy okres.';
    } elseif (!validate_nick($nick)) {
        $flash = 'Nieprawidłowy nick. Dozwolone: litery, cyfry, podkreślenie, 2-16 znaków.';
    } else {
        // dla testu mamy 0 zł -> od razu potwierdzamy
        $price = 0.0;
        $order_id = uniqid('order_');
        $transaction = [
            'order_id' => $order_id,
            'nick' => $nick,
            'duration_days' => $duration,
            'price' => $price,
            'status' => 'pending',
            'created_at' => date('c')
        ];
        save_transaction($transaction);

        // jeśli cena == 0 -> przyjmujemy jako zapłacone (TEST)
        $paid = false;
        if ($price == 0.0) {
            $paid = true;
        } else {
            // TODO: tutaj stwórz płatność przez VL/VIshop i poczekaj na callback
            // przykładowo: createVlshopPayment($order_id, $price, $nick, ...)
        }

        if ($paid) {
            // wykonaj RCON i nadaj grupę tymczasowo
            $cmd = "lp user " . escapeshellarg($nick) . " parent addtemp vip {$duration}d";
            // uwaga: escapeshellarg dodaje apostrofy - niektóre serwery oczekują bez nich; poniżej używamy bezpośredniego wstrzyknięcia po walidacji
            $cmd = "lp user {$nick} parent addtemp vip {$duration}d";

            try {
                $r = new RconClient($rcon_host, $rcon_port, $rcon_password, 3);
                $r->connect();
                $resp = $r->sendCommand($cmd);
                $r->close();

                $transaction['status'] = 'completed';
                $transaction['rcon_response'] = $resp;
                $transaction['completed_at'] = date('c');
                save_transaction($transaction);

                $flash = "Sukces: przydzielono VIP na {$duration} dni graczowi {$nick}.";
            } catch (Exception $e) {
                $transaction['status'] = 'rcon_failed';
                $transaction['error'] = $e->getMessage();
                save_transaction($transaction);
                $flash = 'Błąd RCON: ' . htmlspecialchars($e->getMessage());
            }
        } else {
            $flash = 'Zainicjowano płatność. Oczekiwanie na potwierdzenie...';
        }
    }
}

// -----------------------------
// --- Prosty interfejs sklepu (HTML) ---
// -----------------------------
?>
<!doctype html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sklep - VIP (test)</title>
<style>
/* Prosty styl - dopasuj do swojego index.php jeśli chcesz */
body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f8;color:#222;margin:0;padding:20px}
.container{max-width:980px;margin:0 auto}
.header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px}
.card{background:#fff;border-radius:8px;padding:18px;box-shadow:0 6px 18px rgba(16,24,40,0.06);margin-bottom:12px}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px}
.product-title{font-size:18px;font-weight:700}
.btn{display:inline-block;padding:10px 14px;border-radius:8px;text-decoration:none;border:none;background:#2563eb;color:#fff;cursor:pointer}
.input,select{padding:8px;border-radius:6px;border:1px solid #ddd;width:100%;box-sizing:border-box}
.small{font-size:13px;color:#555}
.flash{margin:12px 0;padding:10px;border-radius:6px;background:#eef2ff;color:#1e3a8a}
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Sklep - VIP</h1>
        <div class="small">Testowy sklep - płatności 0 zł</div>
    </div>

    <?php if ($flash): ?>
        <div class="flash"><?=htmlspecialchars($flash)?></div>
    <?php endif; ?>

    <div class="card">
        <form id="buyForm" method="post">
            <input type="hidden" name="action" value="buy">
            <div style="display:flex;gap:12px;align-items:flex-start;flex-wrap:wrap">
                <div style="flex:1;min-width:240px">
                    <label class="small">Nick gracza (Minecraft)</label>
                    <input class="input" type="text" name="nick" placeholder="TwójNick" required pattern="[A-Za-z0-9_]{2,16}">
                </div>
                <div style="width:200px">
                    <label class="small">Okres</label>
                    <select name="duration" class="input">
                        <option value="7">VIP - 7 dni (0 zł)</option>
                        <option value="14">VIP - 14 dni (0 zł)</option>
                        <option value="31">VIP - 31 dni (0 zł)</option>
                    </select>
                </div>
                <div style="align-self:flex-end">
                    <button class="btn" type="submit">Kup teraz (test)</button>
                </div>
            </div>
        </form>
    </div>

    <div class="grid">
        <div class="card">
            <div class="product-title">VIP - 7 dni</div>
            <div class="small">Cena: 0 zł - Test</div>
            <p class="small">Nadaje rangę VIP na 7 dni.</p>
        </div>
        <div class="card">
            <div class="product-title">VIP - 14 dni</div>
            <div class="small">Cena: 0 zł - Test</div>
            <p class="small">Nadaje rangę VIP na 14 dni.</p>
        </div>
        <div class="card">
            <div class="product-title">VIP - 31 dni</div>
            <div class="small">Cena: 0 zł - Test</div>
            <p class="small">Nadaje rangę VIP na 31 dni.</p>
        </div>
    </div>

    <hr style="margin:20px 0">
    <div class="card">
        <h3>Log transakcji (ostatnie 20)</h3>
        <pre style="white-space:pre-wrap;max-height:300px;overflow:auto;background:#f7fafc;padding:12px;border-radius:6px;border:1px solid #eee"><?php
            $data = json_decode(file_get_contents($transactions_file), true);
            if (!is_array($data)) $data = [];
            $last = array_slice($data, -20);
            echo htmlspecialchars(json_encode($last, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        ?></pre>
    </div>

    <p class="small">Uwaga: dla produkcji przenieś dane konfiguracyjne do pliku konfiguracyjnego i zabezpiecz dostęp do tego pliku (.htaccess lub inny mechanizm autoryzacji).</p>
</div>
</body>
</html>
