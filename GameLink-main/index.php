<?php
// index.php - Page d'accueil publique
session_start();

// Récupérer le message flash si existant
$flash_message = $_SESSION['flash_index'] ?? null;
unset($_SESSION['flash_index']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="GameLink - Votre plateforme de jeux vidéo">
    <title>GameLink - Accueil</title>
    <link rel="stylesheet" href="CSS/HEADER.css" type="text/css"/>
    <link rel="stylesheet" href="CSS/STYLE_INDEX.css" type="text/css"/>
    <link rel="icon" type="image/svg+xml" href="ICON/LogoSimple.svg">
</head>
<body>
    <!-- HEADER -->
    <header>
        <nav class="Menu">
            <a href="index.php">
                <img class="logo" src="ICON/LogoComplet.svg" alt="Logo GameLink">
            </a>
            <a href="index.php">ACCUEIL</a>
            
                <a href="PAGE/RECHERCHE.php">RECHERCHE</a>
                <a href="PAGE/COMMUNAUTE.php">COMMUNAUTÉ</a>
                <a href="PAGE/ACCUEIL.php">MON ESPACE</a>
           
        </nav>
        <?php if (isset($_SESSION['user_id'])): ?>
            <div style="display: flex; align-items: center; gap: 15px;">
                <span style="color: white;">Bonjour, <?= htmlspecialchars($_SESSION['user_pseudo']) ?></span>
                <a href="INCLUDES/logout.php">
                    <img src="ICON/iconProfil.svg" alt="Logo Profil" width="40">
                </a>
            </div>
        <?php else: ?>
            <a href="PAGE/AUTH.php" class="btn btn-primary" style="margin-right: 20px;">Connexion / Inscription</a>
        <?php endif; ?>
    </header>
    
    <main>
        <!-- Messages Flash -->
        <?php if ($flash_message && isset($flash_message['error'])): ?>
            <div class="flash-message error-message">
                ⚠ <?= htmlspecialchars($flash_message['error']) ?>
            </div>
        <?php endif; ?>

        <?php if ($flash_message && isset($flash_message['success'])): ?>
            <div class="flash-message success-message">
                ✓ <?= htmlspecialchars($flash_message['success']) ?>
            </div>
        <?php endif; ?>

        <!-- BANNIÈRE DE BIENVENUE -->
        <section class="welcome-banner">
            <h1>🎮 Bienvenue sur GameLink</h1>
            <p>Découvrez, notez et partagez vos jeux vidéo préférés avec une communauté passionnée</p>
            
            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="cta-buttons">
                    <a href="PAGE/AUTH.php?tab=signup" class="btn-big btn-primary">Créer un compte</a>
                    <a href="PAGE/AUTH.php" class="btn-big btn-secondary">Se connecter</a>
                </div>
            <?php else: ?>
                <div class="cta-buttons">
                    <a href="PAGE/ACCUEIL.php" class="btn-big btn-primary">Mon espace</a>
                    <a href="PAGE/RECHERCHE.php" class="btn-big btn-secondary">Rechercher des jeux</a>
                </div>
            <?php endif; ?>
        </section>

        <!-- SECTION 1 : CATALOGUE DE JEUX (Image à gauche) -->
        <section class="feature-section">
            <div class="feature-image">
                <!-- Remplacez le placeholder par votre image -->
                <!-- <img src="IMAGES/catalogue-jeux.jpg" alt="Catalogue de jeux"> -->
                <div class="image-placeholder">📚 Catalogue</div>
            </div>
            <div class="feature-content">
                <div class="feature-number">Fonctionnalité 01</div>
                <h2>Un catalogue de jeux immense</h2>
                <p>
                    Explorez une bibliothèque complète de milliers de jeux vidéo. 
                    De l'indépendant au triple A, du rétro aux dernières sorties, 
                    découvrez tous les jeux classés par genre, plateforme et année de sortie.
                </p>
                <a href="PAGE/RECHERCHE.php" class="feature-link">Explorer le catalogue</a>
            </div>
        </section>

        <!-- SECTION 2 : SYSTÈME DE NOTATION (Image à droite) -->
        <section class="feature-section">
            <div class="feature-image">
                <!-- Remplacez le placeholder par votre image -->
                <!-- <img src="IMAGES/notation.jpg" alt="Système de notation"> -->
                <div class="image-placeholder">⭐ Notes</div>
            </div>
            <div class="feature-content">
                <div class="feature-number">Fonctionnalité 02</div>
                <h2>Notez et partagez vos avis</h2>
                <p>
                    Donnez votre avis sur les jeux que vous avez joués. Attribuez des notes, 
                    écrivez des critiques détaillées et découvrez ce que la communauté pense 
                    de vos titres préférés. Vos avis aident les autres joueurs à faire leurs choix.
                </p>
                <a href="PAGE/AUTH.php" class="feature-link">Commencer à noter</a>
            </div>
        </section>

        <!-- SECTION 3 : COMMUNAUTÉ/FORUMS (Image à gauche) -->
        <section class="feature-section">
            <div class="feature-image">
                <!-- Remplacez le placeholder par votre image -->
                <!-- <img src="IMAGES/communaute.jpg" alt="Communauté"> -->
                <div class="image-placeholder">💬 Communauté</div>
            </div>
            <div class="feature-content">
                <div class="feature-number">Fonctionnalité 03</div>
                <h2>Rejoignez la communauté</h2>
                <p>
                    Échangez avec des milliers de passionnés sur nos forums. Participez à des 
                    discussions, créez des groupes thématiques, organisez des événements gaming 
                    et trouvez des partenaires de jeu qui partagent vos intérêts.
                </p>
                <a href="PAGE/COMMUNAUTE.php" class="feature-link">Découvrir la communauté</a>
            </div>
        </section>

        <!-- SECTION 4 : PLAYLISTS PERSONNALISÉES (Image à droite) -->
        <section class="feature-section">
            <div class="feature-image">
                <!-- Remplacez le placeholder par votre image -->
                <!-- <img src="IMAGES/playlists.jpg" alt="Playlists"> -->
                <div class="image-placeholder">📋 Playlists</div>
            </div>
            <div class="feature-content">
                <div class="feature-number">Fonctionnalité 04</div>
                <h2>Organisez votre bibliothèque</h2>
                <p>
                    Créez des playlists personnalisées pour organiser vos jeux. Triez-les par 
                    catégories : à faire, en cours, terminés, favoris... Suivez votre progression 
                    et ne perdez jamais de vue les jeux que vous voulez découvrir.
                </p>
                <a href="PAGE/AUTH.php" class="feature-link">Créer mes playlists</a>
            </div>
        </section>

    </main>
     
</body>
</html>