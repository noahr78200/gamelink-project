<?php
// INCLUDES/auth_login.php
session_start();

/* 1) Connexion BDD */
require_once __DIR__ . '/../DATA/DBConfig.php';

/* 2) CSRF */
if (isset($_POST['csrf']) && $_POST['csrf'] !== ($_SESSION['csrf'] ?? '')) {
  file_put_contents('/tmp/GL_login.log', date('c') . " CSRF_FAIL\n", FILE_APPEND);
  http_response_code(400);
  exit('Erreur CSRF');
}

/* 3) Données */
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$errors   = [];
$old      = ['email' => $email];

// Validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['loginEmail'] = 'Email invalide';
}
if (strlen($password) < 6) {
    $errors['loginPassword'] = '6 caractères minimum';
}

if ($errors) {
  $_SESSION['flash'] = ['errors' => $errors, 'old' => $old];
  file_put_contents('/tmp/GL_login.log', date('c') . " VALIDATION_ERR=" . json_encode($errors) . "\n", FILE_APPEND);
  header('Location: ../PAGE/AUTH.php?tab=login', true, 303);
  exit;
}

/* 4) Vérification utilisateur */
try {
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $stmt = $pdo->prepare('SELECT id_joueur, pseudo, email, password_hash FROM joueur WHERE email = ? LIMIT 1');
  $stmt->execute([$email]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  // Vérification mot de passe
  if (!$user || !password_verify($password, $user['password_hash'])) {
    $_SESSION['flash'] = [
        'errors' => ['loginEmail' => 'Email ou mot de passe incorrect'],
        'old' => $old
    ];
    file_put_contents('/tmp/GL_login.log', date('c') . " BAD_CRED email=$email\n", FILE_APPEND);
    header('Location: ../PAGE/AUTH.php?tab=login', true, 303);
    exit;
  }

  // Mise à jour du hash si nécessaire
  if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
    $new_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE joueur SET password_hash = ? WHERE id_joueur = ?");
    $stmt->execute([$new_hash, $user['id_joueur']]);
  }

  // Régénération de l'ID de session pour la sécurité
  session_regenerate_id(true);

  // ✅ Stocker l'utilisateur en attente de validation captcha
  $_SESSION['pending_user_id']     = (int)$user['id_joueur'];
  $_SESSION['pending_user_pseudo'] = $user['pseudo'];
  $_SESSION['pending_user_email']  = $user['email'];
  
  // ✅ IMPORTANT : Initialiser le compteur de tentatives captcha
  $_SESSION['captcha_attempts'] = 0;

  file_put_contents('/tmp/GL_login.log', date('c') . " OK uid=" . $_SESSION['pending_user_id'] . " pseudo=" . $user['pseudo'] . "\n", FILE_APPEND);

  // ✅ Redirection vers captcha (chemin relatif depuis INCLUDES/)
  header('Location: ../PAGE/captcha.php', true, 303);
  exit;

} catch (Throwable $e) {
  error_log('LOGIN ERROR: ' . $e->getMessage());
  file_put_contents('/tmp/GL_login.log', date('c') . " EXC=" . $e->getMessage() . "\n", FILE_APPEND);

  $_SESSION['flash'] = [
      'errors' => ['general' => 'Erreur serveur. Veuillez réessayer.'],
      'old' => $old
  ];
  header('Location: ../PAGE/AUTH.php?tab=login', true, 303);
  exit;
}