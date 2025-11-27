<?php
// ==========================================
// 👥 GESTION DES UTILISATEURS - ADMIN
// ==========================================

// Vérifier que PDO est disponible
if (!isset($pdo)) {
    echo '<section class="admin-surface">';
    echo '<div class="card">';
    echo '<div class="card-title">👥 Gestion des utilisateurs</div>';
    echo '<p style="padding: 40px; text-align: center; color: #f87171;">⚠️ Erreur : Connexion à la base de données non disponible</p>';
    echo '</div>';
    echo '</section>';
    return;
}

// Paramètres de pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Variables pour stocker les résultats
$users = array();
$total_users = 0;
$total_pages = 0;

try {
    // ÉTAPE 1 : Compter le total d'utilisateurs
    $count_sql = "SELECT COUNT(*) as total FROM joueur";
    $stmt_count = $pdo->prepare($count_sql);
    $stmt_count->execute();
    
    $count_result = $stmt_count->fetch(PDO::FETCH_ASSOC);
    $total_users = $count_result ? intval($count_result['total']) : 0;
    $total_pages = $total_users > 0 ? ceil($total_users / $per_page) : 1;

    // ÉTAPE 2 : Récupérer les utilisateurs
    $sql = "SELECT 
        id_joueur,
        pseudo,
        email,
        date_inscription
    FROM joueur 
    ORDER BY date_inscription DESC 
    LIMIT ? OFFSET ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(1, $per_page, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erreur SQL users_management: " . $e->getMessage());
    $error_display = "Erreur lors de la récupération des utilisateurs : " . htmlspecialchars($e->getMessage());
}

// Fonction pour calculer le temps écoulé
function formatTimeAgo($datetime) {
    if (empty($datetime)) {
        return 'Jamais';
    }
    
    try {
        $now = new DateTime();
        $past = new DateTime($datetime);
        $diff = $now->diff($past);
        
        if ($diff->y > 0) {
            return $diff->y . ' an' . ($diff->y > 1 ? 's' : '');
        }
        if ($diff->m > 0) {
            return $diff->m . ' mois';
        }
        if ($diff->d > 0) {
            return $diff->d . ' jour' . ($diff->d > 1 ? 's' : '');
        }
        if ($diff->h > 0) {
            return $diff->h . 'h';
        }
        if ($diff->i > 0) {
            return $diff->i . 'min';
        }
        return "À l'instant";
    } catch (Exception $e) {
        return 'Inconnu';
    }
}
?>

<!-- Interface utilisateurs -->
<section class="admin-surface">
    <div class="card users-card">
        <div class="card-title">
            👥 Liste des utilisateurs (<?= number_format($total_users) ?>)
        </div>

        <?php if (isset($error_display)): ?>
            <div style="padding: 20px; margin: 20px; background: rgba(248, 113, 113, 0.1); border: 1px solid rgba(248, 113, 113, 0.3); border-radius: 10px; color: #f87171;">
                ⚠️ <?= $error_display ?>
            </div>
        <?php endif; ?>

        <?php if (empty($users) && !isset($error_display)): ?>
            <div style="padding: 60px 20px; text-align: center; color: var(--muted);">
                <div style="font-size: 48px; margin-bottom: 16px;">📭</div>
                <div style="font-size: 18px;">Aucun utilisateur inscrit</div>
            </div>
        <?php else: ?>
            <div class="users-table-wrapper">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th style="width: 10%;">ID</th>
                            <th style="width: 25%;">Pseudo</th>
                            <th style="width: 35%;">Email</th>
                            <th style="width: 30%;">Date d'inscription</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr class="user-row">
                                <td>
                                    <div class="user-id">
                                        #<?= $user['id_joueur'] ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="user-pseudo">
                                        <?= htmlspecialchars($user['pseudo']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="user-email">
                                        <?= htmlspecialchars($user['email']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="user-date">
                                        <?php 
                                            $date = new DateTime($user['date_inscription']);
                                            echo $date->format('d/m/Y à H:i');
                                        ?>
                                        <small style="display: block; color: var(--muted); font-size: 11px; margin-top: 2px;">
                                            Il y a <?= formatTimeAgo($user['date_inscription']) ?>
                                        </small>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="users-pagination">
                    <div class="pagination-info">
                        Page <?= $page ?> sur <?= $total_pages ?> 
                        • <?= number_format($total_users) ?> utilisateur<?= $total_users > 1 ? 's' : '' ?>
                    </div>
                    <div class="pagination-controls">
                        <?php if ($page > 1): ?>
                            <a href="?tab=users&page=1" class="pagination-btn" title="Première page">
                                ⏮️
                            </a>
                            <a href="?tab=users&page=<?= $page - 1 ?>" class="pagination-btn" title="Page précédente">
                                ◀️
                            </a>
                        <?php endif; ?>

                        <?php 
                        // Afficher 5 numéros de page max
                        $start = max(1, $page - 2);
                        $end = min($total_pages, $page + 2);
                        
                        for ($i = $start; $i <= $end; $i++): 
                        ?>
                            <a href="?tab=users&page=<?= $i ?>" 
                               class="pagination-btn <?= $i === $page ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?tab=users&page=<?= $page + 1 ?>" class="pagination-btn" title="Page suivante">
                                ▶️
                            </a>
                            <a href="?tab=users&page=<?= $total_pages ?>" class="pagination-btn" title="Dernière page">
                                ⏭️
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<style>
/* ==================== STYLES UTILISATEURS ==================== */

.users-card {
    min-height: 400px;
}

.card-title {
    margin-bottom: 20px;
}

/* Tableau */
.users-table-wrapper {
    overflow-x: auto;
    margin-top: 0;
    border-radius: 12px;
    border: 1px solid var(--border);
}

.users-table {
    width: 100%;
    border-collapse: collapse;
    background: transparent;
}

.users-table thead th {
    text-align: left;
    color: var(--muted);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 14px 16px;
    background: linear-gradient(180deg, #1d2330, #181e2a);
    border-bottom: 2px solid var(--border);
    position: sticky;
    top: 0;
    z-index: 10;
}

.users-table tbody tr {
    transition: all 0.2s ease;
    border-bottom: 1px solid var(--border);
}

.users-table tbody tr:hover {
    background: linear-gradient(90deg, rgba(110, 168, 255, 0.08), transparent 70%);
}

.users-table tbody tr:last-child {
    border-bottom: none;
}

.users-table tbody td {
    padding: 16px;
    color: var(--text);
    vertical-align: middle;
}

/* Textes */
.user-id {
    font-family: ui-monospace, monospace;
    font-weight: 700;
    font-size: 15px;
    color: var(--accent);
}

.user-pseudo {
    font-weight: 600;
    font-size: 15px;
    color: var(--text);
}

.user-email {
    color: var(--muted);
    font-size: 14px;
    word-break: break-all;
}

.user-date {
    font-size: 14px;
    color: var(--valueMild);
}

/* Pagination */
.users-pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid var(--border);
    flex-wrap: wrap;
    gap: 16px;
}

.pagination-info {
    color: var(--muted);
    font-size: 14px;
    font-weight: 500;
}

.pagination-controls {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.pagination-btn {
    min-width: 36px;
    height: 36px;
    padding: 0 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.04));
    color: var(--text);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.pagination-btn:hover {
    background: linear-gradient(180deg, rgba(110, 168, 255, 0.2), rgba(110, 168, 255, 0.1));
    border-color: var(--accent);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(110, 168, 255, 0.2);
}

.pagination-btn.active {
    background: linear-gradient(180deg, rgba(110, 168, 255, 0.3), rgba(110, 168, 255, 0.2));
    border-color: var(--accent);
    color: var(--accent);
    font-weight: 700;
    cursor: default;
    box-shadow: 0 0 0 3px rgba(110, 168, 255, 0.15);
}

.pagination-btn.active:hover {
    transform: none;
}

/* Responsive */
@media (max-width: 768px) {
    .users-table {
        font-size: 13px;
    }
    
    .users-table th,
    .users-table td {
        padding: 12px 10px;
    }
    
    .pagination-controls {
        width: 100%;
        justify-content: center;
    }
    
    .pagination-info {
        width: 100%;
        text-align: center;
    }
}
</style>