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

// Récupération des paramètres
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Requête de base
$sql = "SELECT 
    j.id_joueur,
    j.pseudo,
    j.email,
    j.avatar_config,
    j.date_inscription,
    j.bio,
    j.pays,
    ua.last_activity
FROM joueur j
LEFT JOIN user_activity ua ON j.id_joueur = ua.user_id
WHERE 1=1";

$params = [];

// Filtrage par recherche
if (!empty($search)) {
    $sql .= " AND (j.pseudo LIKE :search OR j.email LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

// Compter le total pour la pagination
$count_sql = "SELECT COUNT(*) as total FROM joueur j WHERE 1=1";
if (!empty($search)) {
    $count_sql .= " AND (j.pseudo LIKE :search OR j.email LIKE :search)";
}

try {
    // Total d'utilisateurs
    $stmt_count = $pdo->prepare($count_sql);
    if (!empty($search)) {
        $stmt_count->execute([':search' => '%' . $search . '%']);
    } else {
        $stmt_count->execute();
    }
    $total_users = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_users / $per_page);

    // Debug
    echo "<!-- DEBUG: Total users = $total_users -->";
    echo "<!-- DEBUG: SQL = $sql -->";

    // Récupération des utilisateurs avec pagination
    $sql .= " ORDER BY j.date_inscription DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<!-- DEBUG: Users count = " . count($users) . " -->";

} catch (Exception $e) {
    $users = [];
    $total_users = 0;
    $total_pages = 0;
    $error_message = $e->getMessage();
    error_log("Erreur récupération utilisateurs : " . $error_message);
    echo "<!-- DEBUG ERROR: " . htmlspecialchars($error_message) . " -->";
}

// Fonction pour calculer le temps écoulé
function time_elapsed($datetime) {
    if (empty($datetime)) return 'Jamais';
    
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) return $diff->y . ' an' . ($diff->y > 1 ? 's' : '');
    if ($diff->m > 0) return $diff->m . ' mois';
    if ($diff->d > 0) return $diff->d . ' jour' . ($diff->d > 1 ? 's' : '');
    if ($diff->h > 0) return $diff->h . ' heure' . ($diff->h > 1 ? 's' : '');
    if ($diff->i > 0) return $diff->i . ' min';
    return 'À l\'instant';
}

// Fonction pour générer l'avatar
function generate_avatar($avatar_config, $pseudo) {
    if (!empty($avatar_config)) {
        // Si tu as une config d'avatar personnalisée, traite-la ici
        $config = json_decode($avatar_config, true);
        if (isset($config['url'])) {
            return '<img src="' . htmlspecialchars($config['url']) . '" class="user-avatar" alt="Avatar">';
        }
    }
    
    // Avatar par défaut avec initiale
    $initial = strtoupper(mb_substr($pseudo, 0, 1));
    $colors = ['#667eea', '#764ba2', '#f093fb', '#4facfe', '#43e97b', '#fa709a'];
    $color = $colors[ord($initial) % count($colors)];
    
    return '<div class="user-avatar-default" style="background: ' . $color . '">' . 
           htmlspecialchars($initial) . 
           '</div>';
}

// Fonction pour déterminer le statut en ligne
function get_online_status($last_activity) {
    if (empty($last_activity)) return 'offline';
    
    $now = new DateTime();
    $last = new DateTime($last_activity);
    $diff = $now->getTimestamp() - $last->getTimestamp();
    
    if ($diff < 300) return 'online'; // 5 minutes
    if ($diff < 3600) return 'away'; // 1 heure
    return 'offline';
}
?>

