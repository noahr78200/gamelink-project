<?php
// ==========================================
// 🎮 PAGE ADMIN FINALE
// ==========================================

session_start();

// Vérification admin
require_once __DIR__ . '/../INCLUDES/check_admin.php';
require_admin();

// Connexion BDD
require_once __DIR__ . '/../DATA/DBConfig.php';

// Charger les stats
require_once __DIR__ . '/../INCLUDES/stats.php';

// ===== Message à la une (BDD) =====
$headlineTitle = '';
$headlineBody  = '';

// 1) Charger le message actuel
try {
    $stmt = $pdo->prepare("SELECT title, body FROM homepage_headline WHERE id = 1");
    $stmt->execute();
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $headlineTitle = $row['title'];
        $headlineBody  = $row['body'];
    }
} catch (Exception $e) {
    // TODO : logger l'erreur en prod si besoin
    // echo 'Erreur headline SELECT : ' . $e->getMessage();
}

// 2) Si le formulaire "Message à la une" est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['headline_form'])) {
    $headlineTitle = trim($_POST['headline_title'] ?? '');
    $headlineBody  = trim($_POST['headline_body']  ?? '');

    if ($headlineTitle !== '' || $headlineBody !== '') {
        $sql = "
            INSERT INTO homepage_headline (id, title, body)
            VALUES (1, :title, :body)
            ON DUPLICATE KEY UPDATE
                title = VALUES(title),
                body  = VALUES(body)
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':title' => $headlineTitle,
            ':body'  => $headlineBody,
        ]);
    }

    // Redirection pour éviter le re-submit et rester sur l'onglet Edition
    header('Location: ADMIN.php?tab=edition&headline_saved=1');
    exit;
}

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

<?php include __DIR__ . '/../INCLUDES/header.php'; ?>

<body class="admin">
 
  <main>
    <!-- Message de bienvenue -->
    <div class="admin-welcome">
      <h2> Bienvenue Admin !</h2>
      <p>Connecté avec l'ID : <strong><?= htmlspecialchars($_SESSION['user_id'] ?? $_SESSION['user_pseudo'] ?? 'Admin') ?></strong></p>
      <p style="font-size: 14px; margin-top: 10px;">
       Total de joueurs : <strong><?= number_format($total_joueurs) ?></strong>
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
      <a href="?tab=edition" class="admin-tab <?= $current_tab === 'edition' ? 'active' : '' ?>">
        📝 Edition
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
                    data-id="rep_1005"
                    data-offender="CircuitLogiqueMaVie"
                    data-message="fais ton chronographe sale fou"
                    data-reason="Inapproprié"
                    data-date="2025-10-06T14:12:00+02:00"
                    data-game="Processing"
                    data-chat="Chat d'équipe"
                    data-reporter="TweeDleDee"
                    data-strikes2025="1">
                  <td><span class="avatar"></span>CircuitLogiqueMaVie</td>
                  <td>« fais ton chronographe sale fou »</td>
                  <td>Inapproprié</td>
                  <td>OCT 6</td>
                </tr>

                <tr class="report-row"
                    data-id="rep_1006"
                    data-offender="Clio2NordPasDeCalais"
                    data-message="toi jvais t'attraper fdp ezzzz"
                    data-reason="Inapproprié"
                    data-date="2025-10-06T14:12:00+02:00"
                    data-game="Rocket League"
                    data-chat="Chat d'équipe"
                    data-reporter="YTUUZI"
                    data-strikes2025="1">
                  <td><span class="avatar"></span>Clio2NordPasDeCalais</td>
                  <td>« toi jvais t'attraper fdp ezzzz »</td>
                  <td>Inapproprié</td>
                  <td>OCT 6</td>
                </tr>

                <tr class="report-row"
                    data-id="rep_1007"
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
                    data-id="rep_1008"
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
      <?php 
      $users_file = __DIR__ . '/users_management.php';
      if (file_exists($users_file)) {
          include $users_file;
      } else {
          echo '<section class="admin-surface">';
          echo '<div class="card">';
          echo '<div class="card-title">👥 Gestion des utilisateurs</div>';
          echo '<p style="padding: 20px; text-align: center; color: #99a1b3;">Fichier users_management.php introuvable</p>';
          echo '</div>';
          echo '</section>';
      }
      ?>
    </div>

    <!-- ONGLET EDITION -->
    <div class="tab-content <?= $current_tab === 'edition' ? 'active' : '' ?>">
      <section class="admin-surface">
        <!-- Message à la une -->
        <section class="card headline-card" id="headline">
          <div class="card-title">Message à la une</div>

          <form id="headlineForm" class="nl-form" method="post" action="?tab=edition">
            <input type="hidden" name="headline_form" value="1">

            <div class="nl-field">
              <label for="headlineTitle">Titre du message</label>
              <input
                id="headlineTitle"
                name="headline_title"
                type="text"
                placeholder="Ex : Tournoi GameLink ce week-end !"
                required
                value="<?= htmlspecialchars($headlineTitle) ?>"
              >
            </div>

            <div class="nl-field">
              <label for="headlineBody">Corps du message</label>
              <textarea
                id="headlineBody"
                name="headline_body"
                rows="4"
                placeholder="Détail de l'événement, date, heure, récompenses…"
                required
              ><?= htmlspecialchars($headlineBody) ?></textarea>
              <small class="muted">
                Ce texte sera affiché sur la page d'accueil pour informer les joueurs.
              </small>
            </div>

            <div class="nl-actions">
              <button type="submit" class="btn primary">Publier / mettre à jour</button>
            </div>
          </form>
        </section>

        <!-- Newsletter -->
        <section class="card newsletter-card" id="newsletter">
  <div class="card-title">Newsletter — Edition</div>
   <form id="newsletterForm" class="nl-form" method="post" action="../API/newsletter_send.php">
  <div class="nl-field">
    <label for="nlSubject">Objet du mail</label>
    <input
      id="nlSubject"
      name="subject"
      type="text"
      required
      placeholder="Ex: Mise à jour GameLink v2.1"
    >
  </div>

  <div class="nl-field">
    <label for="nlTitle">Titre dans l'email</label>
    <input
      id="nlTitle"
      name="title"
      type="text"
      required
      placeholder="Découvrez les nouveautés !"
    >
  </div>

  <div class="nl-field">
    <label for="nlBody">Corps du mail</label>
    <textarea
      id="nlBody"
      name="body"
      rows="10"
      required
      placeholder="Texte du message."
    ></textarea>
    <small class="muted">
      Cette zone servira pour le contenu principal de ta newsletter.
    </small>
  </div>

  <div class="nl-actions">
    <input
      type="email"
      id="nlTestEmail"
      name="test_email"
      placeholder="Envoyer un test à… (optionnel)"
    >
    <div class="spacer"></div>

    <!-- Deux boutons submit, avec un name="action" différent -->
    <button type="submit" class="btn warn" name="action" value="test">
      Envoyer un test
    </button>
    <button type="submit" class="btn primary" name="action" value="all">
      Envoyer à tous
    </button>
  </div>
</form>
     
 

</section>

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
            <div class="modal-label">Pédigré (2025)</div>
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