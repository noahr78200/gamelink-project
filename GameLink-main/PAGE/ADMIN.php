<?php
// ==========================================
// 🎮 PAGE ADMIN FINALE
// ==========================================

session_start();

// Vérification admin
require_once __DIR__ . '/../INCLUDES/check_admin.php';
require_admin();

// Charger les stats
require_once __DIR__ . '/../INCLUDES/stats.php';

$current_tab = $_GET['tab'] ?? 'dashboard';
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <title>Admin | GameLink</title>
  <link rel="stylesheet" href="../CSS/HEADER.css" type="text/css"/>
 
  <link rel="icon" type="image/png" sizes="32x32" href="../ICON/LogoSimple.svg">
  <style>
  
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
      <a href="ADMIN.php" class="active">ADMIN</a>
    </nav>
    <a href="">
      <img src="../ICON/iconProfil.svg" alt="Logo Profil">
    </a>
  </header>
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
  </style>
  
  <main>
    <!-- Message de bienvenue -->
    <div class="admin-welcome">
      <h2>👋 Bienvenue Admin !</h2>
      <p>Connecté avec l'ID : <strong><?= htmlspecialchars($_SESSION['user_id'] ?? $_SESSION['user_pseudo'] ?? 'Admin') ?></strong></p>
      <p style="font-size: 14px; margin-top: 10px;">
        📊 Total de joueurs : <strong><?= number_format($total_joueurs) ?></strong>
      </p>
    </div>

    <!-- Onglets -->
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
        
        <!-- LES 4 COMPTEURS PRINCIPAUX -->
        <div class="kpi-row">
          
          <!-- COMPTEUR 1 : Inscriptions aujourd'hui -->
          <div class="kpi-card">
            <div class="kpi-label">Inscriptions aujourd'hui :</div>
            <div class="kpi-main">
              <span class="kpi-value"><?= number_format($inscriptions_aujourd_hui ?? 0) ?></span>
            </div>
            <div style="font-size: 12px; color: #99a1b3; margin-top: 5px;">
              Nouveaux joueurs aujourd'hui
            </div>
          </div>

          <!-- COMPTEUR 2 : Connectés MAINTENANT -->
          <div class="kpi-card">
            <div class="kpi-label">Connectés maintenant :</div>
            <div class="kpi-main">
              <span class="kpi-value"><?= number_format($connectes_maintenant ?? 0) ?></span>
            </div>
            <div style="font-size: 12px; color: #99a1b3; margin-top: 5px;">
              Actifs dans les 5 dernières minutes
            </div>
          </div>

          <!-- COMPTEUR 3 : Connectés 24h (NOUVEAU !) -->
          <div class="kpi-card">
            <div class="kpi-label">Connectés 24h :</div>
            <div class="kpi-main">
              <span class="kpi-value"><?= number_format($connectes_24h ?? 0) ?></span>
            </div>
            <div style="font-size: 12px; color: #99a1b3; margin-top: 5px;">
              Actifs dans les dernières 24h
            </div>
          </div>

          <!-- COMPTEUR 4 : Pages vues aujourd'hui -->
          <div class="kpi-card">
            <div class="kpi-label">Pages vues aujourd'hui :</div>
            <div class="kpi-main">
              <span class="kpi-value"><?= number_format($pages_vues_aujourd_hui ?? 0) ?></span>
            </div>
            <div style="font-size: 12px; color: #99a1b3; margin-top: 5px;">
              Total de pages visitées
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
            <div class="card-title">📄 Pages les plus visitées aujourd'hui</div>
            <ul class="toplist">
              <?php if (empty($top_pages)): ?>
                <li style="text-align: center; color: #99a1b3; padding: 20px;">
                  Pas encore de données 📊<br>
                  <small>Les stats apparaîtront dès que tu navigues !</small>
                </li>
              <?php else: ?>
                <?php foreach ($top_pages as $page): ?>
                  <li>
                    <span title="<?= htmlspecialchars($page['page_url']) ?>">
                      <?php 
                      // Afficher juste le nom de la page
                      $page_name = basename(parse_url($page['page_url'], PHP_URL_PATH));
                      echo htmlspecialchars($page_name ?: $page['page_url']);
                      ?>
                    </span>
                    <b><?= number_format($page['views']) ?> vues</b>
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
                  <td colspan="4" style="text-align: center; color: #99a1b3; padding: 20px;">
                    Aucun signalement pour le moment
                  </td>
                </tr>
              </tbody>
            </table>
          </section>

          <aside class="card alert-card">
            <div class="alert-icon">✅</div>
            <div class="alert-text">Tout va bien !</div>
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
          echo '<section class="admin-surface">';
          echo '<div class="card">';
          echo '<div class="card-title">🔒 Gestion Captcha</div>';
          echo '<p style="padding: 20px; text-align: center; color: #99a1b3;">Fichier manage_captcha.php introuvable</p>';
          echo '</div>';
          echo '</section>';
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

  <script src="../JS/ADMIN.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6"></script>
</body>
</html>