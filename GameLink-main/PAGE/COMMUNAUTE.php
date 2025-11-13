<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="description"content="ACCUEIL GameLink">
        <title>Communauté | GameLink</title>
        <link rel="stylesheet" href="../CSS/HEADER.css" type="text/css"/>
        <link rel="icon" type="image/png" sizes="32x32" href="../ICON/LogoSimple.svg">
    </head>
    <body>
       <?php
    require_once __DIR__ . '/../INCLUDES/check_admin.php';
?>
<header>
    <nav class="Menu">
        <a href="">
            <img class="logo" src="../ICON/LogoComplet.svg" alt="Logo GameLink" width="">
        </a>
        <a href="ACCUEIL.php">ACCUEIL</a>
        <a href="RECHERCHE.php">RECHERCHE</a>
        <a href="COMMUNAUTE.php">COMMUNAUTÉ</a>
        
        <?php if (is_admin()): ?>
            <!-- Ce lien ne s'affiche QUE pour l'ID joueur 7 -->
            <a href="ADMIN.php">ADMIN</a>
        <?php endif; ?>
    </nav>
    <a href="">
        <img src="../ICON/iconProfil.svg" alt="Logo Profil" width="">
    </a>
</header>
        <main>
         
        </main>
         
    </body>