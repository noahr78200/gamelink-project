<?php
// ==========================================
// 🎮 PAGE ADMIN AVEC GESTION D'ERREURS
// ==========================================

// Activer l'affichage des erreurs pour voir le problème
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==========================================
// ÉTAPE 1 : VÉRIFIER check_admin.php
// ==========================================

$check_admin_file = __DIR__ . '/../INCLUDES/check_admin.php';

if (!file_exists($check_admin_file)) {
    die("
        <div style='padding: 20px; background: #fee; border: 2px solid red; margin: 20px; border-radius: 10px;'>
            <h2>❌ Fichier manquant</h2>
            <p>Le fichier <code>check_admin.php</code> n'existe pas dans <code>/INCLUDES/</code></p>
            <p><strong>Solution :</strong> Crée ce fichier ou commente la ligne qui l'appelle.</p>
        </div>
    ");
}

try {
    require_once $check_admin_file;
} catch (Exception $e) {
    die("
        <div style='padding: 20px; background: #fee; border: 2px solid red; margin: 20px; border-radius: 10px;'>
            <h2>❌ Erreur dans check_admin.php</h2>
            <p><strong>Message :</strong> " . htmlspecialchars($e->getMessage()) . "</p>
        </div>
    ");
}

// ==========================================
// ÉTAPE 2 : VÉRIFIER LA FONCTION require_admin()
// ==========================================

if (function_exists('require_admin')) {
    try {
        require_admin();
    } catch (Exception $e) {
        die("
            <div style='padding: 20px; background: #fee; border: 2px solid red; margin: 20px; border-radius: 10px;'>
                <h2>❌ Erreur dans require_admin()</h2>
                <p><strong>Message :</strong> " . htmlspecialchars($e->getMessage()) . "</p>
            </div>
        ");
    }
} else {
    // Si la fonction n'existe pas, on fait une vérification simple
    session_start();
    if (!isset($_SESSION['id_joueur'])) {
        die("
            <div style='padding: 20px; background: #fff3cd; border: 2px solid orange; margin: 20px; border-radius: 10px;'>
                <h2>⚠️ Non connecté</h2>
                <p>Tu dois être connecté pour accéder à cette page.</p>
                <p><a href='../PAGE/AUTH.php'>Se connecter</a></p>
            </div>
        ");
    }
}

// ==========================================
// ÉTAPE 3 : CHARGER LES STATS
// ==========================================

$stats_file = __DIR__ . '/../INCLUDES/stats.php';

if (!file_exists($stats_file)) {
    echo "
        <div style='padding: 20px; background: #fff3cd; border: 2px solid orange; margin: 20px; border-radius: 10px;'>
            <h2>⚠️ Fichier stats.php manquant</h2>
            <p>Le fichier <code>stats.php</code> n'existe pas dans <code>/INCLUDES/</code></p>
        </div>
    ";
    // Initialiser des valeurs par défaut
    $joueurs_actifs = 0;
    $connectes_maintenant = 0;
    $pages_vues = 0;
    $top_pages = [];
    $total_joueurs = 0;
} else {
    try {
        include $stats_file;
    } catch (Exception $e) {
        echo "
            <div style='padding: 20px; background: #fee; border: 2px solid red; margin: 20px; border-radius: 10px;'>
                <h2>❌ Erreur dans stats.php</h2>
                <p><strong>Message :</strong> " . htmlspecialchars($e->getMessage()) . "</p>
            </div>
        ";
        // Initialiser des valeurs par défaut
        $joueurs_actifs = 0;
        $connectes_maintenant = 0;
        $pages_vues = 0;
        $top_pages = [];
        $total_joueurs = 0;
    }
}

// Onglet actif
$current_tab = $_GET['tab'] ?? 'dashboard';
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <title>Admin | GameLink</title>
  <link rel="stylesheet" href="../CSS/HEADER.css" type="text/css"/>
  <link rel="stylesheet" href="../CSS/STYLE_ADMIN.css" type="text/css"/>
  <link rel="icon" type="image/png" sizes="32x32" href="../ICON/LogoSimple.svg">
  <style>
    .admin-tabs {
      display: flex;
      gap: 10px;
      margin: 20px 0;
      border-bottom: 2px solid #2a3140;
      padding: 0 20px;
    }
    
    .admin-tab {
      padding: 12px 24px;
      background: transparent;
      border: none;
      color: #99a1b3;
      cursor: pointer;
      font-size: 16px;
      font-weight: 500;
      border-bottom: 3px solid transparent;
      transition: all 0.3s;
      text-decoration: none;
      display: inline-block;
    }
    
    .admin-tab:hover {
      color: #e9ecf1;
      background: rgba(255,255,255,0.05);
    }
    
    .admin-tab.active {
      color: #6ea8fe;
      border-bottom-color: #6ea8fe;
    }
    
    .tab-content {
      display: none;
    }
    
    .tab-content.active {
      display: block;
    }
    
    .admin-welcome {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 20px;
      border-radius: 12px;
      margin: 20px;
      text-align: center;
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    
    .admin-welcome h2 {
      margin: 0 0 10px 0;
      font-size: 24px;
    }
    
    .admin-welcome p {
      margin: 5px 0;
      opacity: 0.9;
    }

    .debug-info {
      background: rgba(255,255,255,0.1);
      padding: 10px;
      border-radius: 8px;
      margin-top: 10px;
      font-size: 12px;
      font-family: monospace;
    }
  </style>
</head>

<body class="admin">
  <header>
    <nav class="Menu">
      <a href="">
        <img class="logo" src="../ICON/LogoComplet.svg" alt="Logo GameLink">
      </a>
      <a href="ACCUEIL.php">ACCUEIL</a>
      <a href="RECHERCHE.php">RECHERCHE</a>
      <a href="COMMUNAUTE.php">COMMUNAUTÉ</a>
      
      <?php if (function_exists('is_admin') && is_admin()): ?>
        <a href="ADMIN.php">ADMIN</a>
      <?php endif; ?>
    </nav>
    <a href="">
      <img src="../ICON/iconProfil.svg" alt="Logo Profil">
    </a>
  </header>
  
  <main>
    <!-- Message de bienvenue -->
    <div class="admin-welcome">
      <h2>👋 Salut Admin !</h2>
      <p>Tu es connecté avec l'ID : <strong><?= htmlspecialchars($_SESSION['id_joueur'] ?? 'N/A') ?></strong></p>
      
      <?php if (isset($total_joueurs)): ?>
        <p style="font-size: 14px; margin-top: 10px;">
          📊 Total de joueurs inscrits : <strong><?= number_format($total_joueurs) ?></strong>
        </p>
      <?php endif; ?>
      
      <!-- Debug info -->
      <div class="debug-info">
        🔍 Debug : 
        Actifs=<?= $joueurs_actifs ?? '?' ?> | 
        Connectés=<?= $connectes_maintenant ?? '?' ?> | 
        Pages=<?= $pages_vues ?? '?' ?>
      </div>
    </div>

    <!-- Menu avec les onglets -->
    <div class="admin-tabs">
      <a href="?tab=dashboard" class="admin-tab <?= $current_tab === 'dashboard' ? 'active' : '' ?>">
        📊 Statistiques
      </a>
      <a href="?tab=captcha" class="admin-tab <?= $current_tab === 'captcha' ? 'active' : '' ?>">
        🔒 Captcha
      </a>
      <a href="?tab=users" class="admin-tab <?= $current_tab === 'users' ? 'active' : '' ?>">
        👥 Utilisateurs
      </a>
    </div>

    <!-- ONGLET STATISTIQUES -->
    <div class="tab-content <?= $current_tab === 'dashboard' ? 'active' : '' ?>">
      <section class="admin-surface">
        
        <!-- LES 3 COMPTEURS -->
        <div class="kpi-row">
          
          <div class="kpi-card">
            <div class="kpi-label">Joueurs actifs aujourd'hui :</div>
            <div class="kpi-main">
              <span class="kpi-value">
                <?= isset($joueurs_actifs) ? number_format($joueurs_actifs) : '0' ?>
              </span>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-label">Connectés maintenant :</div>
            <div class="kpi-main">
              <span class="kpi-value">
                <?= isset($connectes_maintenant) ? number_format($connectes_maintenant) : '0' ?>
              </span>
            </div>
          </div>

          <div class="kpi-card">
            <div class="kpi-label">Pages vues aujourd'hui :</div>
            <div class="kpi-main">
              <span class="kpi-value">
                <?= isset($pages_vues) ? number_format($pages_vues) : '0' ?>
              </span>
            </div>
          </div>
        </div>

        <!-- Grille avec les graphiques -->
        <div class="admin-grid">
          <div class="stack">
            <div class="card chart">
              <div class="card-title">Signalements journaliers</div>
              <canvas id="chartReports"></canvas>
            </div>
            <div class="card chart">
              <div class="card-title">Utilisateurs actifs</div>
              <canvas id="chartActive"></canvas>
            </div>
          </div>

          <div class="card chart big">
            <div class="card-title">Évolution du trafic</div>
            <canvas id="chartBig"></canvas>
          </div>

          <!-- Top 5 des pages -->
          <aside class="card sidecard">
            <div class="card-title">📄 Pages les plus visitées</div>
            <ul class="toplist">
              <?php if (empty($top_pages)): ?>
                <li style="text-align: center; color: #99a1b3; padding: 20px;">
                  Pas encore de données 📊<br>
                  <small>Navigue sur le site !</small>
                </li>
              <?php else: ?>
                <?php foreach ($top_pages as $page): ?>
                  <li>
                    <span><?= htmlspecialchars($page['page_url']) ?></span>
                    <b><?= number_format($page['views']) ?></b>
                  </li>
                <?php endforeach; ?>
              <?php endif; ?>
            </ul>
          </aside>
        </div>

        <!-- Signalements -->
        <div class="reports-grid">
          <section class="card">
            <div class="card-title">Contenu signalé</div>
            <table class="report-table">
              <thead>
                <tr>
                  <th>Joueurs</th>
                  <th>Contenu</th>
                  <th>Raison</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <tr class="report-row">
                  <td><span class="avatar"></span>Exemple</td>
                  <td>« Message test »</td>
                  <td>Inapproprié</td>
                  <td>AUJOURD'HUI</td>
                </tr>
              </tbody>
            </table>
          </section>

          <aside class="card alert-card">
            <div class="alert-icon">⚠️</div>
            <div class="alert-text">Surveillez les signalements</div>
          </aside>
        </div>
      </section>
    </div>

    <!-- ONGLET CAPTCHA -->
    <div class="tab-content <?= $current_tab === 'captcha' ? 'active' : '' ?>">
      <?php 
      $captcha_file = __DIR__ . '/manage_captcha.php';
      if (file_exists($captcha_file)) {
          include $captcha_file;
      } else {
          echo '<section class="admin-surface"><div class="card"><p style="padding: 20px; text-align: center;">Le fichier manage_captcha.php n\'existe pas</p></div></section>';
      }
      ?>
    </div>

    <!-- ONGLET UTILISATEURS -->
    <div class="tab-content <?= $current_tab === 'users' ? 'active' : '' ?>">
      <section class="admin-surface">
        <div class="card">
          <div class="card-title">Gestion des utilisateurs</div>
          <p style="padding: 20px; text-align: center; color: #99a1b3;">
            🚧 En construction
          </p>
        </div>
      </section>
    </div>

  </main>

  <!-- Modal -->
  <div class="modal-overlay" id="reportModal" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
      <header class="modal-header">
        <h3 id="modalTitle">Détail du signalement</h3>
        <button class="modal-close" type="button" aria-label="Fermer">&times;</button>
      </header>

      <section class="modal-body">
        <div class="modal-grid">
          <div class="modal-block">
            <div class="modal-label">Auteur</div>
            <div class="modal-value" id="m-offender">—</div>
          </div>
          <div class="modal-block">
            <div class="modal-label">Message</div>
            <div class="modal-value mono" id="m-message">—</div>
          </div>
          <div class="modal-block">
            <div class="modal-label">Raison</div>
            <div class="modal-value" id="m-reason">—</div>
          </div>
          <div class="modal-block">
            <div class="modal-label">Date</div>
            <div class="modal-value" id="m-date">—</div>
          </div>
        </div>
      </section>

      <footer class="modal-footer">
        <button type="button" class="btn ghost modal-close">Fermer</button>
        <div class="right-actions">
          <button type="button" class="btn warn">Avertir</button>
          <button type="button" class="btn danger">Sanction</button>
          <button type="button" class="btn primary">Marquer traité</button>
        </div>
      </footer>
    </div>
  </div>

  <script src="../JS/ADMIN.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6"></script>
</body>
</html>