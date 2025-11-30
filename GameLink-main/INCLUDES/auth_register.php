<?php

session_start();

require_once __DIR__ . '/../DATA/DBConfig.php';

if (isset($_POST['csrf']) && $_POST['csrf'] !== ($_SESSION['csrf'] ?? '')) {
  file_put_contents('/tmp/GL_register.log', date('c') . " CSRF_FAIL\n", FILE_APPEND);
  http_response_code(400);
  exit('Erreur CSRF');
}

$pseudo = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm'] ?? $_POST['password_confirm'] ?? '';

$errors = [];
$old = ['name' => $pseudo, 'email' => $email];

if ($pseudo === '' || strlen($pseudo) < 3) {
  $errors['suName'] = '3 caractères minimum';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $errors['suEmail'] = 'Email invalide';
}

if (strlen($password) < 6) {
  $errors['suPass'] = '6 caractères minimum';
}

if ($password !== $confirm) {
  $errors['suConfirm'] = 'Les mots de passe ne correspondent pas';
}

if ($errors) {
  $_SESSION['flash'] = ['errors' => $errors, 'old' => $old];
  file_put_contents('/tmp/GL_register.log', date('c') . " VALIDATION_ERR=" . json_encode($errors) . "\n", FILE_APPEND);
  header('Location: /PAGE/AUTH.php?tab=signup', true, 303);
  exit;
}

try {
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $chk = $pdo->prepare('SELECT id_joueur FROM joueur WHERE email = ?');
  $chk->execute([$email]);
  
  if ($chk->fetch()) {
    $_SESSION['flash'] = ['errors' => ['suEmail' => 'Email déjà utilisé'], 'old' => $old];
    file_put_contents('/tmp/GL_register.log', date('c') . " DUP_EMAIL\n", FILE_APPEND);
    header('Location: /PAGE/AUTH.php?tab=signup', true, 303);
    exit;
  }

  $hash = password_hash($password, PASSWORD_BCRYPT);
  
  $ins = $pdo->prepare('INSERT INTO joueur (pseudo, email, password_hash) VALUES (?, ?, ?)');
  $ins->execute([$pseudo, $email, $hash]);
  
  $newId = (int)$pdo->lastInsertId();

  file_put_contents('/tmp/GL_register.log', date('c') . " INSERT_OK id=$newId\n", FILE_APPEND);

  $_SESSION['pending_user_id'] = $newId;
  $_SESSION['pending_user_pseudo'] = $pseudo;

  $script = $_SERVER['SCRIPT_NAME'];
  $incDir = rtrim(dirname($script), '/');
  $siteDir = rtrim(dirname($incDir), '/');
  $captcha = $siteDir . '/PAGE/captcha.php';

  header('Location: ' . $captcha, true, 303);
  exit;

} catch (Throwable $e) {
  error_log('REGISTER ERROR: ' . $e->getMessage());
  file_put_contents('/tmp/GL_register.log', date('c') . " EXC=" . $e->getMessage() . "\n", FILE_APPEND);

  $_SESSION['flash'] = ['errors' => ['general' => "Erreur d'inscription"], 'old' => $old];
  header('Location: /PAGE/AUTH.php?tab=signup', true, 303);
  exit;
}