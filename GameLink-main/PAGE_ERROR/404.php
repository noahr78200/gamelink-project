<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page introuvable | GameLink</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../ICON/LogoSimple.svg">
    <style>
        :root {
            --bg: #0f1115;
            --bg-grad1: #11151c;
            --bg-grad2: #0c0f14;
            --panel: #1a1e27;
            --border: #2a3140;
            --text: #E7E9ED;
            --muted: #8c95a3;
            --accent: #6ea8ff;
            --glow: rgba(110,168,255,.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, var(--bg-grad1) 0%, var(--bg-grad2) 100%);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 50% 50%, var(--glow), transparent 70%);
            animation: rotate 20s linear infinite;
            pointer-events: none;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .error-container {
            text-align: center;
            padding: 40px;
            max-width: 600px;
            z-index: 1;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

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
            0%, 100% { transform: scale(1); box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3); }
            50% { transform: scale(1.05); box-shadow: 0 15px 40px rgba(102, 126, 234, 0.5); }
        }

        .error-code {
            font-size: 120px;
            font-weight: 900;
            background: linear-gradient(135deg, var(--accent) 0%, #667eea 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 20px;
            animation: glitch 3s infinite;
        }

        @keyframes glitch {
            0%, 100% { transform: translate(0); }
            20% { transform: translate(-2px, 2px); }
            40% { transform: translate(-2px, -2px); }
            60% { transform: translate(2px, 2px); }
            80% { transform: translate(2px, -2px); }
        }

        h1 {
            font-size: 32px;
            color: var(--text);
            margin-bottom: 15px;
            font-weight: 700;
        }

        .description {
            font-size: 18px;
            color: var(--muted);
            margin-bottom: 40px;
            line-height: 1.6;
        }

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
            background: linear-gradient(135deg, var(--accent) 0%, #667eea 100%);
            color: white;
            box-shadow: 0 5px 20px rgba(110, 168, 255, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(110, 168, 255, 0.5);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        .suggestions {
            margin-top: 50px;
            padding: 25px;
            background: rgba(42, 49, 64, 0.3);
            border-radius: 15px;
            border: 1px solid rgba(110, 168, 255, 0.2);
        }

        .suggestions h3 {
            font-size: 18px;
            color: var(--accent);
            margin-bottom: 15px;
        }

        .suggestions ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .suggestions a {
            color: var(--muted);
            text-decoration: none;
            transition: color 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .suggestions a:hover {
            color: var(--accent);
        }

        @media (max-width: 768px) {
            .error-code { font-size: 80px; }
            h1 { font-size: 24px; }
            .description { font-size: 16px; }
            .buttons { flex-direction: column; }
            .btn { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="logo">🎮</div>
        <div class="error-code">404</div>
        <h1>Oups ! Cette page n'existe pas</h1>
        <p class="description">
            On dirait que tu as trouvé un passage secret... mais il ne mène nulle part ! 
            La page que tu cherches a peut-être été déplacée ou n'existe plus.
        </p>
        <div class="buttons">
            <a href="/index.php" class="btn btn-primary">🏠 Retour à l'accueil</a>
            <button onclick="history.back()" class="btn btn-secondary">⬅️ Page précédente</button>
        </div>
        <div class="suggestions">
            <h3>🔍 Pages populaires :</h3>
            <ul>
                <li><a href="/index.php">🏠 Accueil</a></li>
                <li><a href="/PAGE/RECHERCHE.php">🔎 Rechercher des jeux</a></li>
                <li><a href="/PAGE/COMMUNAUTE.php">👥 Communauté</a></li>
                <li><a href="/PAGE/CHAT.php">💬 Chat</a></li>
            </ul>
        </div>
    </div>
</body>
</html>