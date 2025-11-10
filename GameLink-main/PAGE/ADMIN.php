
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <title>Admin | GameLink</title>
  <link rel="stylesheet" href="/GameLink/CSS/HEADER.css" type="text/css"/>
  <link rel="stylesheet" href="/GameLink/CSS/STYLE_ADMIN.css" type="text/css"/>
  <link rel="icon" type="image/png" sizes="32x32" href="/GameLink/ICON/LogoSimple.svg">
</head>

<body class="admin">
  <header>
            <nav class="Menu">
                <a href="">
                    <img  class="logo" src="/GameLink/ICON/LogoComplet.svg" alt="Logo GameLink" width="">
                </a>
                <a href="ACCUEIL.php">ACCUEIL</a>
                <a href="RECHERCHE.php">RECHERCHE</a>
                <a href="COMMUNAUTE.php">COMMUNAUTÉ</a>
                <a href="ADMIN.php">ADMIN</a>
            </nav>
            <a href="">
                <img src="/GameLink/ICON/iconProfil.svg" alt="Logo Profil" width="">
            </a>
  </header>
  
  <main>
    <section class="admin-surface">

      
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

    
        <div class="admin-grid">
          <!-- Colonne 1 : pile de petites cartes -->
          <div class="stack">
            <!-- 1) Signalements journaliers -->
            <div class="card chart">
              <div class="card-title">Signalements journaliers</div>
              <canvas id="chartReports"></canvas>
            </div>

            <!-- 2) Utilisateurs actifs en temps réel -->
            <div class="card chart">
              <div class="card-title">Utilisateurs actifs en temps réel</div>
              <canvas id="chartActive"></canvas>
            </div>
          </div>

          <!-- Colonne 2 : grande carte -->
          <div class="card chart big">
            <div class="card-title">—</div>
            <canvas id="chartBig"></canvas> <!-- (optionnel pour plus tard) -->
          </div>

          <!-- Colonne 3 : sidecard -->
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




  
      </div>

      
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
          <div class="alert-icon">⚠️</div>
          <div class="alert-text">Surveillez la hausse des signalements.</div>
        </aside>
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
<script src="/PA/JS/ADMIN.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6"></script>



</body>
</html>
