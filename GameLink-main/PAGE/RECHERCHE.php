<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="description"content="ACCUEIL GameLink">
        <title>Recherche | GameLink</title>
        <link rel="stylesheet" href="/GameLink/CSS/HEADER.css" type="text/css"/>
        <link rel="stylesheet" href="/GameLink/CSS/STYLE_RECHERCHE.css" type="text/css"/>
        <link rel="stylesheet" href="/GameLink/CSS/GAME_CARD.css">
        <link rel="icon" type="image/png" sizes="32x32" href="/GameLink/ICON/LogoSimple.svg">
    </head>
    <body>
        <header>
            <nav class="Menu">
                <a href="">
                    <img  class="logo" src="/GameLink/ICON/LogoComplet.svg" alt="Logo GameLink" width="">
                </a>
                <a href="ACCUEIL.php">ACCUEIL</a>
                <a href="RECHERCHE.php">RECHERCHE</a>
                <a href="COMMUNAUTE.php">COMMUNAUTÉ</a>
                <a href="ADMIN.php">ADMIN</a>
            </nav>
            <a href="">
                <img src="/GameLink/ICON/iconProfil.svg" alt="Logo Profil" width="">
            </a>
        </header>
        
        <main>
            <section class="search-area">
                <form class="search" role="search" action="RECHERCHE.php" method="get"> 
                    <input type="text" type="search" id="search-Bar"
                        placeholder="Titre du jeu, platforme, genre, etc..." 
                        autocomplete="off"/>
                    <img src="/GameLink/ICON/loupe.png" alt="Icône de loupe" />
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
        <script src="/GameLink/JS/RECHERCHE.js?v=1" defer></script>
    </body>