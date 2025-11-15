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
    <header class="main-header">
        <div class="header-inner">
            <a href="ACCUEIL.php" class="logo">GameLink</a>
        </div>
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

        <!-- 🔹 Filtres simples : plateforme / genre / éditeur -->
        <section class="filters-panel">
            <div class="filters-groups">

                <!-- Plateformes -->
                <div class="filter-group">
                    <span class="filter-label">Plateforme</span>
                    <div class="filters">
                        <button class="chip chip--active" type="button" data-type="platform" data-value="">Toutes</button>
                        <button class="chip" type="button" data-type="platform" data-value="pc">PC</button>
                        <button class="chip" type="button" data-type="platform" data-value="playstation 4">PS4</button>
                        <button class="chip" type="button" data-type="platform" data-value="playstation 5">PS5</button>
                        <button class="chip" type="button" data-type="platform" data-value="xbox one">Xbox One</button>
                        <button class="chip" type="button" data-type="platform" data-value="nintendo switch">Switch</button>
                    </div>
                </div>

                <!-- Genres -->
                <div class="filter-group">
                    <span class="filter-label">Genre</span>
                    <div class="filters">
                        <button class="chip chip--active" type="button" data-type="genre" data-value="">Tous</button>
                        <button class="chip" type="button" data-type="genre" data-value="role-playing">RPG</button>
                        <button class="chip" type="button" data-type="genre" data-value="shooter">Shooter</button>
                        <button class="chip" type="button" data-type="genre" data-value="adventure">Aventure</button>
                        <button class="chip" type="button" data-type="genre" data-value="sport">Sport</button>
                        <button class="chip" type="button" data-type="genre" data-value="racing">Course</button>
                    </div>
                </div>

                <!-- Éditeurs -->
                <div class="filter-group">
                    <span class="filter-label">Éditeur</span>
                    <div class="filters">
                        <button class="chip chip--active" type="button" data-type="editor" data-value="">Tous</button>
                        <button class="chip" type="button" data-type="editor" data-value="nintendo">Nintendo</button>
                        <button class="chip" type="button" data-type="editor" data-value="ubisoft">Ubisoft</button>
                        <button class="chip" type="button" data-type="editor" data-value="electronic arts">EA</button>
                        <button class="chip" type="button" data-type="editor" data-value="sony">Sony</button>
                    </div>
                </div>
            </div>

            <button class="filters-reset" type="button" id="filters-reset">
                Réinitialiser les filtres
            </button>
        </section>

        <section class="games-section">
            <p class="games-count" id="games-count">0 jeu trouvé</p>
            <ul id="Game-list" class="game-list">
                <!-- Les jeux seront ajoutés ici en JavaScript -->
            </ul>
        </section>
    </main>

    <!-- On reste sur le même chemin JS qui fonctionnait -->
    <script src="../JS/RECHERCHE.js?v=3" defer></script>
</body>
</html>
