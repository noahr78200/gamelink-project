<?php
// PAGE/captcha.php
session_start();

// ==== DEBUG MINIMAL (à retirer en prod) ====
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// 1) Vérifier l'utilisateur en attente
if (!isset($_SESSION['pending_user_id'])) {
    header('Location: /PAGE/AUTH.php');
    exit;
}

// 2) Init compteur
if (!isset($_SESSION['captcha_attempts'])) {
    $_SESSION['captcha_attempts'] = 0;
}

define('CAPTCHA_JSON', __DIR__ . '/../DATA/captcha_bank.json');

// --- Helpers sûrs ---
function load_bank(): array {
    if (!file_exists(CAPTCHA_JSON)) return [];
    $txt = @file_get_contents(CAPTCHA_JSON);
    if ($txt === false) return [];
    $arr = @json_decode($txt, true);
    return is_array($arr) ? $arr : [];
}

function normalize_text($s): string {
    $s = trim((string)$s);

    // mb_strtolower peut ne pas être dispo → fallback
    if (function_exists('mb_strtolower')) {
        $s = mb_strtolower($s, 'UTF-8');
    } else {
        $s = strtolower($s);
    }

    // iconv peut ne pas être dispo → fallback
    if (function_exists('iconv')) {
        $trans = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        if ($trans !== false) $s = $trans;
    }

    $s = preg_replace('/[^a-z0-9]+/i', ' ', $s);
    $s = preg_replace('/\s+/', ' ', $s);
    return trim($s);
}

function is_answer_correct($userInput, $expectedRaw): bool {
    $u = normalize_text($userInput);
    $candidates = array_map('trim', explode('|', (string)$expectedRaw));
    foreach ($candidates as $c) {
        if ($u === normalize_text($c)) return true;
    }
    return false;
}

function pick_random_question_index(array $bank, $avoidIndex = null) {
    $active = [];
    foreach ($bank as $i => $row) {
        if (!empty($row['enabled'])) $active[] = $i;
    }
    if (empty($active)) return null;
    if ($avoidIndex !== null && count($active) > 1) {
        $active = array_values(array_filter($active, function($x) use ($avoidIndex){ return $x !== $avoidIndex; }));
    }
    return $active[array_rand($active)];
}

function connect_and_finish_then_redirect_home() {
    // Active la session utilisateur
    $_SESSION['user_id']     = $_SESSION['pending_user_id'];
    $_SESSION['user_pseudo'] = $_SESSION['pending_user_pseudo'] ?? '';
    $_SESSION['user_email']  = $_SESSION['pending_user_email']  ?? '';
    $_SESSION['logged_in']   = true;
    $_SESSION['login_time']  = time();

    // Nettoyage
    unset($_SESSION['pending_user_id'], $_SESSION['pending_user_pseudo'], $_SESSION['pending_user_email']);
    unset($_SESSION['captcha_idx'], $_SESSION['captcha_attempts']);

    $_SESSION['flash'] = [
        'success' => 'Connexion réussie ! Bienvenue, ' . htmlspecialchars($_SESSION['user_pseudo']) . ' !'
    ];

    // IMPORTANT : chemin absolu
    header('Location: /PAGE/ACCUEIL.php');
    exit;
}

// 3) Charger la banque
$bank = load_bank();

// Si aucune question active → connecter direct (pas d'echo AVANT header)
if (empty($bank)) {
    connect_and_finish_then_redirect_home();
}

// 4) POST : vérification de la réponse
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idx    = isset($_SESSION['captcha_idx']) ? (int)$_SESSION['captcha_idx'] : null;
    $answer = trim($_POST['captcha_answer'] ?? '');

    if ($idx !== null && isset($bank[$idx])) {
        $expected = $bank[$idx]['a'] ?? '';

        if (is_answer_correct($answer, $expected)) {
            connect_and_finish_then_redirect_home();
        } else {
            $_SESSION['captcha_attempts']++;

            if ($_SESSION['captcha_attempts'] >= 3) {
                // Reset & retour index
                unset($_SESSION['pending_user_id'], $_SESSION['pending_user_pseudo'], $_SESSION['pending_user_email']);
                unset($_SESSION['captcha_idx'], $_SESSION['captcha_attempts']);

                $_SESSION['flash_index'] = [
                    'error' => 'Vous avez échoué 3 fois au captcha. Veuillez vous reconnecter.'
                ];
                header('Location: /index.php');
                exit;
            }

            $error = "Mauvaise réponse (" . $_SESSION['captcha_attempts'] . "/3) – nouvelle question.";
            $newIdx = pick_random_question_index($bank, $idx);
            $_SESSION['captcha_idx'] = $newIdx;
        }
    } else {
        $_SESSION['captcha_idx'] = pick_random_question_index($bank, null);
    }
}

// 5) Choisir une question si besoin
if (!isset($_SESSION['captcha_idx']) || !isset($bank[$_SESSION['captcha_idx']])) {
    $_SESSION['captcha_idx'] = pick_random_question_index($bank, null);
}

$currentIdx = $_SESSION['captcha_idx'];
$currentQ   = $bank[$currentIdx]['q'] ?? 'Question indisponible';
$attempts   = $_SESSION['captcha_attempts'];
$remaining  = 3 - $attempts;
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Connexion - Captcha | GameLink</title>
  <link rel="stylesheet" href="/CSS/HEADER.css">
  <link rel="stylesheet" href="/CSS/AUTH.css">
  <style>
    /* ... (garde ton CSS inline si tu veux) ... */
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
