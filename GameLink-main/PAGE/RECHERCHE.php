<?php
session_start();
require_once __DIR__ . '/../INCLUDES/track.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="description" content="Recherche GameLink">
    <title>Recherche | GameLink</title>

    <link rel="stylesheet" href="../CSS/HEADER.css" type="text/css">
    <link rel="stylesheet" href="../CSS/STYLE_RECHERCHE.css" type="text/css">
    <link rel="stylesheet" href="../CSS/GAME_CARD.css" type="text/css">
    <link rel="icon" type="image/png" sizes="32x32" href="../ICON/LogoSimple.svg">
</head>
<body>
    <?php
    // Inclure le header (qui affichera ou non le lien ADMIN)
    include __DIR__ . '/../INCLUDES/header.php';
    ?>
    </header>

    <main class="page-recherche">
        <section class="search-bar-section">
            <h1>Rechercher un jeu</h1>
            <div class="search-bar">
                <input
                    type="text"
                    id="search-input"
                    placeholder="Tape le nom d'un jeu (ex : Mario, Zelda...)">
                <button id="search-button">Rechercher</button>
            </div>
            <p id="error-message" class="error-message"></p>
        </section>

        <section class="games-section">
            <ul id="Game-list" class="game-list">
                <!-- Les jeux seront insérés ici en JS -->
            </ul>
        </section>
    </main>

    <!-- ⚠️ Chemin corrigé : SANS slash au début -->
    <script src="../JS/RECHERCHE.js?v=2" defer></script>
</body>
</html>
