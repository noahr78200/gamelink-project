<?php
// DEBUG TEMP — à retirer après
error_reporting(E_ALL); ini_set('display_errors', 1);
file_put_contents('/tmp/GL_trace.log', date('c')." HIT ".basename(__FILE__)." POST=".json_encode($_POST)."\n", FILE_APPEND);

// INCLUDES/auth_login.php
session_start();
require_once __DIR__ . '/../DATA/DBConfig.php';

// 1) Sécurité CSRF
if (empty($_POST['csrf']) || $_POST['csrf'] !== ($_SESSION['csrf'] ?? '')) {
  die('Erreur CSRF');
}

// 2) Récupère et nettoie les données
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$errors = [];
$old = ['email' => $email];

// 3) Validation “comme à 10 ans”
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $errors['loginEmail'] = 'Email invalide';
}
if (strlen($password) < 6) {
  $errors['loginPassword'] = '6 caractères minimum';
}
if ($errors) {
  $_SESSION['flash'] = ['errors' => $errors, 'old' => $old];
  header('Location: ../PAGE/AUTH.php?tab=login');
  exit;
}

// 4) Vérifie l’utilisateur en BDD
try {
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $stmt = $pdo->prepare('SELECT id, password_hash, pseudo FROM joueur WHERE email = ?');
  $stmt->execute([$email]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$user || !password_verify($password, $user['password_hash'])) {
    $_SESSION['flash'] = ['errors' => ['general' => 'Identifiants invalides'], 'old' => $old];
    header('Location: ../PAGE/AUTH.php?tab=login');
    exit;
  }

  // 5) On ne connecte pas encore : on prépare le CAPTCHA
  $_SESSION['pending_user_id'] = (int)$user['id'];
  $_SESSION['pending_user_pseudo'] = $user['pseudo'];

  header('Location: ../PAGE/captcha.php');
  exit;

} catch (PDOException $e) {
  error_log('LOGIN ERROR: '.$e->getMessage());
  $_SESSION['flash'] = ['errors' => ['general' => 'Erreur serveur'], 'old' => $old];
  header('Location: ../PAGE/AUTH.php?tab=login');
  exit;
}
header('Location: ../PAGE/captcha.php');
exit;