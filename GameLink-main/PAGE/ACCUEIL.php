<?php
// PAGE/ACCUEIL.php - Page d'accueil pour utilisateurs connectés
session_start();

// Protection : redirection si non connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?login_required=1');
    exit;
}

// Récupérer les informations de l'utilisateur
require_once __DIR__ . '/../DATA/DBConfig.php';

$user_id = $_SESSION['user_id'];
$user_pseudo = $_SESSION['user_pseudo'];

// TODO: Récupérer les jeux de l'utilisateur depuis la BDD
// Pour l'instant, on affiche juste un message de bienvenue
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Mon espace GameLink">
    <title>Mon Espace | GameLink</title>
    <link rel="stylesheet" href="../CSS/HEADER.css" type="text/css"/>
    <link rel="stylesheet" href="../CSS/STYLE_ACCUEIL.css" type="text/css"/>
    <link rel="icon" type="image/svg+xml" href="../ICON/LogoSimple.svg">
    <style>
        main {
            padding: 40px 20px;
            max-width: 1400px;
            margin: 0 auto;
        }
        .welcome-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 10px;
            margin-bottom: 40px;
            text-align: center;
        }
        .welcome-banner h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        .game-section {
            margin-bottom: 50px;
        }
        .game-section h2 {
            color: #667eea;
            font-size: 1.8em;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
        }
        .empty-state {
            background: #f8f9fa;
            padding: 60px 20px;
            border-radius: 10px;
            text-align: center;
            color: #666;
        }
        .empty-state p {
            font-size: 1.2em;
            margin-bottom: 20px;
        }
        .btn-add-game {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background 0.3s;
        }
        .btn-add-game:hover {
            background: #5568d3;
        }
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            color: #667eea;
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        .stat-card p {
            color: #666;
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    <header>
        <nav class="Menu">
            <a href="../index.php">
                <img class="logo" src="../ICON/LogoComplet.svg" alt="Logo GameLink">
            </a>
            <a href="ACCUEIL.php">MON ESPACE</a>
            <a href="RECHERCHE.php">RECHERCHE</a>
            <a href="COMMUNAUTE.php">COMMUNAUTÉ</a>
            <a href="ADMIN.php">ADMIN</a>
        </nav>
        <div style="display: flex; align-items: center; gap: 15px;">
            <span style="color: white; font-weight: bold;"><?= htmlspecialchars($user_pseudo) ?></span>
            <a href="../INCLUDES/logout.php" title="Se déconnecter">
                <img src="../ICON/iconProfil.svg" alt="Logo Profil" width="40">
            </a>
        </div>
    </header>
    
    <main>
        <div class="welcome-banner">
            <h1>👋 Bienvenue, <?= htmlspecialchars($user_pseudo) ?> !</h1>
            <p>Voici votre espace personnel GameLink</p>
        </div>

        <div class="stats-cards">
            <div class="stat-card">
                <h3>0</h3>
                <p>Jeux en cours</p>
            </div>
            <div class="stat-card">
                <h3>0</h3>
                <p>Jeux terminés</p>
            </div>
            <div class="stat-card">
                <h3>0</h3>
                <p>Jeux à faire</p>
            </div>
            <div class="stat-card">
                <h3>0</h3>
                <p>Jeux favoris</p>
            </div>
        </div>

        <section class="game-section">
            <h2>🎮 JEUX EN COURS</h2>
            <div class="empty-state">
                <p>Vous n'avez pas encore de jeux en cours</p>
                <a href="RECHERCHE.php" class="btn-add-game">Rechercher des jeux</a>
            </div>
        </section>

        <section class="game-section">
            <h2>📝 JEUX À COMMENCER</h2>
            <div class="empty-state">
                <p>Aucun jeu dans votre liste "à faire"</p>
                <a href="RECHERCHE.php" class="btn-add-game">Ajouter des jeux</a>
            </div>
        </section>

        <section class="game-section">
            <h2>⏸️ JEUX EN PAUSE</h2>
            <div class="empty-state">
                <p>Aucun jeu en pause</p>
            </div>
        </section>

        <section class="game-section">
            <h2>✅ JEUX TERMINÉS</h2>
            <div class="empty-state">
                <p>Vous n'avez pas encore terminé de jeux</p>
                <a href="RECHERCHE.php" class="btn-add-game">Découvrir des jeux</a>
            </div>
        </section>
    </main>
     
</body>
</html>
