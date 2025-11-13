<?php
// ==========================================
// FICHIER : manage_captcha.php
// BUT : Gérer les questions du captcha (pour les admins)
// ==========================================

// Démarrer la session
session_start();

// ÉTAPE 1 : Vérifier que l'utilisateur est connecté
// --------------------------------------------------
if (!isset($_SESSION['user_id'])) {
  // Si pas connecté, retour à la page de connexion
  header('Location: AUTH.php');
  exit;
}

// Définir où se trouve le fichier JSON
const JSON_FILE = __DIR__ . '/../DATA/captcha_bank.json';

// ====================
// FONCTIONS UTILES
// ====================

// Fonction pour charger les questions
function load_bank() {
  // Si le fichier n'existe pas, retourner un tableau vide
  if (!file_exists(JSON_FILE)) {
    return [];
  }
  
  // Lire le fichier
  $txt = file_get_contents(JSON_FILE);
  
  // Convertir en tableau PHP
  $arr = json_decode($txt, true);
  
  // Vérifier que c'est bien un tableau
  return is_array($arr) ? $arr : [];
}

// Fonction pour sauvegarder les questions
function save_bank($arr) {
  // Convertir le tableau en JSON et sauvegarder
  file_put_contents(JSON_FILE, json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// ÉTAPE 2 : Charger les questions existantes
// -------------------------------------------
$bank = load_bank();
$msg = '';

// ÉTAPE 3 : Traiter les actions (ajouter, supprimer, activer/désactiver)
// -----------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  
  // ACTION : Ajouter une question
  if ($action === 'add') {
    $q = trim($_POST['q'] ?? '');
    $a = trim($_POST['a'] ?? '');
    
    // Vérifier que les champs ne sont pas vides
    if ($q !== '' && $a !== '') {
      // Ajouter la nouvelle question
      $bank[] = ['q' => $q, 'a' => $a, 'enabled' => true];
      save_bank($bank);
      $msg = "Question ajoutée avec succès !";
    } else {
      $msg = "Merci de remplir la question ET la réponse.";
    }
  } 
  // ACTION : Supprimer une question
  elseif ($action === 'delete') {
    $i = (int)($_POST['idx'] ?? -1);
    
    // Vérifier que l'index existe
    if (isset($bank[$i])) {
      // Supprimer la question
      array_splice($bank, $i, 1);
      save_bank($bank);
      $msg = "Question supprimée.";
    }
  } 
  // ACTION : Activer/désactiver une question
  elseif ($action === 'toggle') {
    $i = (int)($_POST['idx'] ?? -1);
    
    // Vérifier que l'index existe
    if (isset($bank[$i])) {
      // Inverser le statut (actif <-> désactivé)
      $bank[$i]['enabled'] = !$bank[$i]['enabled'];
      save_bank($bank);
      $msg = "Statut modifié.";
    }
  }
  
  // Recharger les questions après modification
  $bank = load_bank();
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gérer Captcha | GameLink</title>
  <link rel="stylesheet" href="../CSS/HEADER.css">
  <style>
    /* Styles de base */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Roboto', sans-serif;
      background: #f5f6fa;
      padding: 20px;
    }
    
    .container {
      max-width: 1200px;
      margin: 0 auto;
      background: white;
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    h1 {
      color: #667eea;
      margin-bottom: 30px;
      text-align: center;
    }
    
    h2 {
      color: #333;
      margin-top: 40px;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid #667eea;
    }
    
    /* Message de succès */
    .success-message {
      background: #51cf66;
      color: white;
      padding: 15px;
      border-radius: 5px;
      margin-bottom: 20px;
      text-align: center;
    }
    
    /* Boîte d'information */
    .info-box {
      background: #e7f5ff;
      border-left: 4px solid #667eea;
      padding: 15px;
      margin-bottom: 20px;
    }
    
    /* Formulaire */
    .form-group {
      margin-bottom: 20px;
    }
    
    label {
      display: block;
      margin-bottom: 8px;
      font-weight: bold;
      color: #333;
    }
    
    input[type="text"] {
      width: 100%;
      padding: 12px;
      border: 2px solid #ddd;
      border-radius: 5px;
      font-size: 1em;
    }
    
    input[type="text"]:focus {
      outline: none;
      border-color: #667eea;
    }
    
    /* Boutons */
    button {
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.3s;
    }
    
    button[type="submit"] {
      background: #667eea;
      color: white;
    }
    
    button[type="submit"]:hover {
      background: #5568d3;
    }
    
    .btn-toggle {
      background: #ffa500;
      color: white;
      padding: 8px 15px;
      margin-right: 5px;
    }
    
    .btn-delete {
      background: #ff6b6b;
      color: white;
      padding: 8px 15px;
    }
    
    .btn-toggle:hover {
      background: #ff8c00;
    }
    
    .btn-delete:hover {
      background: #ff5252;
    }
    
    /* Tableau */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    
    th, td {
      padding: 15px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    
    th {
      background: #667eea;
      color: white;
      font-weight: bold;
    }
    
    tr:hover {
      background: #f8f9fa;
    }
    
    /* Statuts */
    .status-active {
      color: #51cf66;
      font-weight: bold;
    }
    
    .status-inactive {
      color: #ff6b6b;
      font-weight: bold;
    }
    
    /* Lien de retour */
    .back-link {
      display: inline-block;
      margin-top: 30px;
      color: #667eea;
      text-decoration: none;
      font-weight: bold;
    }
    
    .back-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>🔒 Gestion des questions Captcha</h1>

    <!-- Afficher le message de succès/erreur -->
    <?php if ($msg): ?>
      <div class="success-message"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- Boîte d'information -->
    <div class="info-box">
      <strong>ℹ️ Information :</strong> Pour accepter plusieurs réponses valides, séparez-les par le caractère <code>|</code><br>
      Exemple : <code>paris|Paris|PARIS</code>
    </div>

    <!-- Formulaire pour ajouter une question -->
    <h2>➕ Ajouter une question</h2>
    <form method="post">
      <input type="hidden" name="action" value="add">
      
      <div class="form-group">
        <label for="question">Question</label>
        <input type="text" id="question" name="q" placeholder="Ex: Quelle est la capitale de la France ?" required>
      </div>
      
      <div class="form-group">
        <label for="answer">Réponse(s) acceptée(s)</label>
        <input type="text" id="answer" name="a" placeholder="Ex: paris (ou paris|Paris pour plusieurs variantes)" required>
      </div>
      
      <button type="submit">Ajouter la question</button>
    </form>

    <!-- Liste des questions existantes -->
    <h2>📋 Questions existantes (<?= count($bank) ?>)</h2>
    
    <?php if (empty($bank)): ?>
      <p style="text-align: center; color: #999; margin: 40px 0;">Aucune question enregistrée pour le moment.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th style="width: 50px;">#</th>
            <th>Question</th>
            <th style="width: 200px;">Réponse(s)</th>
            <th style="width: 100px;">Statut</th>
            <th style="width: 200px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($bank as $i => $row): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><?= htmlspecialchars($row['q']) ?></td>
              <td><code><?= htmlspecialchars($row['a']) ?></code></td>
              <td>
                <?php if (!empty($row['enabled'])): ?>
                  <span class="status-active">✓ Active</span>
                <?php else: ?>
                  <span class="status-inactive">✗ Désactivée</span>
                <?php endif; ?>
              </td>
              <td>
                <!-- Bouton Activer/Désactiver -->
                <form method="post" style="display:inline;">
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="idx" value="<?= $i ?>">
                  <button type="submit" class="btn-toggle">
                    <?= !empty($row['enabled']) ? 'Désactiver' : 'Activer' ?>
                  </button>
                </form>
                
                <!-- Bouton Supprimer -->
                <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer cette question ?')">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="idx" value="<?= $i ?>">
                  <button type="submit" class="btn-delete">Supprimer</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <a href="ADMIN.php" class="back-link">← Retour à l'administration</a>
  </div>
</body>
</html>