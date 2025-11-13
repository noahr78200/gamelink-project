<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="description"content="ACCUEIL GameLink">
        <title>Recherche | GameLink</title>
        <link rel="stylesheet" href="../CSS/HEADER.css" type="text/css"/>
        <link rel="stylesheet" href="../CSS/STYLE_RECHERCHE.css" type="text/css"/>
        <link rel="stylesheet" href="../CSS/GAME_CARD.css">
        <link rel="icon" type="image/png" sizes="32x32" href="../ICON/LogoSimple.svg">
    </head>
    <body>
  
<?php 
    // Inclure le header (qui affichera ou non le lien ADMIN)
    include __DIR__ . '/../INCLUDES/header.php'; 
    ?>
        <main>
            <section class="search-area">
                <form class="search" role="search" action="RECHERCHE.php" method="get"> 
                    <input type="text" type="search" id="search-Bar"
                        placeholder="Titre du jeu, platforme, genre, etc..." 
                        autocomplete="off"/>
                    <img src="../ICON/loupe.png" alt="Icône de loupe" />
                </form>

                <div class="filters">
                    <button class="chip">Plateformes</button>
                    <button class="chip">Genres</button>
                    <button class="chip">Autres</button>
                    <button class="chip">Note</button>
                    <button class="chip">Trier</button>
                    <button class="chip">Date</button>
                </div>
            </section>

            <section>
                <ul id="Game-list"></ul>
            </section>  
        
        </main>
        <script src="../JS/RECHERCHE.js?v=1" defer></script>
    </body>