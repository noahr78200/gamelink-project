<?php
// ==========================================
// FICHIER : header.php
// BUT : Header réutilisable pour toutes les pages
// ==========================================

// Démarrer la session si elle n'existe pas
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure le fichier de vérification admin
require_once __DIR__ . '/../INCLUDES/check_admin.php';
?>
<header>
    <nav class="Menu">
        <a href="">
            <img class="logo" src="/GameLink/ICON/LogoComplet.svg" alt="Logo GameLink" width="">
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
        <img src="/GameLink/ICON/iconProfil.svg" alt="Logo Profil" width="">
    </a>
</header>