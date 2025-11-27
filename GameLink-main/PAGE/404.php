<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page introuvable | GameLink</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../ICON/LogoSimple.svg">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #0f1115 0%, #1a1e27 100%);
            color: #e7e9ed;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        /* Effet de particules en arrière-plan */
        .background-effect {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .particle {
            position: absolute;
            width: 2px;
            height: 2px;
            background: rgba(110, 168, 255, 0.3);
            border-radius: 50%;
            animation: float 10s infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) translateX(0);
                opacity: 0;
            }
            50% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) translateX(50px);
            }
        }

        /* Container principal */
        .error-container {
            text-align: center;
            padding: 40px;
            max-width: 600px;
            z-index: 1;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Logo GameLink */
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 15px 40px rgba(102, 126, 234, 0.5);
            }
        }

        /* Titre 404 */
        .error-code {
            font-size: 120px;
            font-weight: 900;
            background: linear-gradient(135deg, #6ea8fe 0%, #667eea 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 20px;
            text-shadow: 0 0 30px rgba(110, 168, 255, 0.5);
            animation: glitch 3s infinite;
        }

        @keyframes glitch {
            0%, 100% {
                transform: translate(0);
            }
            20% {
                transform: translate(-2px, 2px);
            }
            40% {
                transform: translate(-2px, -2px);
            }
            60% {
                transform: translate(2px, 2px);
            }
            80% {
                transform: translate(2px, -2px);
            }
        }

        /* Titre */
        h1 {
            font-size: 32px;
            color: #e7e9ed;
            margin-bottom: 15px;
            font-weight: 700;
        }

        /* Description */
        .description {
            font-size: 18px;
            color: #99a1b3;
            margin-bottom: 40px;
            line-height: 1.6;
        }

        /* Boutons */
        .buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 15px 35px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6ea8fe 0%, #667eea 100%);
            color: white;
            box-shadow: 0 5px 20px rgba(110, 168, 255, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(110, 168, 255, 0.5);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #e7e9ed;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        /* Suggestions */
        .suggestions {
            margin-top: 50px;
            padding: 25px;
            background: rgba(42, 49, 64, 0.3);
            border-radius: 15px;
            border: 1px solid rgba(110, 168, 255, 0.2);
        }

        .suggestions h3 {
            font-size: 18px;
            color: #6ea8fe;
            margin-bottom: 15px;
        }

        .suggestions ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .suggestions li {
            text-align: left;
        }

        .suggestions a {
            color: #99a1b3;
            text-decoration: none;
            transition: color 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .suggestions a:hover {
            color: #6ea8fe;
        }

        /* Timer de redirection */
        .redirect-timer {
            margin-top: 30px;
            font-size: 14px;
            color: #99a1b3;
        }

        .redirect-timer .countdown {
            color: #6ea8fe;
            font-weight: 700;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .error-code {
                font-size: 80px;
            }

            h1 {
                font-size: 24px;
            }

            .description {
                font-size: 16px;
            }

            .buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Effet de particules -->
    <div class="background-effect" id="particles"></div>

    <!-- Container d'erreur -->
    <div class="error-container">
        <!-- Logo -->
        <div class="logo">🎮</div>

        <!-- Code erreur -->
        <div class="error-code">404</div>

        <!-- Titre -->
        <h1>Oups ! Cette page n'existe pas</h1>

        <!-- Description -->
        <p class="description">
            On dirait que tu as trouvé un passage secret... mais il ne mène nulle part ! 
            La page que tu cherches a peut-être été déplacée ou n'existe plus.
        </p>

        <!-- Boutons -->
        <div class="buttons">
            <a href="/HTML/ACCUEIL.php" class="btn btn-primary">
                🏠 Retour à l'accueil
            </a>
            <button onclick="history.back()" class="btn btn-secondary">
                ⬅️ Page précédente
            </button>
        </div>

        <!-- Suggestions -->
        <div class="suggestions">
            <h3>🔍 Pages populaires :</h3>
            <ul>
                <li><a href="/HTML/ACCUEIL.php">🏠 Accueil</a></li>
                <li><a href="/HTML/RECHERCHE.php">🔎 Rechercher des jeux</a></li>
                <li><a href="/HTML/COMMUNAUTE.php">👥 Communauté</a></li>
                <li><a href="/HTML/CHAT.php">💬 Chat</a></li>
            </ul>
        </div>

        <!-- Timer de redirection (optionnel) -->
        <!-- Décommente si tu veux une redirection automatique -->
        <!--
        <div class="redirect-timer">
            Redirection automatique vers l'accueil dans <span class="countdown" id="countdown">10</span> secondes...
        </div>
        -->
    </div>

    <script>
        // Créer des particules flottantes
        const particlesContainer = document.getElementById('particles');
        for (let i = 0; i < 50; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDelay = Math.random() * 10 + 's';
            particle.style.animationDuration = (Math.random() * 10 + 5) + 's';
            particlesContainer.appendChild(particle);
        }

        // REDIRECTION AUTOMATIQUE (optionnel)
        // Décommente ces lignes si tu veux une redirection automatique après 10 secondes
        /*
        let timeLeft = 10;
        const countdownElement = document.getElementById('countdown');
        
        const countdown = setInterval(() => {
            timeLeft--;
            countdownElement.textContent = timeLeft;
            
            if (timeLeft <= 0) {
                clearInterval(countdown);
                window.location.href = '/HTML/ACCUEIL.php';
            }
        }, 1000);
        */

        // Message dans la console pour les curieux
        console.log('%c404 - Page introuvable', 'font-size: 24px; color: #6ea8fe; font-weight: bold;');
        console.log('%cMais tu peux taper "play" pour jouer au Snake ! 🐍', 'font-size: 14px; color: #99a1b3;');
    </script>
</body>
</html>