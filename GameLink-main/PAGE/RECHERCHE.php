<?php
session_start();
// require_once __DIR__ . '/../INCLUDES/track.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Recherche | GameLink</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Adapte si besoin, mais en prod : DocumentRoot = GameLink-main -->
    <link rel="stylesheet" href="/CSS/HEADER.css" type="text/css">
    <link rel="stylesheet" href="/CSS/STYLE_RECHERCHE.css" type="text/css">
    <link rel="icon" type="image/png" sizes="32x32" href="/ICON/LogoSimple.svg">
</head>
<body>
    <main class="page-recherche">

        <!-- Barre de recherche -->
        <section class="search-header">
            <h1>Rechercher un jeu</h1>
            <p class="search-subtext">
                Par défaut les jeux sont triés par ordre alphabétique. Tu peux rechercher par nom
                et filtrer par plateforme, genre ou éditeur.
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

        <!-- Filtres simples -->
        <section class="filters-panel">
            <div class="filters-panel-header">
                <span>Filtres</span>
                <button type="button" class="filters-reset" id="filters-reset">
                    Réinitialiser les filtres
                </button>
            </div>

            <div class="filters-groups">
                <!-- Plateforme -->
                <div class="filter-group">
                    <span class="filter-label">Plateforme</span>
                    <div class="filters">
                        <button class="chip chip--active" data-type="platform" data-value="">Toutes</button>
                        <button class="chip" data-type="platform" data-value="pc">PC</button>
                        <button class="chip" data-type="platform" data-value="playstation 4">PS4</button>
                        <button class="chip" data-type="platform" data-value="playstation 5">PS5</button>
                        <button class="chip" data-type="platform" data-value="xbox one">Xbox One</button>
                        <button class="chip" data-type="platform" data-value="nintendo switch">Switch</button>
                    </div>
                </div>

                <!-- Genre -->
                <div class="filter-group">
                    <span class="filter-label">Genre</span>
                    <div class="filters">
                        <button class="chip chip--active" data-type="genre" data-value="">Tous</button>
                        <button class="chip" data-type="genre" data-value="role-playing">RPG</button>
                        <button class="chip" data-type="genre" data-value="shooter">Shooter</button>
                        <button class="chip" data-type="genre" data-value="adventure">Aventure</button>
                        <button class="chip" data-type="genre" data-value="sport">Sport</button>
                        <button class="chip" data-type="genre" data-value="racing">Course</button>
                    </div>
                </div>

                <!-- Éditeur -->
                <div class="filter-group">
                    <span class="filter-label">Éditeur</span>
                    <div class="filters">
                        <button class="chip chip--active" data-type="editor" data-value="">Tous</button>
                        <button class="chip" data-type="editor" data-value="nintendo">Nintendo</button>
                        <button class="chip" data-type="editor" data-value="ubisoft">Ubisoft</button>
                        <button class="chip" data-type="editor" data-value="electronic arts">EA</button>
                        <button class="chip" data-type="editor" data-value="sony">Sony</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Liste des jeux -->
        <section class="games-section">
            <div class="games-section-header">
                <p class="games-count" id="games-count">0 jeu trouvé</p>
                <p class="games-sort-label">Tri : alphabétique (A → Z)</p>
            </div>

            <ul id="Game-list" class="game-list">
                <!-- Rempli en JS -->
            </ul>
        </section>
    </main>

    <!-- Important : chemin depuis la racine du site -->
    <script src="../JS/RECHERCHE.js?v=3" defer></script>
</body>
</html>
