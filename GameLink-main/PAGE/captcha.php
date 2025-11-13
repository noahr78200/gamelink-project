<?php
// ==========================================
// FICHIER : captcha.php
// BUT : Vérifier que l'utilisateur est humain
// ==========================================

// Démarrer la session
session_start();

// ÉTAPE 1 : Vérifier qu'un utilisateur essaie de se connecter
// ------------------------------------------------------------
if (!isset($_SESSION['pending_user_id'])) {
  // Si personne n'essaie de se connecter, retour à la page de connexion
  header('Location: /PAGE/AUTH.php');
  exit;
}

// ÉTAPE 2 : Initialiser le compteur de tentatives
// ------------------------------------------------
if (!isset($_SESSION['captcha_attempts'])) {
  $_SESSION['captcha_attempts'] = 0;
}

// Définir où se trouve le fichier des questions
define('CAPTCHA_JSON', __DIR__ . '/../DATA/captcha_bank.json');

// ====================
// FONCTIONS UTILES
// ====================

// Fonction pour charger les questions depuis le fichier JSON
function load_bank() {
  // Si le fichier n'existe pas, retourner un tableau vide
  if (!file_exists(CAPTCHA_JSON)) {
    return [];
  }
  
  // Lire le contenu du fichier
  $txt = @file_get_contents(CAPTCHA_JSON);
  if ($txt === false) {
    return [];
  }
  
  // Convertir le JSON en tableau PHP
  $arr = @json_decode($txt, true);
  return is_array($arr) ? $arr : [];
}

// Fonction pour normaliser le texte (enlever accents, majuscules, etc.)
function normalize_text($texte) {
  // Enlever les espaces au début et à la fin
  $texte = trim((string)$texte);

  // Convertir en minuscules
  if (function_exists('mb_strtolower')) {
    $texte = mb_strtolower($texte, 'UTF-8');
  } else {
    $texte = strtolower($texte);
  }

  // Enlever les accents
  if (function_exists('iconv')) {
    $trans = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texte);
    if ($trans !== false) {
      $texte = $trans;
    }
  }

  // Garder seulement les lettres et chiffres
  $texte = preg_replace('/[^a-z0-9]+/i', ' ', $texte);
  $texte = preg_replace('/\s+/', ' ', $texte);
  return trim($texte);
}

// Fonction pour vérifier si la réponse est correcte
function is_answer_correct($reponse_utilisateur, $reponse_attendue) {
  // Normaliser la réponse de l'utilisateur
  $u = normalize_text($reponse_utilisateur);
  
  // Séparer les réponses possibles (séparées par |)
  $candidates = array_map('trim', explode('|', (string)$reponse_attendue));
  
  // Vérifier si une des réponses correspond
  foreach ($candidates as $c) {
    if ($u === normalize_text($c)) {
      return true;
    }
  }
  return false;
}

// Fonction pour choisir une question au hasard
function pick_random_question_index($bank, $avoidIndex = null) {
  // Trouver toutes les questions actives
  $active = [];
  foreach ($bank as $i => $row) {
    if (!empty($row['enabled'])) {
      $active[] = $i;
    }
  }
  
  // Si aucune question active, retourner null
  if (empty($active)) {
    return null;
  }
  
  // Éviter de reposer la même question
  if ($avoidIndex !== null && count($active) > 1) {
    $active = array_values(array_filter($active, function($x) use ($avoidIndex) {
      return $x !== $avoidIndex;
    }));
  }
  
  // Choisir une question au hasard
  return $active[array_rand($active)];
}

// Fonction pour connecter l'utilisateur et aller à l'accueil
function connect_and_finish_then_redirect_home() {
  // Activer la session utilisateur
  $_SESSION['user_id'] = $_SESSION['pending_user_id'];
  $_SESSION['user_pseudo'] = $_SESSION['pending_user_pseudo'] ?? '';
  $_SESSION['user_email'] = $_SESSION['pending_user_email'] ?? '';
  $_SESSION['logged_in'] = true;
  $_SESSION['login_time'] = time();

  // Nettoyer les variables temporaires
  unset($_SESSION['pending_user_id'], $_SESSION['pending_user_pseudo'], $_SESSION['pending_user_email']);
  unset($_SESSION['captcha_idx'], $_SESSION['captcha_attempts']);

  // Message de bienvenue
  $_SESSION['flash'] = [
    'success' => 'Connexion réussie ! Bienvenue, ' . htmlspecialchars($_SESSION['user_pseudo']) . ' !'
  ];

  // Rediriger vers l'accueil
  header('Location: /PAGE/ACCUEIL.php');
  exit;
}

