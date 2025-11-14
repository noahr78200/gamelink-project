<?php
// ==========================================
// 🎮 PAGE ADMIN - Version Simple
// ==========================================
// Cette page montre les statistiques de ton site

// PROTECTION : Seuls les admins peuvent voir cette page
require_once __DIR__ . '/../INCLUDES/check_admin.php';
require_admin();

// On récupère les statistiques
include __DIR__ . '/../INCLUDES/3_get_stats.php';

// On récupère l'onglet actif
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
    /* Styles pour les onglets */
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
      margin: 0;
      opacity: 0.9;
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
      
      <?php if (is_admin()): ?>
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
      <h2>👋 Bienvenue Admin !</h2>
      <p>Tu es connecté avec l'ID : <strong><?= htmlspecialchars($_SESSION['user_id']) ?></strong></p>
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
        
        <!-- Les 4 compteurs principaux -->
        <div class="kpi-row">
          
          <!-- COMPTEUR 1 : Visiteurs aujourd'hui -->
          <div class="kpi-card">
            <div class="kpi-label">Visiteurs aujourd'hui :</div>
            <div class="kpi-main">
              <span class="kpi-value"><?= $stats['dau']['value'] ?></span>
              <span class="delta <?= $stats['dau']['trend'] ?>">
                <?= $stats['dau']['trend'] === 'up' ? '▲' : '▼' ?> 
                <?= abs($stats['dau']['delta']) ?>%
              </span>
            </div>
          </div>

          <!-- COMPTEUR 2 : Connectés maintenant -->
          <div class="kpi-card">
            <div class="kpi-label">Connectés maintenant :</div>
            <div class="kpi-main">
              <span class="kpi-value"><?= $stats['online_users']['value'] ?></span>
              <span class="delta <?= $stats['online_users']['trend'] ?>">
                <?= $stats['online_users']['trend'] === 'up' ? '▲' : '▼' ?> 
                <?= abs($stats['online_users']['delta']) ?>%
              </span>
            </div>
          </div>

          <!-- COMPTEUR 3 : Nouvelles inscriptions -->
          <div class="kpi-card">
            <div class="kpi-label">Inscriptions aujourd'hui :</div>
            <div class="kpi-main">
              <span class="kpi-value"><?= $stats['new_registrations']['value'] ?></span>
              <span class="delta <?= $stats['new_registrations']['trend'] ?>">
                <?= $stats['new_registrations']['trend'] === 'up' ? '▲' : '▼' ?> 
                <?= abs($stats['new_registrations']['delta']) ?>%
              </span>
            </div>
          </div>

          <!-- COMPTEUR 4 : Pages vues -->
          <div class="kpi-card">
            <div class="kpi-label">Pages vues aujourd'hui :</div>
            <div class="kpi-main">
              <span class="kpi-value"><?= $stats['page_views']['value'] ?></span>
              <span class="delta <?= $stats['page_views']['trend'] ?>">
                <?= $stats['page_views']['trend'] === 'up' ? '▲' : '▼' ?> 
                <?= abs($stats['page_views']['delta']) ?>%
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

          <!-- Liste des pages les plus visitées -->
          <aside class="card sidecard">
            <div class="card-title">📄 Pages les plus visitées</div>
            <ul class="toplist">
              <?php if (empty($stats['page_views']['top_pages'])): ?>
                <li style="text-align: center; color: #99a1b3; padding: 20px;">
                  Pas encore de données 📊<br>
                  <small>Navigue sur le site pour voir des stats !</small>
                </li>
              <?php else: ?>
                <?php foreach ($stats['page_views']['top_pages'] as $page): ?>
                  <li>
                    <span><?= htmlspecialchars($page['page_url']) ?></span>
                    <b><?= $page['views'] ?></b>
                  </li>
                <?php endforeach; ?>
              <?php endif; ?>
            </ul>
          </aside>
        </div>

        <!-- Tableau des signalements -->
        <div class="reports-grid">
          <section class="card">
            <div class="card-title">Contenu signalé</div>
            <table class="report-table">
              <thead>
                <tr>
                  <th>Users</th>
                  <th>Contenu</th>
                  <th>Raison</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><span class="avatar"></span>Exemple1</td>
                  <td>« message inapproprié »</td>
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
      <?php include __DIR__ . '/manage_captcha.php'; ?>
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

  <script src="../JS/ADMIN.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6"></script>
</body>
</html>