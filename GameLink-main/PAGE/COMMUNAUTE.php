<?php
// PAGE/COMMUNAUTE.php - Page communauté avec groupes et forums
session_start();

// Protection : Redirection si non connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?login_required=1');
    exit;
}

require_once __DIR__ . '/../DATA/DBConfig.php';

$user_id = $_SESSION['user_id'];
$user_pseudo = $_SESSION['user_pseudo'];

// Récupérer les 6 groupes par défaut
try {
    $stmt = $pdo->query("
        SELECT c.*, 
               (SELECT COUNT(*) FROM adhesion WHERE id_communaute = c.id_communaute AND statut = 'actif') as nb_membres,
               (SELECT COUNT(*) FROM adhesion WHERE id_communaute = c.id_communaute AND id_joueur = $user_id AND statut = 'actif') as is_member
        FROM communaute c
        ORDER BY c.id_communaute
        LIMIT 6
    ");
    $groupes = $stmt->fetchAll();
} catch (PDOException $e) {
    $groupes = [];
    error_log("Erreur récupération groupes : " . $e->getMessage());
}

// Récupérer les derniers sujets de forum
try {
    $stmt = $pdo->query("
        SELECT p.*, j.pseudo, 
               (SELECT COUNT(*) FROM commentaire WHERE id_publication = p.id_publication) as nb_commentaires
        FROM publication p
        JOIN joueur j ON p.id_joueur = j.id_joueur
        WHERE p.id_communaute IS NULL
        ORDER BY p.date_creation DESC
        LIMIT 10
    ");
    $forums = $stmt->fetchAll();
} catch (PDOException $e) {
    $forums = [];
    error_log("Erreur récupération forums : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Communauté | GameLink</title>
    <link rel="stylesheet" href="../CSS/HEADER.css">
    <link rel="stylesheet" href="../CSS/STYLE_COMMUNAUTE.css">
    <link rel="icon" type="image/svg+xml" href="../ICON/LogoSimple.svg">
</head>
 <?php  include __DIR__ . '/../INCLUDES/header.php'; 
    ?>
<body>
    <!-- HEADER -->
 
    <main class="communaute-container">
        
        <!-- SECTION GROUPES -->
        <section class="groupes-section">
            <div class="section-header">
                <h1>🎮 Groupes de Jeux</h1>
                <p>Rejoins un groupe et discute avec d'autres joueurs passionnés !</p>
            </div>

            <div class="groupes-grid">
                <?php foreach ($groupes as $groupe): ?>
                    <div class="groupe-card" data-groupe-id="<?= $groupe['id_communaute'] ?>">
                        <!-- IMAGE du groupe (à personnaliser) -->
                        <div class="groupe-image">
                            <img src="../IMAGES/groupes/groupe_<?= $groupe['id_communaute'] ?>.jpg" 
                                 alt="<?= htmlspecialchars($groupe['nom']) ?>"
                                 onerror="this.src='../IMAGES/groupes/default.jpg'">
                        </div>
                        
                        <!-- INFO du groupe -->
                        <div class="groupe-info">
                            <h3><?= htmlspecialchars($groupe['nom']) ?></h3>
                            <p class="groupe-desc"><?= htmlspecialchars($groupe['description']) ?></p>
                            <div class="groupe-stats">
                                <span>👥 <?= $groupe['nb_membres'] ?> membres</span>
                            </div>
                        </div>

                        <!-- BOUTON -->
                        <div class="groupe-actions">
                            <?php if ($groupe['is_member']): ?>
                                <button class="btn btn-secondary btn-quitter" data-groupe-id="<?= $groupe['id_communaute'] ?>">
                                    ✓ Membre
                                </button>
                                <button class="btn btn-primary btn-ouvrir-chat" data-groupe-id="<?= $groupe['id_communaute'] ?>">
                                    💬 Ouvrir le chat
                                </button>
                            <?php else: ?>
                                <button class="btn btn-primary btn-rejoindre" data-groupe-id="<?= $groupe['id_communaute'] ?>">
                                    + Rejoindre
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- SECTION FORUMS -->
        <section class="forums-section">
            <div class="section-header">
                <h2>💬 Forums</h2>
                <a href="forum_creer.php" class="btn btn-primary">+ Créer un sujet</a>
            </div>

            <div class="forums-list">
                <?php if (empty($forums)): ?>
                    <div class="empty-state">
                        <p>Aucun sujet pour le moment. Sois le premier à créer un sujet !</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($forums as $forum): ?>
                        <div class="forum-item">
                            <div class="forum-content">
                                <h3><a href="forum_detail.php?id=<?= $forum['id_publication'] ?>"><?= htmlspecialchars($forum['titre']) ?></a></h3>
                                <p class="forum-preview"><?= htmlspecialchars(substr($forum['contenu'], 0, 150)) ?>...</p>
                                <div class="forum-meta">
                                    <span class="forum-author">Par <?= htmlspecialchars($forum['pseudo']) ?></span>
                                    <span class="forum-date"><?= date('d/m/Y à H:i', strtotime($forum['date_creation'])) ?></span>
                                    <span class="forum-comments">💬 <?= $forum['nb_commentaires'] ?> réponses</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

    </main>

    <!-- MODAL CHAT (Bulle qui s'ouvre) -->
    <div id="chatModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="chatGroupeNom">Nom du groupe</h3>
                <button class="btn-close" onclick="fermerChat()">×</button>
            </div>
            
            <div class="modal-body">
                <!-- Messages du groupe -->
                <div id="chatMessages" class="chat-messages">
                    <div class="loading">Chargement des messages...</div>
                </div>

                <!-- Formulaire d'envoi -->
                <form id="chatForm" class="chat-form">
                    <input type="hidden" id="chatGroupeId" name="groupe_id">
                    <textarea id="chatInput" name="message" placeholder="Écris ton message..." rows="3" required></textarea>
                    <button type="submit" class="btn btn-primary">Envoyer</button>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-danger btn-quitter-modal" id="btnQuitterGroupe">Quitter le groupe</button>
            </div>
        </div>
    </div>

    <!-- OVERLAY (fond sombre derrière le modal) -->
    <div id="modalOverlay" class="modal-overlay" onclick="fermerChat()"></div>

    <script src="../JS/COMMUNAUTE.js"></script>
</body>
</html>