<?php
// Démarrer la session si elle n'existe pas
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure les fichiers nécessaires
require_once __DIR__ . '/../INCLUDES/check_admin.php';
require_once __DIR__ . '/../INCLUDES/avatar_svg.php';
require_once __DIR__ . '/../DATA/DBConfig.php';

// Récupérer le nom de la page actuelle
$current_page = basename($_SERVER['PHP_SELF']);

$header_avatar_config = null;
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT avatar_config FROM joueur WHERE id_joueur = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $result = $stmt->fetch();
        if ($result) {
            $header_avatar_config = $result['avatar_config'];
        }
    } catch (Exception $e) {
        $header_avatar_config = null;
    }
}
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
            <a href="ADMIN.php" class="<?= $current_page === 'ADMIN.php' ? 'active' : '' ?>">
                ADMIN
            </a>
        <?php endif; ?>
    </nav>
    <a href="PROFIL.php" style="line-height: 0;">
        <?php echo generateAvatarSVG($header_avatar_config, 35); ?>
    </a>
    <script src="../JS/snake.js"></script>
</header>
<script src="../JS/logo_menu_phone.js"></script>