<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Erreur serveur | GameLink</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../ICON/LogoSimple.svg">
    <style>
        :root {
            --bg: #0f1115;
            --bg-grad1: #11151c;
            --bg-grad2: #0c0f14;
            --text: #E7E9ED;
            --muted: #8c95a3;
            --warning: #fbbf24;
            --glow-yellow: rgba(251, 191, 36, .12);
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
            background: radial-gradient(circle at 50% 50%, var(--glow-yellow), transparent 70%);
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
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            box-shadow: 0 10px 30px rgba(245, 158, 11, 0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); box-shadow: 0 10px 30px rgba(245, 158, 11, 0.3); }
            50% { transform: scale(1.05); box-shadow: 0 15px 40px rgba(245, 158, 11, 0.5); }
        }

        .error-code {
            font-size: 120px;
            font-weight: 900;
            background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 20px;
            animation: blink 2s infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
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
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .info-box {
            margin: 30px 0;
            padding: 20px;
            background: rgba(251, 191, 36, 0.1);
            border: 1px solid rgba(251, 191, 36, 0.3);
            border-radius: 12px;
            text-align: left;
        }

        .info-box h3 {
            color: var(--warning);
            font-size: 16px;
            margin-bottom: 10px;
        }

        .info-box ul {
            list-style: none;
            padding: 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.8;
        }

        .info-box li::before {
            content: '→ ';
            color: var(--warning);
            font-weight: bold;
        }

        .buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
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
            background: linear-gradient(135deg, #6ea8ff 0%, #667eea 100%);
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
        <div class="logo">⚠️</div>
        <div class="error-code">500</div>
        <h1>Erreur serveur</h1>
        <p class="description">
            Une erreur s'est produite sur le serveur. 
            Nos équipes ont été automatiquement notifiées et travaillent à résoudre le problème.
        </p>
        <div class="info-box">
            <h3>🔧 Que faire ?</h3>
            <ul>
                <li>Rafraîchis la page dans quelques instants</li>
                <li>Reviens à l'accueil et réessaye</li>
                <li>Si le problème persiste, contacte le support</li>
            </ul>
        </div>
        <div class="buttons">
            <a href="/index.php" class="btn btn-primary">🏠 Retour à l'accueil</a>
            <button onclick="location.reload()" class="btn btn-secondary">🔄 Rafraîchir</button>
        </div>
    </div>
</body>
</html>