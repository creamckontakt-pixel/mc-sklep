<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CreaMC.pl - Regulamin</title>
    <link rel="icon" type="image/png" sizes="32x32" href="logo.png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: #0a0a12;
            color: #fff;
            overflow-x: hidden;
            background-image: 
                radial-gradient(circle at 20% 20%, rgba(255, 105, 180, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(138, 43, 226, 0.1) 0%, transparent 50%);
        }

        /* SIDEBAR NAVIGATION - IDENTYCZNIE JAK W INDEX.PHP */
        .sidebar {
            position: fixed;
            top: 0;
            left: -320px;
            width: 300px;
            height: 100vh;
            background: linear-gradient(180deg, rgba(30, 30, 45, 0.98), rgba(20, 20, 35, 0.98));
            backdrop-filter: blur(30px);
            border-right: 3px solid;
            border-image: linear-gradient(180deg, #ff69b4, #8a2be2) 1;
            z-index: 1000;
            transition: all 0.7s cubic-bezier(0.175, 0.885, 0.32, 1.4);
            box-shadow: 
                15px 0 40px rgba(0, 0, 0, 0.6),
                inset -8px 0 25px rgba(255, 105, 180, 0.15);
            display: flex;
            flex-direction: column;
            padding: 60px 0 40px;
            overflow-y: auto;
        }

        .sidebar.open {
            left: 0;
        }

        /* PRZYCISK MENU */
        .menu-toggle {
            position: fixed;
            top: 25px;
            left: 25px;
            width: 55px;
            height: 55px;
            background: rgba(30, 30, 45, 0.9);
            backdrop-filter: blur(20px);
            border: 2px solid rgba(255, 105, 180, 0.6);
            border-radius: 12px;
            cursor: pointer;
            z-index: 1001;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 5px;
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 
                0 8px 25px rgba(255, 105, 180, 0.3),
                inset 0 1px 10px rgba(255, 255, 255, 0.1);
        }

        .menu-toggle:hover {
            transform: scale(1.1);
            border-color: rgba(255, 105, 180, 0.9);
            background: rgba(255, 105, 180, 0.1);
            box-shadow: 
                0 12px 35px rgba(255, 105, 180, 0.5),
                inset 0 1px 15px rgba(255, 255, 255, 0.2);
        }

        .menu-toggle span {
            display: block;
            width: 20px;
            height: 2px;
            background: linear-gradient(90deg, #ff69b4, #8a2be2);
            border-radius: 2px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
        }

        .menu-toggle span:nth-child(1) {
            transform-origin: center;
        }

        .menu-toggle span:nth-child(2) {
            width: 16px;
            opacity: 1;
        }

        .menu-toggle span:nth-child(3) {
            transform-origin: center;
        }

        .menu-toggle.open span:nth-child(1) {
            transform: rotate(45deg) translate(6px, 6px);
            width: 20px;
        }

        .menu-toggle.open span:nth-child(2) {
            opacity: 0;
            transform: scale(0);
        }

        .menu-toggle.open span:nth-child(3) {
            transform: rotate(-45deg) translate(6px, -6px);
            width: 20px;
        }

        /* LOGO W SIDEBAR */
        .sidebar-logo {
            text-align: center;
            padding: 0 40px 40px;
            margin-bottom: 30px;
            position: relative;
        }

        .sidebar-logo::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80%;
            height: 3px;
            background: linear-gradient(90deg, transparent, #ff69b4, #8a2be2, transparent);
            border-radius: 3px;
        }

        .sidebar-logo img {
            height: 70px;
            width: auto;
            filter: drop-shadow(0 0 25px rgba(255, 105, 180, 0.8));
            transition: all 0.5s ease;
            margin: 0 auto;
            display: block;
        }

        .sidebar-logo img:hover {
            transform: scale(1.15) rotate(-5deg);
            filter: drop-shadow(0 0 35px rgba(255, 105, 180, 1));
        }

        /* LINKI W SIDEBAR */
        .sidebar-links {
            list-style: none;
            padding: 0 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .sidebar-link {
            margin-bottom: 25px;
            text-align: center;
        }

        .sidebar-link a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            text-decoration: none;
            font-weight: 700;
            font-size: 17px;
            padding: 20px 35px;
            border-radius: 20px;
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            border: 2px solid transparent;
            min-width: 200px;
            background: transparent;
        }

        .sidebar-link a::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, 
                transparent, 
                rgba(255, 105, 180, 0.4), 
                rgba(138, 43, 226, 0.4),
                transparent);
            transition: left 0.8s ease;
            border-radius: 20px;
        }

        .sidebar-link a::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            width: 0;
            height: 70%;
            background: linear-gradient(180deg, #ff69b4, #8a2be2);
            border-radius: 10px;
            transition: all 0.5s ease;
            filter: blur(2px);
        }

        .sidebar-link a:hover::before {
            left: 100%;
        }

        .sidebar-link a:hover::after {
            width: 6px;
        }

        .sidebar-link a:hover {
            color: #fff;
            background: rgba(255, 105, 180, 0.2);
            transform: translateX(10px) scale(1.05);
            box-shadow: 
                8px 8px 25px rgba(255, 105, 180, 0.4),
                inset 0 0 40px rgba(138, 43, 226, 0.3);
            text-shadow: 0 0 15px rgba(255, 255, 255, 0.9);
            border: 2px solid rgba(255, 105, 180, 0.5);
        }

        .sidebar-link a.active {
            color: #fff;
            background: linear-gradient(135deg, rgba(255, 105, 180, 0.4), rgba(138, 43, 226, 0.4));
            transform: translateX(10px);
            box-shadow: 
                8px 8px 30px rgba(255, 105, 180, 0.5),
                inset 0 0 50px rgba(138, 43, 226, 0.4);
            border: 2px solid rgba(255, 105, 180, 0.7);
        }

        .sidebar-link i {
            margin-right: 15px;
            font-size: 22px;
            width: 25px;
            text-align: center;
            transition: all 0.4s ease;
            filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.3));
            color: #94a3b8;
        }

        .sidebar-link a:hover i {
            transform: scale(1.4) rotate(15deg);
            filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.5));
            color: #fff;
        }

        .sidebar-link a.active i {
            color: #fff;
        }

        /* SEKCJA REGULAMINU */
        .regulations {
            min-height: 100vh;
            padding: 120px 50px 80px;
            margin-left: 0;
            transition: margin-left 0.7s ease;
            max-width: 1200px;
            margin: 0 auto;
        }

        .regulations.sidebar-open {
            margin-left: 300px;
        }

        .section-title {
            text-align: center;
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 80px;
            background: linear-gradient(135deg, #ff69b4, #8a2be2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 30px rgba(255, 105, 180, 0.3);
            letter-spacing: 1px;
        }

        .regulations-container {
            background: rgba(40, 40, 60, 0.7);
            border: 3px solid transparent;
            border-radius: 25px;
            padding: 50px;
            backdrop-filter: blur(15px);
            box-shadow: 
                0 15px 35px rgba(255, 105, 180, 0.2),
                0 0 50px rgba(138, 43, 226, 0.1) inset;
            position: relative;
            overflow: hidden;
            animation: borderGlow 2s ease-in-out infinite alternate;
        }

        @keyframes borderGlow {
            0% { 
                border-color: rgba(255, 105, 180, 0.5);
                box-shadow: 
                    0 15px 35px rgba(255, 105, 180, 0.2),
                    0 0 50px rgba(138, 43, 226, 0.1) inset;
            }
            100% { 
                border-color: rgba(138, 43, 226, 0.5);
                box-shadow: 
                    0 15px 35px rgba(138, 43, 226, 0.2),
                    0 0 50px rgba(255, 105, 180, 0.1) inset;
            }
        }

        .regulation-section {
            margin-bottom: 40px;
            padding: 30px;
            background: rgba(30, 30, 45, 0.5);
            border-radius: 20px;
            border-left: 5px solid #ff69b4;
            transition: all 0.4s ease;
        }

        .regulation-section:hover {
            transform: translateX(10px);
            background: rgba(30, 30, 45, 0.7);
            box-shadow: 0 10px 25px rgba(255, 105, 180, 0.2);
        }

        .regulation-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #ff69b4;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .regulation-title i {
            font-size: 2rem;
            filter: drop-shadow(0 0 10px rgba(255, 105, 180, 0.5));
        }

        .regulation-content {
            color: #cbd5e1;
            line-height: 1.8;
            font-size: 1.1rem;
        }

        .regulation-list {
            list-style: none;
            padding-left: 20px;
        }

        .regulation-list li {
            margin-bottom: 12px;
            position: relative;
            padding-left: 30px;
        }

        .regulation-list li::before {
            content: '▸';
            position: absolute;
            left: 0;
            color: #8a2be2;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .regulation-warning {
            background: linear-gradient(135deg, rgba(255, 69, 0, 0.2), rgba(255, 0, 0, 0.2));
            border-left: 5px solid #ff4500;
            padding: 25px;
            border-radius: 15px;
            margin: 30px 0;
            animation: pulseWarning 2s ease-in-out infinite;
        }

        @keyframes pulseWarning {
            0%, 100% { box-shadow: 0 0 20px rgba(255, 69, 0, 0.3); }
            50% { box-shadow: 0 0 30px rgba(255, 69, 0, 0.6); }
        }

        .warning-title {
            color: #ff4500;
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer {
            background: rgba(20, 20, 30, 0.95);
            padding: 40px 50px;
            text-align: center;
            border-top: 1px solid rgba(255, 105, 180, 0.3);
            margin-left: 0;
            transition: margin-left 0.7s ease;
        }

        .footer.sidebar-open {
            margin-left: 300px;
        }

        .footer p {
            color: #94a3b8;
            margin-bottom: 5px;
            font-size: 0.9rem;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 280px;
                left: -280px;
            }

            .regulations.sidebar-open,
            .footer.sidebar-open {
                margin-left: 280px;
            }

            .regulations {
                padding: 100px 20px 60px;
            }

            .regulations-container {
                padding: 30px 20px;
            }

            .section-title {
                font-size: 2.2rem;
                margin-bottom: 50px;
            }

            .regulation-title {
                font-size: 1.5rem;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <img src="logo.png" alt="CreaMC.pl">
        </div>
        <ul class="sidebar-links">
            <li class="sidebar-link">
                <a href="index.php">
                    <i class="fas fa-home"></i>
                    Strona Główna
                </a>
            </li>
            <li class="sidebar-link">
                <a href="sklep.php">
                    <i class="fas fa-shopping-cart"></i>
                    Sklep
                </a>
            </li>
            <li class="sidebar-link">
                <a href="regulamin.php" class="active">
                    <i class="fas fa-book"></i>
                    Regulamin
                </a>
            </li>
        </ul>
    </nav>

    <button class="menu-toggle" id="menuToggle">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <section class="regulations" id="regulations">
        <h2 class="section-title">Regulamin Serwera</h2>
        
        <div class="regulations-container">
            <div class="regulation-warning">
                <h3 class="warning-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    WAŻNE INFORMACJE
                </h3>
                <p class="regulation-content">
                    Każdy gracz jest zobowiązany do zapoznania się z regulaminem.<br>Nieznajomość regulaminu nie zwalnia z jego przestrzegania.<br>Administracja ma prawo do zmiany regulaminu w dowolnym momencie bez wcześniejszego powiadomienia.
                </p>
            </div>

            <div class="regulation-section">
                <h3 class="regulation-title">
                    <i class="fas fa-user-shield"></i>
                    §1 Zasady Ogólne
                </h3>
                <div class="regulation-content">
                    <ul class="regulation-list">
                        <li>Zabrania się używania jakichkolwiek modyfikacji gry dających przewagę nad innymi graczami (x-ray, killaura, nofall, bridge itp.)</li>
                        <li>Zabronione jest podszywanie się pod członków administracji</li>
                        <li>Nick gracza nie może być obraźliwy ani zawierać wulgaryzmów</li>
                        <li>Zabrania się reklamowania innych serwerów bez wyraźnej zgody administratora</li>
                    </ul>
                </div>
            </div>

            <div class="regulation-section">
                <h3 class="regulation-title">
                    <i class="fas fa-comments"></i>
                    §2 Czat i Komunikacja
                </h3>
                <div class="regulation-content">
                    <ul class="regulation-list">
                        <li>Zabrania się używania wulgaryzmów i przekleństw</li>
                        <li>Zakaz spamowania i floodowania czatu</li>
                        <li>Zabronione jest obrażanie innych graczy i prowokowanie kłótni</li>
                        <li>Zakaz dyskryminacji i mowy nienawiści</li>
                        <li>Zabrania się nadużywania CAPS LOCK</li>
                        <li>Należy zachować kulturę wypowiedzi</li>
                    </ul>
                </div>
            </div>

            <div class="regulation-section">
                <h3 class="regulation-title">
                    <i class="fas fa-gamepad"></i>
                    §3 Rozgrywka
                </h3>
                <div class="regulation-content">
                    <ul class="regulation-list">
                        <li>Zabrania się griefowania</li>
                        <li>Zakaz kradzieży przedmiotów z cudzych skrzynek</li>
                        <li>Zabronione jest wykorzystywanie błędów gry (bugów)</li>
                        <li>Należy szanować teren innych graczy</li>
                        <li>Zakaz budowania nieprzyzwoitych lub obraźliwych budowli(nie dotyczy twojej posesji)</li>
                        <li>Zabrania się tworzenia pułapek na graczy</li>
                    </ul>
                </div>
            </div>

            <div class="regulation-section">
                <h3 class="regulation-title">
                    <i class="fas fa-gavel"></i>
                    §4 Kary i Odwołania
                </h3>
                <div class="regulation-content">
                    <ul class="regulation-list">
                        <li>Administracja ma prawo karać graczy bez podawania konkretnej przyczyny</li>
                        <li>Od banów można się odwoływać na naszym Discordzie</li>
                        <li>Kary są uzależnione od stopnia wykroczenia</li>
                        <li>MK po banie jest zabronione</li>
                        <li>Administracja nie zwraca przedmiotów utraconych w wyniku śmierci lub kradzieży</li>
                    </ul>
                </div>
            </div>

            <div class="regulation-section">
                <h3 class="regulation-title">
                    <i class="fas fa-store"></i>
                    §5 Sklep i Płatności
                </h3>
                <div class="regulation-content">
                    <ul class="regulation-list">
                        <li>Zakupione usługi nie podlegają zwrotowi (chyba że przedmiot nie dotarł bądź coś poszło nie tak)</li>
                        <li>Zabrania się odsprzedaży kont z zakupionymi usługami</li>
                        <li>Wszystkie transakcje są rejestrowane</li>
                        <li>W przypadku problemów z zakupem należy kontaktować się z administracją</li>
                    </ul>
                </div>
            </div>

            <div class="regulation-warning">
                <h3 class="warning-title">
                    <i class="fas fa-info-circle"></i>
                    KONTAKT Z ADMINISTRACJĄ
                </h3>
                <p class="regulation-content">
                    W przypadku pytań, problemów lub potrzeby pomocy, zapraszamy na naszego Discorda: 
                    <a href="https://discord.gg/RK2V9DuzdJ" style="color: #ff69b4; text-decoration: none; font-weight: bold;">https://discord.gg/RK2V9DuzdJ</a>
                </p>
            </div>
        </div>
    </section>

    <footer class="footer" id="footer">
        <p>&copy; <?php echo date('Y'); ?> CreaMC.pl - Wszelkie prawa zastrzeżone</p>
    </footer>

    <script>
        // Sidebar toggle - identycznie jak w index.php
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const regulations = document.getElementById('regulations');
        const footer = document.getElementById('footer');

        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            menuToggle.classList.toggle('open');
            regulations.classList.toggle('sidebar-open');
            footer.classList.toggle('sidebar-open');
        });

        // Animacje pojawiania się sekcji regulaminu
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        });

        // Obserwuj sekcje regulaminu
        document.querySelectorAll('.regulation-section').forEach(section => {
            section.style.opacity = '0';
            section.style.transform = 'translateY(30px)';
            section.style.transition = 'all 0.6s ease';
            observer.observe(section);
        });
    </script>
</body>
</html>