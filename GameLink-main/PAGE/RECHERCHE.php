<?php
session_start();
require_once __DIR__ . '/../INCLUDES/track.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="description" content="Recherche de jeux | GameLink">
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
                    placeholder="Tape le nom d'un jeu (ex: Zelda, Mario, FIFA...)">
                <button id="search-button">Rechercher</button>
            </div>
            <p id="error-message" class="error-message"></p>
        </section>

        <section class="filter-chips">
            <div class="chips">
                <button class="chip" type="button">Tous</button>
                <button class="chip" type="button">Populaires</button>
                <button class="chip" type="button">Note</button>
                <button class="chip" type="button">Date</button>
            </div>
        </section>

        <section class="games-section">
            <ul id="Game-list" class="game-list">
                <!-- Les jeux seront ajoutés ici en JavaScript -->
            </ul>
        </section>
    </main>

    <!-- Chemin corrigé vers ton JS -->
    <script src="../JS/RECHERCHE.js" defer></script>
</body>
</html>
