<?php
// PAGE/captcha.php - Vérification captcha avant connexion finale
session_start();

// Vérifier qu'on a bien un utilisateur en attente
if (!isset($_SESSION['pending_user_id'])) {
    header('Location: AUTH.php');
    exit;
}

const JSON_FILE = __DIR__ . '/../DATA/captcha_bank.json';

// Charge la banque de questions
function load_bank() {
  if (!file_exists(JSON_FILE)) return [];
  $txt = file_get_contents(JSON_FILE);
  $arr = json_decode($txt, true);
  return is_array($arr) ? $arr : [];
}

// Normalisation : enlever accents, espace, casse...
function normalize_text($s) {
  $s = trim((string)$s);
  $s = mb_strtolower($s, 'UTF-8');
  // tente d'enlever accents
  $trans = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
  if ($trans !== false) $s = $trans;
  // ne garder que lettres et chiffres, transformer en espaces
  $s = preg_replace('/[^a-z0-9]+/i', ' ', $s);
  $s = preg_replace('/\s+/', ' ', $s);
  return trim($s);
}

function is_answer_correct($userInput, $expectedRaw) {
  $u = normalize_text($userInput);
  $candidates = array_map('trim', explode('|', $expectedRaw));
  foreach ($candidates as $c) {
    if ($u === normalize_text($c)) return true;
  }
  return false;
}

// Pick random enabled question index, avoid previous if possible
function pick_random_question_index($bank, $avoidIndex = null) {
  $active = [];
  foreach ($bank as $i => $row) {
    if (!empty($row['enabled'])) $active[] = $i;
  }
  if (empty($active)) return null;
  if ($avoidIndex !== null && count($active) > 1) {
    $active = array_values(array_filter($active, fn($x) => $x !== $avoidIndex));
  }
  return $active[array_rand($active)];
}

$bank = load_bank();
if (empty($bank)) {
  echo "Aucune question disponible. Contactez l'administrateur.";
  exit;
}

// Si POST (envoi de la réponse)
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $idx = isset($_SESSION['captcha_idx']) ? (int)$_SESSION['captcha_idx'] : null;
  $answer = trim($_POST['captcha_answer'] ?? '');
  
  if ($idx !== null && isset($bank[$idx])) {
    $expected = $bank[$idx]['a'] ?? '';
    if (is_answer_correct($answer, $expected)) {
      // Captcha validé - Activer la session utilisateur
      $_SESSION['user_id'] = $_SESSION['pending_user_id'];
      $_SESSION['user_pseudo'] = $_SESSION['pending_user_pseudo'];
      
      // Nettoyer les variables temporaires
      unset($_SESSION['pending_user_id']);
      unset($_SESSION['pending_user_pseudo']);
      unset($_SESSION['captcha_idx']);
      
      // Redirection vers la page d'accueil connectée
      header('Location: ACCUEIL.php');
      exit;
    } else {
      $error = "Mauvaise réponse – nouvelle question.";
      // nouvelle question (différente si possible)
      $newIdx = pick_random_question_index($bank, $idx);
      $_SESSION['captcha_idx'] = $newIdx;
    }
  } else {
    // pas d'index -> choisir un
    $_SESSION['captcha_idx'] = pick_random_question_index($bank, null);
  }
}

// Si pas d'index en session, en définir un
if (!isset($_SESSION['captcha_idx']) || !isset($bank[$_SESSION['captcha_idx']])) {
  $_SESSION['captcha_idx'] = pick_random_question_index($bank, null);
}
$currentIdx = $_SESSION['captcha_idx'];
$currentQ = $bank[$currentIdx]['q'] ?? 'Question indisponible';
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Connexion - Captcha | GameLink</title>
  <link rel="stylesheet" href="../CSS/HEADER.css">
  <link rel="stylesheet" href="../CSS/AUTH.css">
  <style>
    body {
      font-family: 'Roboto', sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .captcha-container {
      background: white;
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.2);
      max-width: 500px;
      width: 100%;
    }
    .captcha-container h1 {
      color: #667eea;
      text-align: center;
      margin-bottom: 30px;
    }
    .error-message {
      background: #ff6b6b;
      color: white;
      padding: 15px;
      border-radius: 5px;
      margin-bottom: 20px;
      text-align: center;
    }
    .question-box {
      background: #f8f9fa;
      padding: 20px;
      border-radius: 5px;
      margin-bottom: 20px;
    }
    .question-box strong {
      color: #667eea;
      display: block;
      margin-bottom: 10px;
    }
    .question-text {
      font-size: 1.1em;
      color: #333;
    }
    label {
      display: block;
      margin-bottom: 10px;
      color: #333;
      font-weight: bold;
    }
    input[type="text"] {
      width: 100%;
      padding: 12px;
      border: 2px solid #ddd;
      border-radius: 5px;
      font-size: 1em;
      box-sizing: border-box;
    }
    input[type="text"]:focus {
      outline: none;
      border-color: #667eea;
    }
    button[type="submit"] {
      width: 100%;
      padding: 15px;
      background: #667eea;
      color: white;
      border: none;
      border-radius: 5px;
      font-size: 1.1em;
      font-weight: bold;
      cursor: pointer;
      margin-top: 20px;
      transition: background 0.3s;
    }
    button[type="submit"]:hover {
      background: #5568d3;
    }
    .logo-container {
      text-align: center;
      margin-bottom: 20px;
    }
    .logo-container img {
      max-width: 200px;
    }
  </style>
</head>
<body>
  <div class="captcha-container">
    <div class="logo-container">
      <img src="../ICON/LogoComplet.svg" alt="GameLink Logo">
    </div>
    
    <h1>🔒 Vérification de sécurité</h1>

    <?php if ($error): ?>
      <div class="error-message"><?=htmlspecialchars($error)?></div>
    <?php endif; ?>

    <form method="post">
      <div class="question-box">
        <strong>Question :</strong>
        <div class="question-text"><?=htmlspecialchars($currentQ)?></div>
      </div>
      
      <label for="captcha_answer">Votre réponse :</label>
      <input type="text" id="captcha_answer" name="captcha_answer" required autofocus>
      
      <button type="submit">Valider</button>
    </form>
  </div>
</body>
</html>
