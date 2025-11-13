<?php
// ==========================================
// FICHIER : ADMIN.php
// BUT : Page d'administration avec onglets
// ==========================================

// PROTECTION : Seul l'ID 7 peut accéder
require_once __DIR__ . '/../INCLUDES/check_admin.php';
require_admin();

// Récupérer l'onglet actif (par défaut : dashboard)
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
    
    /* Contenu des onglets */
    .tab-content {
      display: none;
    }
    
    .tab-content.active {
      display: block;
    }
    
    /* Message de bienvenue */
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
        <img class="logo" src="../ICON/LogoComplet.svg" alt="Logo GameLink" width="">
      </a>
      <a href="ACCUEIL.php">ACCUEIL</a>
      <a href="RECHERCHE.php">RECHERCHE</a>
      <a href="COMMUNAUTE.php">COMMUNAUTÉ</a>
      
      <?php if (is_admin()): ?>
        <a href="ADMIN.php">ADMIN</a>
      <?php endif; ?>
    </nav>
    <a href="">
      <img src="../ICON/iconProfil.svg" alt="Logo Profil" width="">
    </a>
  </header>
  
  <main>
    <!-- Message de bienvenue -->
    <div class="admin-welcome">
      <h2>👋 Bienvenue Admin !</h2>
      <p>Vous êtes connecté avec l'ID joueur : <strong><?= htmlspecialchars($_SESSION['user_id']) ?></strong></p>
    </div>

    <!-- Onglets de navigation -->
    <div class="admin-tabs">
      <a href="?tab=dashboard" class="admin-tab <?= $current_tab === 'dashboard' ? 'active' : '' ?>">
        📊 Dashboard
      </a>
      <a href="?tab=captcha" class="admin-tab <?= $current_tab === 'captcha' ? 'active' : '' ?>">
        🔒 Gestion Captcha
      </a>
      <a href="?tab=users" class="admin-tab <?= $current_tab === 'users' ? 'active' : '' ?>">
        👥 Utilisateurs
      </a>
    </div>

    <!-- ONGLET 1 : DASHBOARD -->
    <div class="tab-content <?= $current_tab === 'dashboard' ? 'active' : '' ?>" id="dashboard-tab">
      <section class="admin-surface">
        <!-- KPI Row -->
        <div class="kpi-row">
          <div class="kpi-card">
            <div class="kpi-label">DAU :</div>
            <div class="kpi-main">
              <span class="kpi-value">1,785</span>
              <span class="delta up">▲ +1,4%</span>
            </div>
          </div>
          <div class="kpi-card">
            <div class="kpi-label">Connexions (temps réel) :</div>
            <div class="kpi-main">
              <span class="kpi-value">2,762</span>
              <span class="delta down">▼ −9,2%</span>
            </div>
          </div>
          <div class="kpi-card">
            <div class="kpi-label">Nouvelles inscriptions :</div>
            <div class="kpi-main">
              <span class="kpi-value">2,762</span>
              <span class="delta up">▲ +6,02%</span>
            </div>
          </div>
          <div class="kpi-card">
            <div class="kpi-label">Signalements (jours) :</div>
            <div class="kpi-main">
              <span class="kpi-value">171</span>
              <span class="delta up">▲ +71,0%</span>
            </div>
          </div>
        </div>

        <!-- Admin Grid -->
        <div class="admin-grid">
          <div class="stack">
            <div class="card chart">
              <div class="card-title">Signalements journaliers</div>
              <canvas id="chartReports"></canvas>
            </div>
            <div class="card chart">
              <div class="card-title">Utilisateurs actifs en temps réel</div>
              <canvas id="chartActive"></canvas>
            </div>
          </div>

          <div class="card chart big">
            <div class="card-title">—</div>
            <canvas id="chartBig"></canvas>
          </div>

          <aside class="card sidecard">
            <div class="card-title">TOP jeux joués actuellement</div>
            <ul class="toplist">
              <li><span>League of Legends</span><b>1250</b></li>
              <li><span>CS:GO</span><b>1077</b></li>
              <li><span>FIFA</span><b>977</b></li>
              <li><span>Valorant</span><b>905</b></li>
              <li><span>Minecraft</span><b>859</b></li>
              <li><span>Fortnite</span><b>623</b></li>
              <li><span>Roblox</span><b>567</b></li>
            </ul>
          </aside>
        </div>

        <!-- Reports Grid -->
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
              </tbody>
            </table>
          </section>

          <aside class="card alert-card">
            <div class="alert-icon">⚠️</div>
            <div class="alert-text">Surveillez la hausse des signalements.</div>
          </aside>
        </div>
      </section>
    </div>

    <!-- ONGLET 2 : GESTION CAPTCHA -->
    <div class="tab-content <?= $current_tab === 'captcha' ? 'active' : '' ?>" id="captcha-tab">
      <?php include __DIR__ . '../PAGE/manage_captcha.php'; ?>
    </div>

    <!-- ONGLET 3 : UTILISATEURS (à développer) -->
    <div class="tab-content <?= $current_tab === 'users' ? 'active' : '' ?>" id="users-tab">
      <section class="admin-surface">
        <div class="card">
          <div class="card-title">Gestion des utilisateurs</div>
          <p style="padding: 20px; text-align: center; color: #99a1b3;">
            🚧 Cette section est en cours de développement
          </p>
        </div>
      </section>
    </div>

  </main>

  <!-- Modal détaillé : Signalement -->
  <div class="modal-overlay" id="reportModal" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
      <header class="modal-header">
        <h3 id="modalTitle">Détail du signalement</h3>
        <button class="modal-close" type="button" aria-label="Fermer">&times;</button>
      </header>

      <section class="modal-body">
        <div class="modal-grid">
          <div class="modal-block">
            <div class="modal-label">Auteur (utilisateur signalé)</div>
            <div class="modal-value" id="m-offender">—</div>
          </div>
          <div class="modal-block">
            <div class="modal-label">Message signalé</div>
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
          <div class="modal-block">
            <div class="modal-label">Jeu</div>
            <div class="modal-value" id="m-game">—</div>
          </div>
          <div class="modal-block">
            <div class="modal-label">Type de chat</div>
            <div class="modal-value" id="m-chat">—</div>
          </div>
          <div class="modal-block">
            <div class="modal-label">Plaignant</div>
            <div class="modal-value" id="m-reporter">—</div>
          </div>
          <div class="modal-block">
            <div class="modal-label">Pédigrée (2025)</div>
            <div class="modal-value">
              <span id="m-strikes">0 signalement</span>
              <span class="badge badge-ok" id="m-badge" hidden>Historique propre</span>
            </div>
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