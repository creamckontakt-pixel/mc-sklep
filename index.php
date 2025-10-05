<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CreaMC.pl - Serwer Minecraft</title>
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

        /* SIDEBAR NAVIGATION - LEWA STRONA TAK JAK BYŁO */
        .sidebar {
            position: fixed;
            top: 0;
            left: -320px; /* STARTUJE POZA EKRANEM PO LEWEJ */
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
            left: 0; /* WSUWA SIĘ Z LEWEJ */
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

        /* LOGO W SIDEBAR - WYŚRODKOWANE */
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

        /* LINKI W SIDEBAR - TYLKO HOVER SIĘ PODŚWIETLA */
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
            color: #94a3b8; /* DOMYŚLNIE SZARY */
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
            background: transparent; /* BEZ TŁA DOMYŚLNIE */
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

        /* TYLKO HOVER MA EFEKTY! */
        .sidebar-link a:hover::before {
            left: 100%;
        }

        .sidebar-link a:hover::after {
            width: 6px;
        }

        .sidebar-link a:hover {
            color: #fff; /* BIAŁY TEKST NA HOVER */
            background: rgba(255, 105, 180, 0.2); /* TYLKO NA HOVER */
            transform: translateX(10px) scale(1.05);
            box-shadow: 
                8px 8px 25px rgba(255, 105, 180, 0.4),
                inset 0 0 40px rgba(138, 43, 226, 0.3);
            text-shadow: 0 0 15px rgba(255, 255, 255, 0.9);
            border: 2px solid rgba(255, 105, 180, 0.5);
        }

        /* AKTYWNY LINK TEŻ MA EFEKTY */
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
            color: #94a3b8; /* DOMYŚLNIE SZARY */
        }

        .sidebar-link a:hover i {
            transform: scale(1.4) rotate(15deg);
            filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.5));
            color: #fff; /* BIAŁY NA HOVER */
        }

        .sidebar-link a.active i {
            color: #fff; /* BIAŁY DLA AKTYWNEGO */
        }

        /* RESZTA SEKCJI BEZ ZMIAN - TAK JAK BYŁO */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
            background: linear-gradient(rgba(10, 10, 18, 0.85), rgba(10, 10, 18, 0.9)), 
                        url('minecraft-background.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            padding: 0 20px;
            margin-left: 0;
            transition: margin-left 0.7s ease;
        }

        .hero.sidebar-open {
            margin-left: 300px; /* PRZESUWA STRONĘ W PRAWO */
        }

        .hero-content {
            max-width: 800px;
        }

        .hero h1 {
            font-size: 5rem;
            font-weight: 900;
            margin-bottom: 25px;
            background: linear-gradient(135deg, #ff69b4, #8a2be2, #ff69b4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-size: 200% 200%;
            animation: textShine 3s ease-in-out infinite;
            text-shadow: 0 0 50px rgba(255, 105, 180, 0.5);
            letter-spacing: 2px;
        }

        @keyframes textShine {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .hero-subtitle {
            font-size: 1.4rem;
            color: #cbd5e1;
            margin-bottom: 50px;
            font-weight: 300;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
        }

        .server-display {
            background: rgba(40, 40, 60, 0.7);
            border: 3px solid transparent;
            border-radius: 20px;
            padding: 30px;
            margin: 40px auto;
            max-width: 450px;
            backdrop-filter: blur(15px);
            box-shadow: 
                0 15px 35px rgba(255, 105, 180, 0.2),
                0 0 50px rgba(138, 43, 226, 0.1) inset;
            position: relative;
            overflow: hidden;
            animation: borderGlowHero 2s ease-in-out infinite alternate;
        }

        @keyframes borderGlowHero {
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

        .server-ip {
            font-size: 2.5rem;
            font-weight: 800;
            color: #ff69b4;
            margin-bottom: 10px;
            font: bold 2em 'Trebuchet MS', sans-serif;
            letter-spacing: 2px;
            text-shadow: 0 0 20px rgba(255, 105, 180, 0.5);
        }

        .server-version {
            color: #94a3b8;
            font-size: 1.1rem;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        .cta-buttons {
            display: flex;
            gap: 25px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 40px;
        }

        .cta-btn {
            padding: 18px 45px;
            font-size: 1.2rem;
            font-weight: 700;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-decoration: none;
            display: inline-block;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .cta-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s;
        }

        .cta-btn:hover::before {
            left: 100%;
        }

        .cta-btn.connect {
             background: transparent; /* ZMIENIONE - przezroczyste tło domyślnie */
            color: #ff69b4; /* ZMIENIONE - różowy tekst domyślnie */
            border: 3px solid #ff69b4; /* ZMIENIONE - różowy border domyślnie */
            box-shadow: 0 5px 20px rgba(255, 105, 180, 0.3); /* ZMIENIONE - mniejszy shadow */
}

        .cta-btn.connect:hover {
            background: linear-gradient(135deg, #ff69b4); /* GRADIENT TYLKO NA HOVER */
            color: white;
            transform: translateY(-8px) scale(1.05);
            box-shadow: 
            0 20px 40px rgba(255, 105, 180, 0.6),
            0 0 30px rgba(138, 43, 226, 0.4);
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
            border: 3px solid transparent; /* ZMIENIONE - przezroczysty border na hover */
}

        .cta-btn.shop {
            background: transparent;
            color: #8a2be2;
            border: 3px solid #8a2be2;
            box-shadow: 0 5px 20px rgba(138, 43, 226, 0.3);
        }

        .cta-btn.shop:hover {
            background: #8a2be2;
            color: white;
            transform: translateY(-8px) scale(1.05);
            box-shadow: 
                0 20px 40px rgba(138, 43, 226, 0.6),
                0 0 30px rgba(138, 43, 226, 0.4);
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
        }

        .social {
            padding: 120px 50px;
            background: rgba(15, 15, 25, 0.9);
            margin-left: 0;
            transition: margin-left 0.7s ease;
        }

        .social.sidebar-open {
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

        .social-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 40px;
            max-width: 1100px;
            margin: 0 auto;
        }

        .social-card {
            background: rgba(40, 40, 60, 0.7);
            border: 2px solid transparent;
            border-radius: 25px;
            padding: 50px 40px;
            text-align: center;
            text-decoration: none;
            color: #fff;
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            backdrop-filter: blur(15px);
            position: relative;
            overflow: hidden;
        }

        .social-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.7s;
        }

        .social-card:hover::before {
            left: 100%;
        }

        .social-card.discord {
            border-color: #5865f2;
            box-shadow: 0 10px 30px rgba(88, 101, 242, 0.2);
        }

        .social-card.discord:hover {
            transform: translateY(-15px) scale(1.05) rotate(2deg);
            border-color: #5865f2;
            background: rgba(88, 101, 242, 0.15);
            box-shadow: 
                0 25px 50px rgba(88, 101, 242, 0.4),
                0 0 60px rgba(88, 101, 242, 0.3),
                0 0 100px rgba(88, 101, 242, 0.2);
        }

        .social-card.tiktok {
            border-color: #ff0050;
            box-shadow: 0 10px 30px rgba(255, 0, 80, 0.2);
        }

        .social-card.tiktok:hover {
            transform: translateY(-15px) scale(1.05) rotate(-2deg);
            border-color: #ff0050;
            background: rgba(255, 0, 80, 0.15);
            box-shadow: 
                0 25px 50px rgba(255, 0, 80, 0.4),
                0 0 60px rgba(255, 0, 80, 0.3),
                0 0 100px rgba(255, 0, 80, 0.2);
        }

        .social-icon {
            font-size: 5rem;
            margin-bottom: 30px;
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            filter: drop-shadow(0 5px 15px rgba(0, 0, 0, 0.3));
        }

        .social-card:hover .social-icon {
            transform: scale(1.3) rotate(10deg);
            filter: drop-shadow(0 10px 25px rgba(0, 0, 0, 0.5));
        }

        .notification {
            position: fixed;
            top: 120px;
            right: -777px;
            background: linear-gradient(135deg, #ff69b4, #8a2be2);
            color: white;
            padding: 20px 30px;
            border-radius: 15px;
            font-weight: 700;
            z-index: 1001;
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 15px 35px rgba(255, 105, 180, 0.4);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .notification.show {
            right: 30px;
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

            .hero.sidebar-open,
            .social.sidebar-open,
            .footer.sidebar-open {
                margin-left: 280px;
            }

            .hero h1 {
                font-size: 3rem;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }

            .cta-btn {
                width: 250px;
            }

            .social {
                padding: 80px 20px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="notification" id="notification">IP skopiowane! Wklej w swoją wyszukiwarkę Minecraft!</div>

    <nav class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <img src="logo.png" alt="CreaMC.pl">
        </div>
        <ul class="sidebar-links">
            <li class="sidebar-link">
                <a href="index.php" class="active">
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
                <a href="regulamin.php">
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

    <section class="hero" id="hero">
        <div class="hero-content">
            <h1>CreaMC.pl</h1>
            <div class="hero-subtitle"></div>
            
            <div class="server-display">
                <div class="server-ip" id="serverIp">Oficjalna strona serwera</div>
                <div class="server-version"></div>
            </div>

            <div class="cta-buttons">
                <button class="cta-btn connect" onclick="copyIp()">Dołącz do gry</button>
                <a href="sklep.php" class="cta-btn shop">Sklep serwera</a>
            </div>
        </div>
    </section>

    <section class="social" id="social">
        <h2 class="section-title">Dołącz do nas</h2>
        <div class="social-grid">
            <a href="https://discord.gg/RK2V9DuzdJ" class="social-card discord" target="_blank">
                <i class="fab fa-discord social-icon"></i>
                <h3 class="social-title">Oficjalny Discord serwera</h3>
                <p class="social-desc">Na naszym Discordzie otrzymasz pełne, szybkie wsparcie od administracji. To właśnie tutaj publikujemy wszystkie najważniejsze informacje i aktualizacje.</p>
            </a>
            
            <a href="https://tiktok.com/@creamic" class="social-card tiktok" target="_blank">
                <i class="fab fa-tiktok social-icon"></i>
                <h3 class="social-title">Nasz profil na TikTok</h3>
                <p class="social-desc">Na naszym TikToku znajdziesz najlepsze urywki z serwera, aktualne informacje, ciekawostki i te wszystkie zabawne momenty, których nie zobaczysz nigdzie indziej.</p>
            </a>
        </div>
    </section>

    <footer class="footer" id="footer">
        <p>&copy; <?php echo date('Y'); ?> CreaMC.pl - Wszelkie prawa zastrzeżone</p>
    </footer>

    <script>
        // Sidebar toggle - TAK JAK BYŁO, Z LEWEJ STRONY
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const hero = document.getElementById('hero');
        const social = document.getElementById('social');
        const footer = document.getElementById('footer');

        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            menuToggle.classList.toggle('open');
            hero.classList.toggle('sidebar-open');
            social.classList.toggle('sidebar-open');
            footer.classList.toggle('sidebar-open');
        });

        // Kopiowanie IP
        function copyIp() {
            navigator.clipboard.writeText('creamc.pl').then(() => {
                showNotification();
            });
        }

        // Pokazanie notyfikacji
        function showNotification() {
            const notification = document.getElementById('notification');
            notification.classList.add('show');
            setTimeout(() => notification.classList.remove('show'), 3000);
        }

        // Efekt pisania dla IP
        function typeWriter(element, text, speed = 80) {
            let i = 0;
            element.textContent = '';
            
            function type() {
                if (i < text.length) {
                    element.textContent += text.charAt(i);
                    i++;
                    setTimeout(type, speed);
                }
            }
            type();
        }

        // Uruchom efekt pisania
        window.onload = function() {
            const ipElement = document.querySelector('.server-ip');
            typeWriter(ipElement, ipElement.textContent, 70);
        };

        // Animacje pojawiania się
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0) scale(1)';
                }
            });
        });

        // Obserwuj karty
        document.querySelectorAll('.social-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(50px) scale(0.9)';
            card.style.transition = 'all 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
            observer.observe(card);
        });
    </script>
</body>
</html>