<?php
// index.php - Page d'accueil publique
session_start();

// Récupérer le message flash si existant
$flash_message = $_SESSION['flash_index'] ?? null;
unset($_SESSION['flash_index']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="GameLink - Votre plateforme de jeux vidéo">
    <title>GameLink - Accueil</title>
    <link rel="stylesheet" href="CSS/HEADER.css" type="text/css"/>
    <link rel="stylesheet" href="CSS/STYLE_ACCUEIL.css" type="text/css"/>
    <link rel="icon" type="image/svg+xml" href="ICON/LogoSimple.svg">
    <style>
        .hero-section {
            text-align: center;
            padding: 80px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .hero-section h1 {
            font-size: 3em;
            margin-bottom: 20px;
        }
        .hero-section p {
            font-size: 1.3em;
            margin-bottom: 30px;
        }
        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            padding: 15px 40px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1em;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .btn-primary {
            background-color: white;
            color: #667eea;
        }
        .btn-secondary {
            background-color: transparent;
            color: white;
            border: 2px solid white;
        }
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            padding: 60px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .feature-card {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .feature-card h3 {
            color: #667eea;
            margin-bottom: 15px;
        }
        .error-message {
            background: #ff6b6b;
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px auto;
            max-width: 600px;
            text-align: center;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(255,107,107,0.3);
        }
        .success-message {
            background: #51cf66;
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px auto;
            max-width: 600px;
            text-align: center;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(81,207,102,0.3);
        }
        .protected-message {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <header>
        <nav class="Menu">
            <a href="index.php">
                <img class="logo" src="ICON/LogoComplet.svg" alt="Logo GameLink">
            </a>
            <a href="index.php">ACCUEIL</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="PAGE/RECHERCHE.php">RECHERCHE</a>
                <a href="PAGE/COMMUNAUTE.php">COMMUNAUTÉ</a>
                <a href="PAGE/ACCUEIL.php">MON ESPACE</a>
            <?php endif; ?>
        </nav>
        <?php if (isset($_SESSION['user_id'])): ?>
            <div style="display: flex; align-items: center; gap: 15px;">
                <span style="color: white;">Bonjour, <?= htmlspecialchars($_SESSION['user_pseudo']) ?></span>
                <a href="INCLUDES/logout.php">
                    <img src="ICON/iconProfil.svg" alt="Logo Profil" width="40">
                </a>
            </div>
        <?php else: ?>
            <a href="PAGE/AUTH.php" class="btn btn-primary" style="margin-right: 20px;">Connexion</a>
        <?php endif; ?>
    </header>
    
    <main>
        <?php if ($flash_message && isset($flash_message['error'])): ?>
            <div class="error-message">
                ❌ <?= htmlspecialchars($flash_message['error']) ?>
            </div>
        <?php endif; ?>

        <?php if ($flash_message && isset($flash_message['success'])): ?>
            <div class="success-message">
                ✅ <?= htmlspecialchars($flash_message['success']) ?>
            </div>
        <?php endif; ?>

        <section class="hero-section">
            <h1>🎮 Bienvenue sur GameLink</h1>
            <p>Découvrez, notez et partagez vos jeux vidéo préférés avec une communauté passionnée</p>
            
            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="cta-buttons">
                    <a href="PAGE/AUTH.php?tab=signup" class="btn btn-primary">Créer un compte</a>
                    <a href="PAGE/AUTH.php" class="btn btn-secondary">Se connecter</a>
                </div>
            <?php else: ?>
                <div class="cta-buttons">
                    <a href="PAGE/ACCUEIL.php" class="btn btn-primary">Mon espace</a>
                    <a href="PAGE/RECHERCHE.php" class="btn btn-secondary">Rechercher des jeux</a>
                </div>
            <?php endif; ?>
        </section>

        <?php if (isset($_GET['login_required'])): ?>
            <div class="protected-message">
                ⚠️ Vous devez être connecté pour accéder à cette fonctionnalité. 
                <a href="PAGE/AUTH.php">Connectez-vous</a> ou <a href="PAGE/AUTH.php?tab=signup">créez un compte</a>.
            </div>
        <?php endif; ?>

        <section class="features">
            <div class="feature-card">
                <h3>📚 Catalogue de jeux</h3>
                <p>Explorez une vaste bibliothèque de jeux vidéo de tous genres et plateformes</p>
            </div>
            
            <div class="feature-card">
                <h3>⭐ Notez et commentez</h3>
                <p>Partagez votre avis sur vos jeux préférés et découvrez ce que pensent les autres joueurs</p>
            </div>
            
            <div class="feature-card">
                <h3>📋 Playlists personnalisées</h3>
                <p>Créez des listes de jeux à faire, en cours ou terminés pour organiser votre expérience gaming</p>
            </div>
            
            <div class="feature-card">
                <h3>💬 Communauté</h3>
                <p>Rejoignez des forums, créez des groupes et échangez avec d'autres passionnés</p>
            </div>
            
            <div class="feature-card">
                <h3>✉️ Messagerie</h3>
                <p>Discutez avec vos amis et partagez vos découvertes gaming en direct</p>
            </div>
            
            <div class="feature-card">
                <h3>🎯 Événements</h3>
                <p>Participez à des événements gaming et restez informé des sorties à venir</p>
            </div>
        </section>
    </main>
     
</body>
</html>