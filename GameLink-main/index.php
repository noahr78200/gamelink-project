<?php
// Ici, on ne fait rien de spécial en PHP.
// On affiche juste la page d'accueil publique.
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>GameLink - Bienvenue</title>

    <!-- On relie le CSS de cette page -->
    <link rel="stylesheet" href="CSS/STYLE_INDEX.css">
</head>
<body>

    <!-- ===== HEADER SIMPLE ===== -->
    <header class="main-header">
        <div class="header-inner">

            <!-- Logo qui renvoie vers l'index -->
            <a href="index.php" class="logo">GameLink</a>

            <!-- Menu principal : tout envoie vers AUTH.php -->
            <nav class="main-nav">
                <a href="AUTH.php">Accueil</a>
                <a href="AUTH.php">Recherche</a>
                <a href="AUTH.php">Communauté</a>
                <a href="AUTH.php">Profil</a>
            </nav>

            <!-- Boutons Connexion / Inscription à droite -->
            <div class="auth-buttons">
                <a href="AUTH.php" class="btn btn-login">Connexion</a>
                <a href="AUTH.php" class="btn btn-register">Inscription</a>
            </div>
        </div>
    </header>

    <!-- ===== CONTENU PRINCIPAL ===== -->
    <main>
        <!-- Bandeau de bienvenue (inspiré de ta page d'accueil) -->
        <section class="welcome-banner">
            <h1>Bienvenue sur GameLink 🎮</h1>
            <p>
                Crée ton compte pour découvrir les jeux, les playlists et la communauté.
            </p>
            <a href="AUTH.php" class="btn-big">
                Créer un compte
            </a>
        </section>

        <!-- Bloc "Pourquoi créer un compte ?" -->
        <section class="home-section">
            <h2 class="section-title">Pourquoi créer un compte ?</h2>

            <div class="feature-grid">

                <div class="feature-card">
                    <h3>Ta bibliothèque de jeux</h3>
                    <p>Retrouve facilement tous tes jeux préférés au même endroit.</p>
                    <a href="AUTH.php" class="link-small">Voir les jeux</a>
                </div>

                <div class="feature-card">
                    <h3>Une vraie communauté</h3>
                    <p>Discute avec d'autres joueurs et partage tes découvertes.</p>
                    <a href="AUTH.php" class="link-small">Voir la communauté</a>
                </div>

                <div class="feature-card">
                    <h3>Playlists & avis</h3>
                    <p>Crée des listes de jeux et garde une trace de ce que tu as testé.</p>
                    <a href="AUTH.php" class="link-small">Créer un compte</a>
                </div>

            </div>
        </section>

        <!-- Bloc final avec gros CTA -->
        <section class="home-section">
            <h2 class="section-title">Prêt à rejoindre l’aventure ?</h2>
            <p class="center-text">
                Pour accéder au site complet, tu dois te connecter ou t’inscrire.
            </p>

            <div class="cta-row">
                <a href="AUTH.php" class="btn-big">Je me connecte</a>
                <a href="AUTH.php" class="btn-big btn-outline">Je m’inscris</a>
            </div>
        </section>
    </main>

</body>
</html>
