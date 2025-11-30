<?php

session_start();

require_once __DIR__ . '/../DATA/DBConfig.php';

if (isset($_POST['csrf']) && $_POST['csrf'] !== ($_SESSION['csrf'] ?? '')) {
  file_put_contents('/tmp/GL_login.log', date('c') . " CSRF_FAIL\n", FILE_APPEND);
  http_response_code(400);
  exit('Erreur CSRF');
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$errors = [];

$old = ['email' => $email];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $errors['loginEmail'] = 'Email invalide';
}

if (strlen($password) < 6) {
  $errors['loginPassword'] = '6 caractères minimum';
}

if ($errors) {
  $_SESSION['flash'] = ['errors' => $errors, 'old' => $old];
  file_put_contents('/tmp/GL_login.log', date('c') . " VALIDATION_ERR=" . json_encode($errors) . "\n", FILE_APPEND);
  header('Location: /PAGE/AUTH.php?tab=login', true, 303);
  exit;
}

try {
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $stmt = $pdo->prepare('SELECT id_joueur, pseudo, password_hash FROM joueur WHERE email = ? LIMIT 1');
  
  $stmt->execute([$email]);
  
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$user || !password_verify($password, $user['password_hash'])) {
    $_SESSION['flash'] = ['errors' => ['general' => 'Identifiants invalides'], 'old' => $old];
    file_put_contents('/tmp/GL_login.log', date('c') . " BAD_CRED\n", FILE_APPEND);
    header('Location: /PAGE/AUTH.php?tab=login', true, 303);
    exit;
  }

  $_SESSION['pending_user_id'] = (int)$user['id_joueur'];
  $_SESSION['pending_user_pseudo'] = $user['pseudo'];

  file_put_contents('/tmp/GL_login.log', date('c') . " OK uid=" . $_SESSION['pending_user_id'] . "\n", FILE_APPEND);

  $script = $_SERVER['SCRIPT_NAME'];
  $incDir = rtrim(dirname($script), '/');
  $siteDir = rtrim(dirname($incDir), '/');
  $captcha = $siteDir . '/PAGE/captcha.php';

  header('Location: ' . $captcha, true, 303);
  exit;

} catch (Throwable $e) {
  error_log('LOGIN ERROR: ' . $e->getMessage());
  file_put_contents('/tmp/GL_login.log', date('c') . " EXC=" . $e->getMessage() . "\n", FILE_APPEND);

  $_SESSION['flash'] = ['errors' => ['general' => 'Erreur serveur'], 'old' => $old];
  header('Location: /PAGE/AUTH.php?tab=login', true, 303);
  exit;
}