// ÉTAPE 3 : Charger les questions
// --------------------------------
$bank = load_bank();

// Si aucune question, connecter directement
if (empty($bank)) {
  connect_and_finish_then_redirect_home();
}

// ÉTAPE 4 : Vérifier la réponse (si le formulaire a été envoyé)
// --------------------------------------------------------------
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Récupérer l'index de la question et la réponse
  $idx = isset($_SESSION['captcha_idx']) ? (int)$_SESSION['captcha_idx'] : null;
  $answer = trim($_POST['captcha_answer'] ?? '');

  // Si la question existe
  if ($idx !== null && isset($bank[$idx])) {
    $expected = $bank[$idx]['a'] ?? '';

    // Vérifier si la réponse est correcte
    if (is_answer_correct($answer, $expected)) {
      // Réponse correcte ! Connexion réussie
      connect_and_finish_then_redirect_home();
    } else {
      // Réponse incorrecte
      $_SESSION['captcha_attempts']++;

      // Si 3 échecs, retour à l'accueil
      if ($_SESSION['captcha_attempts'] >= 3) {
        unset($_SESSION['pending_user_id'], $_SESSION['pending_user_pseudo'], $_SESSION['pending_user_email']);
        unset($_SESSION['captcha_idx'], $_SESSION['captcha_attempts']);

        $_SESSION['flash_index'] = [
          'error' => 'Vous avez échoué 3 fois au captcha. Veuillez vous reconnecter.'
        ];
        header('Location: /index.php');
        exit;
      }

      // Afficher l'erreur et choisir une nouvelle question
      $error = "Mauvaise réponse (" . $_SESSION['captcha_attempts'] . "/3) — nouvelle question.";
      $newIdx = pick_random_question_index($bank, $idx);
      $_SESSION['captcha_idx'] = $newIdx;
    }
  } else {
    $_SESSION['captcha_idx'] = pick_random_question_index($bank, null);
  }
}

// ÉTAPE 5 : Choisir une question si besoin
// -----------------------------------------
if (!isset($_SESSION['captcha_idx']) || !isset($bank[$_SESSION['captcha_idx']])) {
  $_SESSION['captcha_idx'] = pick_random_question_index($bank, null);
}

// Récupérer la question actuelle
$currentIdx = $_SESSION['captcha_idx'];
$currentQ = $bank[$currentIdx]['q'] ?? 'Question indisponible';
$attempts = $_SESSION['captcha_attempts'];
$remaining = 3 - $attempts;
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Connexion - Captcha | GameLink</title>
  <style>
    /* Style simple pour la page */
    body {
      font-family: Arial, sans-serif;
      background-color: #f0f0f0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .captcha-container {
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      max-width: 500px;
      width: 100%;
    }
    .logo-container {
      text-align: center;
      margin-bottom: 20px;
    }
    .logo-container img {
      max-width: 200px;
    }
    h1 {
      text-align: center;
      color: #333;
      font-size: 24px;
    }
    .attempts-counter {
      background: #fff3cd;
      padding: 10px;
      border-radius: 5px;
      margin: 15px 0;
      text-align: center;
      color: #856404;
    }
    .error-message {
      background: #f8d7da;
      color: #721c24;
      padding: 10px;
      border-radius: 5px;
      margin: 15px 0;
    }
    .question-box {
      background: #e7f3ff;
      padding: 15px;
      border-radius: 5px;
      margin: 20px 0;
    }
    .question-text {
      font-size: 18px;
      margin-top: 10px;
      color: #333;
    }
    label {
      display: block;
      margin: 15px 0 5px;
      font-weight: bold;
    }
    input[type="text"] {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 16px;
      box-sizing: border-box;
    }
    button {
      width: 100%;
      padding: 12px;
      background: #007bff;
      color: white;
      border: none;
      border-radius: 5px;
      font-size: 16px;
      cursor: pointer;
      margin-top: 15px;
    }
    button:hover {
      background: #0056b3;
    }
  </style>
</head>
<body>
  <div class="captcha-container">
    <div class="logo-container">
      <img src="/ICON/LogoComplet.svg" alt="GameLink Logo">
    </div>

    <h1>🔒 Vérification de sécurité</h1>

    <?php if ($attempts > 0): ?>
      <div class="attempts-counter">⚠️ Tentatives restantes : <?= (int)$remaining ?>/3</div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="question-box">
        <strong>Question :</strong>
        <div class="question-text"><?= htmlspecialchars($currentQ) ?></div>
      </div>

      <label for="captcha_answer">Votre réponse :</label>
      <input type="text" id="captcha_answer" name="captcha_answer" required autofocus>

      <button type="submit">Valider</button>
    </form>
  </div>
</body>
</html>