<!-- Interface utilisateurs -->
<section class="admin-surface">
    <div class="card users-card">
        <div class="card-title" style="display: flex; justify-content: space-between; align-items: center;">
            <span>👥 Liste des utilisateurs (<?= number_format($total_users) ?>)</span>
            <div class="users-search-wrapper">
                <input 
                    type="text" 
                    id="userSearch" 
                    class="users-search-input" 
                    placeholder="🔍 Rechercher par pseudo ou email..."
                    value="<?= htmlspecialchars($search) ?>"
                >
            </div>
        </div>

        <?php if (empty($users)): ?>
            <div style="padding: 40px; text-align: center; color: var(--muted);">
                <?php if (!empty($search)): ?>
                    😕 Aucun utilisateur trouvé pour "<?= htmlspecialchars($search) ?>"
                <?php else: ?>
                    📭 Aucun utilisateur inscrit pour le moment
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="users-table-wrapper">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">Avatar</th>
                            <th style="width: 20%;">Pseudo</th>
                            <th style="width: 25%;">Email</th>
                            <th style="width: 15%;">Pays</th>
                            <th style="width: 15%;">Inscription</th>
                            <th style="width: 15%;">Dernière activité</th>
                            <th style="width: 10%; text-align: center;">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <?php 
                                $status = get_online_status($user['last_activity']);
                                $status_label = $status === 'online' ? 'En ligne' : ($status === 'away' ? 'Absent' : 'Hors ligne');
                                $last_activity_text = time_elapsed($user['last_activity']);
                            ?>
                            <tr class="user-row" data-user-id="<?= $user['id_joueur'] ?>">
                                <td>
                                    <div class="user-avatar-wrapper">
                                        <?= generate_avatar($user['avatar_config'], $user['pseudo']) ?>
                                        <span class="status-indicator status-<?= $status ?>"></span>
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
                                    <div class="user-country">
                                        <?php if (!empty($user['pays'])): ?>
                                            <?= htmlspecialchars($user['pays']) ?>
                                        <?php else: ?>
                                            <span style="color: var(--muted); font-style: italic;">Non renseigné</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="user-date">
                                        <?php 
                                            $date = new DateTime($user['date_inscription']);
                                            echo $date->format('d/m/Y');
                                        ?>
                                        <small style="display: block; color: var(--muted); font-size: 11px;">
                                            Il y a <?= time_elapsed($user['date_inscription']) ?>
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <div class="user-activity">
                                        <?= $last_activity_text ?>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="status-badge status-badge-<?= $status ?>">
                                        <?= $status_label ?>
                                    </span>
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
                        (<?= number_format($total_users) ?> utilisateur<?= $total_users > 1 ? 's' : '' ?>)
                    </div>
                    <div class="pagination-controls">
                        <?php if ($page > 1): ?>
                            <a href="?tab=users&page=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                               class="pagination-btn">
                                ⏮️ Début
                            </a>
                            <a href="?tab=users&page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                               class="pagination-btn">
                                ◀️ Précédent
                            </a>
                        <?php endif; ?>

                        <?php 
                        // Afficher quelques numéros de page
                        $start = max(1, $page - 2);
                        $end = min($total_pages, $page + 2);
                        
                        for ($i = $start; $i <= $end; $i++): 
                        ?>
                            <a href="?tab=users&page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                               class="pagination-btn <?= $i === $page ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?tab=users&page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                               class="pagination-btn">
                                Suivant ▶️
                            </a>
                            <a href="?tab=users&page=<?= $total_pages ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                               class="pagination-btn">
                                Fin ⏭️
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<style>
/* ===== STYLES POUR LA GESTION DES UTILISATEURS ===== */

.users-card {
    min-height: 400px;
}

/* Barre de recherche */
.users-search-wrapper {
    position: relative;
}

.users-search-input {
    width: 320px;
    padding: 10px 16px;
    border: 1px solid var(--border);
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.05);
    color: var(--text);
    font-size: 14px;
    transition: all 0.3s ease;
}

.users-search-input:focus {
    outline: none;
    border-color: var(--accent);
    background: rgba(255, 255, 255, 0.08);
    box-shadow: 0 0 0 3px rgba(110, 168, 255, 0.1);
}

