<?php
// INCLUDES/auth_register.php
session_start();
require_once __DIR__ . '/../DATA/DBConfig.php';

// CSRF
if (empty($_POST['csrf']) || $_POST['csrf'] !== ($_SESSION['csrf'] ?? '')) {
  die('Erreur CSRF');
}

// Données
$pseudo = trim($_POST['name'] ?? '');
$email  = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm'] ?? '';

$errors = [];
$old = ['name' => $pseudo, 'email' => $email];

// Validation simple
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
  header('Location: ../PAGE/AUTH.php?tab=signup');
  exit;
}

try {
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Unicité email
  $chk = $pdo->prepare('SELECT id FROM joueur WHERE email = ?');
  $chk->execute([$email]);
  if ($chk->fetch()) {
    $_SESSION['flash'] = ['errors' => ['suEmail' => 'Email déjà utilisé'], 'old' => $old];
    header('Location: ../PAGE/AUTH.php?tab=signup');
    exit;
  }

  // Insertion
  $hash = password_hash($password, PASSWORD_BCRYPT);
  $ins = $pdo->prepare('INSERT INTO joueur (pseudo, email, password_hash) VALUES (?, ?, ?)');
  $ins->execute([$pseudo, $email, $hash]);

  $newId = (int)$pdo->lastInsertId();

  // Prépare CAPTCHA (pas loggué tant que pas réussi)
  $_SESSION['pending_user_id'] = $newId;
  $_SESSION['pending_user_pseudo'] = $pseudo;

  header('Location: ../PAGE/captcha.php',true,303);
  exit;

} catch (PDOException $e) {
  error_log('REGISTER ERROR: '.$e->getMessage());
  $_SESSION['flash'] = ['errors' => ['general' => "Erreur d'inscription"], 'old' => $old];
  header('Location: ../PAGE/AUTH.php?tab=signup');
  exit;
}
