<?php
session_start();
require_once __DIR__ . '/../INCLUDES/track.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Recherche | GameLink</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="../CSS/HEADER.css" type="text/css">
    <link rel="stylesheet" href="../CSS/STYLE_RECHERCHE.css" type="text/css">
    <link rel="stylesheet" href="../CSS/GAME_CARD.css" type="text/css">
    <link rel="icon" type="image/png" sizes="32x32" href="../ICON/LogoSimple.svg">
</head>
<body>
    <main class="page-recherche">
        <section class="search-header">
            <h1>Rechercher un jeu</h1>
            <p class="search-subtext">
                Tape le nom d’un jeu ou utilise les filtres par plateforme, genre ou éditeur.
            </p>

            <div class="search-area">
                <div class="search">
                    <input
                        type="text"
                        id="search-input"
                        placeholder="Exemple : Zelda, FIFA, Elden Ring...">
                    <button id="search-button" type="button">Rechercher</button>
                </div>
                <p id="error-message" class="error-message"></p>
            </div>
        </section>

        <section class="filters-panel">
            <div class="filters-panel-header">
                <span>Filtres avancés</span>
                <button type="button" class="filters-reset" id="filters-reset">
                    Réinitialiser les filtres
                </button>
            </div>

            <div class="filters-groups">
                <div class="filter-group">
                    <span class="filter-label">Plateformes</span>
                    <div class="filters" id="platform-filters">
                        <!-- Boutons générés en JS -->
                    </div>
                </div>

                <div class="filter-group">
                    <span class="filter-label">Genres</span>
                    <div class="filters" id="genre-filters">
                        <!-- Boutons générés en JS -->
                    </div>
                </div>

                <div class="filter-group">
                    <span class="filter-label">Éditeurs / Studios</span>
                    <div class="filters" id="editor-filters">
                        <!-- Boutons générés en JS -->
                    </div>
                </div>
            </div>
        </section>

        <section class="games-section">
            <div class="games-section-header">
                <p class="games-count" id="games-count">0 jeux trouvés</p>
                <p class="games-sort-label">Tri : alphabétique (A → Z)</p>
            </div>

            <ul id="Game-list" class="game-list">
                <!-- Cartes jeux en JS -->
            </ul>
        </section>
    </main>

    <!-- Chemin racine : adapte si besoin, mais en prod c’est souvent /API/... -->
    <script src="../JS/RECHERCHE.js?v=3" defer></script>
</body>
</html>