/* Tableau des utilisateurs */
.users-table-wrapper {
    overflow-x: auto;
    margin-top: 16px;
}

.users-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: transparent;
}

.users-table thead th {
    text-align: left;
    color: var(--muted);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 12px 14px;
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
    background: linear-gradient(90deg, rgba(110, 168, 255, 0.08), transparent 60%);
    transform: translateX(2px);
}

.users-table tbody td {
    padding: 14px;
    color: var(--text);
}

/* Avatar */
.user-avatar-wrapper {
    position: relative;
    display: inline-block;
}

.user-avatar,
.user-avatar-default {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--border);
}

.user-avatar-default {
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
    color: white;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

/* Indicateur de statut */
.status-indicator {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid var(--panel);
    box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.3);
}

.status-indicator.status-online {
    background: #34d399;
    box-shadow: 0 0 8px rgba(52, 211, 153, 0.6);
    animation: pulse-online 2s infinite;
}

.status-indicator.status-away {
    background: #fbbf24;
}

.status-indicator.status-offline {
    background: #6b7280;
}

@keyframes pulse-online {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

/* Badge de statut */
.status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.status-badge-online {
    color: #34d399;
    background: rgba(52, 211, 153, 0.15);
    border: 1px solid rgba(52, 211, 153, 0.4);
}

.status-badge-away {
    color: #fbbf24;
    background: rgba(251, 191, 36, 0.15);
    border: 1px solid rgba(251, 191, 36, 0.4);
}

.status-badge-offline {
    color: #9ca3af;
    background: rgba(156, 163, 175, 0.15);
    border: 1px solid rgba(156, 163, 175, 0.3);
}

/* Textes */
.user-pseudo {
    font-weight: 600;
    font-size: 15px;
}

.user-email {
    color: var(--muted);
    font-size: 13px;
}

.user-date,
.user-activity,
.user-country {
    font-size: 13px;
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
}

.pagination-controls {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.pagination-btn {
    padding: 8px 14px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.04));
    color: var(--text);
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.pagination-btn:hover {
    background: linear-gradient(180deg, rgba(110, 168, 255, 0.15), rgba(110, 168, 255, 0.08));
    border-color: var(--accent);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.pagination-btn.active {
    background: linear-gradient(180deg, rgba(110, 168, 255, 0.25), rgba(110, 168, 255, 0.15));
    border-color: var(--accent);
    color: var(--accent);
    font-weight: 700;
}

/* Responsive */
@media (max-width: 1200px) {
    .users-search-input {
        width: 250px;
    }
}

@media (max-width: 768px) {
    .card-title {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 12px;
    }
    
    .users-search-input {
        width: 100%;
    }
    
    .users-table {
        font-size: 12px;
    }
    
    .users-table th,
    .users-table td {
        padding: 10px 8px;
    }
    
    .pagination-controls {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
// Recherche en temps réel avec debounce
(function() {
    const searchInput = document.getElementById('userSearch');
    if (!searchInput) return;

    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        
        searchTimeout = setTimeout(() => {
            const searchValue = this.value.trim();
            const currentUrl = new URL(window.location.href);
            
            if (searchValue) {
                currentUrl.searchParams.set('search', searchValue);
            } else {
                currentUrl.searchParams.delete('search');
            }
            
            // Réinitialiser à la page 1 lors d'une recherche
            currentUrl.searchParams.set('page', '1');
            
            // Rediriger vers la nouvelle URL
            window.location.href = currentUrl.toString();
        }, 500); // Attendre 500ms après que l'utilisateur arrête de taper
    });

    // Effet visuel lors du focus
    searchInput.addEventListener('focus', function() {
        this.parentElement.style.transform = 'scale(1.02)';
    });

    searchInput.addEventListener('blur', function() {
        this.parentElement.style.transform = 'scale(1)';
    });
})();
</script>