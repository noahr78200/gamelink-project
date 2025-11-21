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

// Récupérer le nom de la page actuelle pour mettre le lien en surbrillance
$current_page = basename($_SERVER['PHP_SELF']);
?>
<header>
    <nav class="Menu">
        <a href="ACCUEIL.php">
            <img class="logo" src="../ICON/LogoComplet.svg" alt="Logo GameLink">
        </a>
        <a href="ACCUEIL.php" class="<?= $current_page === 'ACCUEIL.php' ? 'active' : '' ?>">
            ACCUEIL
        </a>
        <a href="RECHERCHE.php" class="<?= $current_page === 'RECHERCHE.php' ? 'active' : '' ?>">
            RECHERCHE
        </a>

        <a href="COMMUNAUTE.php" class="<?= $current_page === 'COMMUNAUTE.php' ? 'active' : '' ?>">
            COMMUNAUTÉ
        </a>
         <a href="CHAT.php" class="<?= $current_page === 'CHAT.php' ? 'active' : '' ?>">
            CHAT
        </a>
        
        <?php if (is_admin()): ?>
            <!-- Ce lien ne s'affiche QUE pour les admins -->
            <a href="ADMIN.php" class="<?= $current_page === 'ADMIN.php' ? 'active' : '' ?>">
                ADMIN
            </a>
        <?php endif; ?>
    </nav>
    <a href="PROFIL.php">
        <img src="../ICON/iconProfil.svg" alt="Logo Profil">
    </a>
</header>