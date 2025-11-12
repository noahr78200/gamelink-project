<?php
// INCLUDES/auth_register.php
session_start();

/* 1) Connexion BDD */
require_once __DIR__ . '/../DATA/DBConfig.php';

/* 2) CSRF */
if (isset($_POST['csrf']) && $_POST['csrf'] !== ($_SESSION['csrf'] ?? '')) {
  file_put_contents('/tmp/GL_register.log', date('c') . " CSRF_FAIL\n", FILE_APPEND);
  http_response_code(400);
  exit('Erreur CSRF');
}

/* 3) Données */
$pseudo   = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';
$cgu      = isset($_POST['cgu']);

$errors   = [];
$old      = ['name' => $pseudo, 'email' => $email];

// Validation du pseudo
if (empty($pseudo)) {
    $errors['suName'] = 'Le pseudo est requis';
} elseif (strlen($pseudo) < 3) {
    $errors['suName'] = 'Le pseudo doit contenir au moins 3 caractères';
} elseif (strlen($pseudo) > 50) {
    $errors['suName'] = 'Le pseudo ne peut pas dépasser 50 caractères';
} elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $pseudo)) {
    $errors['suName'] = 'Le pseudo ne peut contenir que des lettres, chiffres, tirets et underscores';
}

// Validation de l'email
if (empty($email)) {
    $errors['suEmail'] = 'L\'email est requis';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['suEmail'] = 'Format d\'email invalide';
} elseif (strlen($email) > 255) {
    $errors['suEmail'] = 'L\'email est trop long';
}

// Validation du mot de passe
if (empty($password)) {
    $errors['suPass'] = 'Le mot de passe est requis';
} elseif (strlen($password) < 6) {
    $errors['suPass'] = 'Le mot de passe doit contenir au moins 6 caractères';
} elseif (strlen($password) > 255) {
    $errors['suPass'] = 'Le mot de passe est trop long';
}

// Vérification de la confirmation
if ($password !== $password_confirm) {
    $errors['suConfirm'] = 'Les mots de passe ne correspondent pas';
}

// Vérification CGU
if (!$cgu) {
    $errors['suCgu'] = 'Vous devez accepter les CGU';
}

// Si erreurs, retour au formulaire
if ($errors) {
  $_SESSION['flash'] = ['errors' => $errors, 'old' => $old];
  file_put_contents('/tmp/GL_register.log', date('c') . " VALIDATION_ERR=" . json_encode($errors) . "\n", FILE_APPEND);
  header('Location: ../PAGE/AUTH.php?tab=signup', true, 303);
  exit;
}

/* 4) Vérification et création du compte */
try {
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Vérifier si l'email existe déjà
  $stmt = $pdo->prepare("SELECT id_joueur FROM joueur WHERE email = ?");
  $stmt->execute([$email]);
  if ($stmt->fetch()) {
    $_SESSION['flash'] = [
        'errors' => ['suEmail' => 'Cet email est déjà utilisé'],
        'old' => $old
    ];
    file_put_contents('/tmp/GL_register.log', date('c') . " EMAIL_EXISTS email=$email\n", FILE_APPEND);
    header('Location: ../PAGE/AUTH.php?tab=signup', true, 303);
    exit;
  }

  // Vérifier si le pseudo existe déjà
  $stmt = $pdo->prepare("SELECT id_joueur FROM joueur WHERE pseudo = ?");
  $stmt->execute([$pseudo]);
  if ($stmt->fetch()) {
    $_SESSION['flash'] = [
        'errors' => ['suName' => 'Ce pseudo est déjà utilisé'],
        'old' => $old
    ];
    file_put_contents('/tmp/GL_register.log', date('c') . " PSEUDO_EXISTS pseudo=$pseudo\n", FILE_APPEND);
    header('Location: ../PAGE/AUTH.php?tab=signup', true, 303);
    exit;
  }

  // Hashage du mot de passe
  $password_hash = password_hash($password, PASSWORD_DEFAULT);

  // Insertion dans la base de données
  $stmt = $pdo->prepare("
    INSERT INTO joueur (pseudo, email, password_hash, date_inscription) 
    VALUES (?, ?, ?, NOW())
  ");
  $stmt->execute([$pseudo, $email, $password_hash]);

  $user_id = $pdo->lastInsertId();

  // ✅ Stocker l'utilisateur en attente de validation captcha
  $_SESSION['pending_user_id']     = (int)$user_id;
  $_SESSION['pending_user_pseudo'] = $pseudo;
  $_SESSION['pending_user_email']  = $email;
  
  // ✅ IMPORTANT : Initialiser le compteur de tentatives captcha
  $_SESSION['captcha_attempts'] = 0;

  file_put_contents('/tmp/GL_register.log', date('c') . " OK uid=$user_id pseudo=$pseudo email=$email\n", FILE_APPEND);

  // ✅ Redirection vers captcha (chemin relatif depuis INCLUDES/)
  header('Location: ../PAGE/captcha.php', true, 303);
  exit;

} catch (Throwable $e) {
  error_log('REGISTER ERROR: ' . $e->getMessage());
  file_put_contents('/tmp/GL_register.log', date('c') . " EXC=" . $e->getMessage() . "\n", FILE_APPEND);
  
  $_SESSION['flash'] = [
      'errors' => ['general' => 'Une erreur est survenue. Veuillez réessayer.'],
      'old' => $old
  ];
  header('Location: ../PAGE/AUTH.php?tab=signup', true, 303);
  exit;
}