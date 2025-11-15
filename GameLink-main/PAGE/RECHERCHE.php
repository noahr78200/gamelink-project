<?php
session_start();
// si tu as un track.php, garde-le :
/* require_once __DIR__ . '/../INCLUDES/track.php'; */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Recherche | GameLink</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Adapte ces chemins si besoin en local -->
    <link rel="stylesheet" href="/CSS/HEADER.css" type="text/css">
    <link rel="stylesheet" href="/CSS/STYLE_RECHERCHE.css" type="text/css">
    <link rel="icon" type="image/png" sizes="32x32" href="/ICON/LogoSimple.svg">
</head>
<body>
    <main class="page-recherche">

        <!-- Titre + barre de recherche -->
        <section class="search-header">
            <h1>Rechercher un jeu</h1>
            <p class="search-subtext">
                Les jeux s’affichent par défaut par ordre alphabétique. Tu peux rechercher
                par nom ou filtrer par plateforme, genre ou éditeur.
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

        <!-- Filtres très simples avec boutons -->
        <section class="filters-panel">
            <div class="filters-panel-header">
                <span>Filtres</span>
                <button type="button" class="filters-reset" id="filters-reset">
                    Réinitialiser les filtres
                </button>
            </div>

            <div class="filters-groups">
                <!-- Plateformes -->
                <div class="filter-group">
                    <span class="filter-label">Plateforme</span>
                    <div class="filters">
                        <button class="chip chip--active" data-type="platform" data-value="">Toutes</button>
                        <button class="chip" data-type="platform" data-value="PC">PC</button>
                        <button class="chip" data-type="platform" data-value="PlayStation 4">PS4</button>
                        <button class="chip" data-type="platform" data-value="PlayStation 5">PS5</button>
                        <button class="chip" data-type="platform" data-value="Xbox One">Xbox One</button>
                        <button class="chip" data-type="platform" data-value="Nintendo Switch">Switch</button>
                    </div>
                </div>

                <!-- Genres -->
                <div class="filter-group">
                    <span class="filter-label">Genre</span>
                    <div class="filters">
                        <button class="chip chip--active" data-type="genre" data-value="">Tous</button>
                        <button class="chip" data-type="genre" data-value="Role-playing (RPG)">RPG</button>
                        <button class="chip" data-type="genre" data-value="Shooter">Shooter</button>
                        <button class="chip" data-type="genre" data-value="Adventure">Aventure</button>
                        <button class="chip" data-type="genre" data-value="Sport">Sport</button>
                        <button class="chip" data-type="genre" data-value="Racing">Course</button>
                    </div>
                </div>

                <!-- Éditeurs / studios (exemples) -->
                <div class="filter-group">
                    <span class="filter-label">Éditeur / Studio</span>
                    <div class="filters">
                        <button class="chip chip--active" data-type="editor" data-value="">Tous</button>
                        <button class="chip" data-type="editor" data-value="Nintendo">Nintendo</button>
                        <button class="chip" data-type="editor" data-value="Electronic Arts">EA</button>
                        <button class="chip" data-type="editor" data-value="Ubisoft">Ubisoft</button>
                        <button class="chip" data-type="editor" data-value="Sony Interactive Entertainment">Sony</button>
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
                <!-- Les jeux seront ajoutés ici en JS -->
            </ul>
        </section>
    </main>

    <!-- JS : en prod /JS/..., en local tu peux mettre ../JS/... -->
    <script src="/JS/RECHERCHE.js?v=1" defer></script>
</body>
</html>
