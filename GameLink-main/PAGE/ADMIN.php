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
  <link rel="stylesheet" href="../CSS/STYLE_ADMIN.css" type="text/css"/>
  <link rel="stylesheet" href="../CSS/STYLE_ADMIN2.css" type="text/css"/>
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
                <th>Users</th>
                <th>Contenu</th>
                <th>Raison</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
                  <tr class="report-row"
                      data-id="rep_1001"
                      data-offender="Zepkenio77"
                      data-message="salopard mid diff"
                      data-reason="Inapproprié"
                      data-date="2025-10-06T14:12:00+02:00"
                      data-game="League of Legends"
                      data-chat="Chat d'équipe"
                      data-reporter="ModArno"
                      data-strikes2025="1">
                    <td><span class="avatar"></span>Zepkenio77</td>
                    <td>« salopard mid diff »</td>
                    <td>Inapproprié</td>
                    <td>OCT 6</td>
                  </tr>

                  <tr class="report-row"
                      data-id="rep_1002"
                      data-offender="YUIAuber"
                      data-message="report noob botlane"
                      data-reason="Inapproprié"
                      data-date="2025-10-06T12:40:00+02:00"
                      data-game="League of Legends"
                      data-chat="Chat général"
                      data-reporter="Kara"
                      data-strikes2025="0">
                    <td><span class="avatar"></span>YUIAuber</td>
                    <td>« report noob botlane »</td>
                    <td>Inapproprié</td>
                    <td>OCT 6</td>
                  </tr>

                  <tr class="report-row"
                      data-id="rep_1003"
                      data-offender="SamsiLaFrappe"
                      data-message="esp3ce de retard"
                      data-reason="Inapproprié"
                      data-date="2025-10-06T11:05:00+02:00"
                      data-game="CS:GO"
                      data-chat="Message privé"
                      data-reporter="Rakun"
                      data-strikes2025="4">
                    <td><span class="avatar"></span>SamsiLaFrappe</td>
                    <td>« esp3ce de retard »</td>
                    <td>Inapproprié</td>
                    <td>OCT 6</td>
                  </tr>

                  <tr class="report-row"
                      data-id="rep_1004"
                      data-offender="CiscoKillerDu92"
                      data-message="go troll dog"
                      data-reason="Inapproprié"
                      data-date="2025-10-06T10:29:00+02:00"
                      data-game="Valorant"
                      data-chat="Chat d'équipe"
                      data-reporter="Zlp"
                      data-strikes2025="2">
                    <td><span class="avatar"></span>CiscoKillerDu92</td>
                    <td>« go troll dog »</td>
                    <td>Inapproprié</td>
                    <td>OCT 6</td>
                  </tr>
                  <tr class="report-row"
                      data-id="rep_1001"
                      data-offender="CircuitLogiqueMaVie"
                      data-message="fais ton chronographe sale fou"
                      data-reason="Inapproprié"
                      data-date="2025-10-06T14:12:00+02:00"
                      data-game="Processing"
                      data-chat="Chat d'équipe"
                      data-reporter="TweeDleDee"
                      data-strikes2025="1">
                    <td><span class="avatar"></span>Zepkenio77</td>
                    <td>« fais ton chronographe sale fou »</td>
                    <td>Inapproprié</td>
                    <td>OCT 6</td>
                  </tr>
                  <tr class="report-row"
                      data-id="rep_1001"
                      data-offender="Clio2NordPasDeCalais"
                      data-message="toi jvais t'attraper fdp ezzzz"
                      data-reason="Inapproprié"
                      data-date="2025-10-06T14:12:00+02:00"
                      data-game="Rocket League "
                      data-chat="Chat d'équipe"
                      data-reporter="YTUUZI"
                      data-strikes2025="1">
                    <td><span class="avatar"></span>Clio2NordPasDeCalais</td>
                    <td>« toi jvais t'attraper fdp ezzzz »</td>
                    <td>Inapproprié</td>
                    <td>OCT 6</td>
                  </tr>
                  <tr class="report-row"
                      data-id="rep_1001"
                      data-offender="AZAMAT"
                      data-message="DECALE AU B STFU"
                      data-reason="Inapproprié"
                      data-date="2025-10-06T14:12:00+02:00"
                      data-game="Valorant"
                      data-chat="Chat d'équipe"
                      data-reporter="ModArno"
                      data-strikes2025="1">
                    <td><span class="avatar"></span>AZAMAT</td>
                    <td>« DECALE AU B STFU »</td>
                    <td>Inapproprié</td>
                    <td>OCT 6</td>
                  </tr>
                  <tr class="report-row"
                      data-id="rep_1001"
                      data-offender="GandalfBriveLaGaillarde"
                      data-message="Bot Gap french retard"
                      data-reason="Inapproprié"
                      data-date="2025-10-06T14:12:00+02:00"
                      data-game="League of Legends"
                      data-chat="Chat d'équipe"
                      data-reporter="temmo4life"
                      data-strikes2025="1">
                    <td><span class="avatar"></span>GandalfBriveLaGaillarde</td>
                    <td>« Bot Gap french retard»</td>
                    <td>Inapproprié</td>
                    <td>OCT 6</td>
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