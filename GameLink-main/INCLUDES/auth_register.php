<?php
// ==========================================
// FICHIER : auth_register.php
// BUT : Créer un nouveau compte utilisateur
// ==========================================

// Démarre une session
session_start();

// ÉTAPE 1 : Se connecter à la base de données
// --------------------------------------------
require_once __DIR__ . '/../DATA/DBConfig.php';

// ÉTAPE 2 : Vérifier le token CSRF (sécurité)
// --------------------------------------------
if (isset($_POST['csrf']) && $_POST['csrf'] !== ($_SESSION['csrf'] ?? '')) {
  file_put_contents('/tmp/GL_register.log', date('c') . " CSRF_FAIL\n", FILE_APPEND);
  http_response_code(400);
  exit('Erreur CSRF');
}

// ÉTAPE 3 : Récupérer les données du formulaire
// ----------------------------------------------
$pseudo = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm'] ?? $_POST['password_confirm'] ?? '';

// Tableaux pour les erreurs et anciennes valeurs
$errors = [];
$old = ['name' => $pseudo, 'email' => $email];

// ÉTAPE 4 : Vérifier que tout est correct
// ----------------------------------------
// Le pseudo doit faire au moins 3 caractères
if ($pseudo === '' || strlen($pseudo) < 3) {
  $errors['suName'] = '3 caractères minimum';
}

// L'email doit être valide
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $errors['suEmail'] = 'Email invalide';
}

// Le mot de passe doit faire au moins 6 caractères
if (strlen($password) < 6) {
  $errors['suPass'] = '6 caractères minimum';
}

// Les deux mots de passe doivent être identiques
if ($password !== $confirm) {
  $errors['suConfirm'] = 'Les mots de passe ne correspondent pas';
}

// S'il y a des erreurs, on retourne au formulaire
if ($errors) {
  $_SESSION['flash'] = ['errors' => $errors, 'old' => $old];
  file_put_contents('/tmp/GL_register.log', date('c') . " VALIDATION_ERR=" . json_encode($errors) . "\n", FILE_APPEND);
  header('Location: /PAGE/AUTH.php?tab=signup', true, 303);
  exit;
}

// ÉTAPE 5 : Ajouter l'utilisateur dans la base de données
// --------------------------------------------------------
try {
  // Activer les erreurs PDO
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Vérifier que l'email n'existe pas déjà
  $chk = $pdo->prepare('SELECT id_joueur FROM joueur WHERE email = ?');
  $chk->execute([$email]);
  
  if ($chk->fetch()) {
    // L'email existe déjà
    $_SESSION['flash'] = ['errors' => ['suEmail' => 'Email déjà utilisé'], 'old' => $old];
    file_put_contents('/tmp/GL_register.log', date('c') . " DUP_EMAIL\n", FILE_APPEND);
    header('Location: /PAGE/AUTH.php?tab=signup', true, 303);
    exit;
  }

  // Crypter le mot de passe (sécurité)
  $hash = password_hash($password, PASSWORD_BCRYPT);
  
  // Insérer le nouvel utilisateur
  $ins = $pdo->prepare('INSERT INTO joueur (pseudo, email, password_hash) VALUES (?, ?, ?)');
  $ins->execute([$pseudo, $email, $hash]);
  
  // Récupérer l'ID du nouvel utilisateur
  $newId = (int)$pdo->lastInsertId();

  // Écrire dans le journal
  file_put_contents('/tmp/GL_register.log', date('c') . " INSERT_OK id=$newId\n", FILE_APPEND);

  // Sauvegarder temporairement l'utilisateur
  $_SESSION['pending_user_id'] = $newId;
  $_SESSION['pending_user_pseudo'] = $pseudo;

  // ÉTAPE 6 : Calculer le chemin vers le captcha
  // ---------------------------------------------
  $script = $_SERVER['SCRIPT_NAME'];
  $incDir = rtrim(dirname($script), '/');
  $siteDir = rtrim(dirname($incDir), '/');
  $captcha = $siteDir . '/PAGE/captcha.php';

  // Rediriger vers le captcha
  header('Location: ' . $captcha, true, 303);
  exit;

} catch (Throwable $e) {
  // Si erreur, on note et on retourne au formulaire
  error_log('REGISTER ERROR: ' . $e->getMessage());
  file_put_contents('/tmp/GL_register.log', date('c') . " EXC=" . $e->getMessage() . "\n", FILE_APPEND);

  $_SESSION['flash'] = ['errors' => ['general' => "Erreur d'inscription"], 'old' => $old];
  header('Location: /PAGE/AUTH.php?tab=signup', true, 303);
  exit;
}