<?php
// ==========================================
// FICHIER : auth_login.php
// BUT : Connecter un utilisateur au site
// ==========================================

// Démarre une session (pour garder l'utilisateur connecté)
session_start();

// ÉTAPE 1 : Se connecter à la base de données
// --------------------------------------------
require_once __DIR__ . '/../DATA/DBConfig.php';

// ÉTAPE 2 : Vérifier le token CSRF (sécurité anti-piratage)
// ----------------------------------------------------------
if (isset($_POST['csrf']) && $_POST['csrf'] !== ($_SESSION['csrf'] ?? '')) {
  // Si le token ne correspond pas, on arrête tout
  file_put_contents('/tmp/GL_login.log', date('c') . " CSRF_FAIL\n", FILE_APPEND);
  http_response_code(400);
  exit('Erreur CSRF');
}

// ÉTAPE 3 : Récupérer les données du formulaire
// ----------------------------------------------
// trim() enlève les espaces au début et à la fin
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Créer un tableau pour stocker les erreurs
$errors = [];

// Créer un tableau pour garder les anciennes valeurs (si erreur)
$old = ['email' => $email];

// ÉTAPE 4 : Vérifier si les données sont correctes
// -------------------------------------------------
// Vérifier si l'email est valide
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $errors['loginEmail'] = 'Email invalide';
}

// Vérifier si le mot de passe fait au moins 6 caractères
if (strlen($password) < 6) {
  $errors['loginPassword'] = '6 caractères minimum';
}

// S'il y a des erreurs, on arrête et on renvoie vers la page de connexion
if ($errors) {
  $_SESSION['flash'] = ['errors' => $errors, 'old' => $old];
  file_put_contents('/tmp/GL_login.log', date('c') . " VALIDATION_ERR=" . json_encode($errors) . "\n", FILE_APPEND);
  header('Location: /PAGE/AUTH.php?tab=login', true, 303);
  exit;
}

// ÉTAPE 5 : Vérifier dans la base de données
// -------------------------------------------
try {
  // Activer les erreurs PDO
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Préparer la requête SQL pour chercher l'utilisateur
  $stmt = $pdo->prepare('SELECT id_joueur, pseudo, password_hash FROM joueur WHERE email = ? LIMIT 1');
  
  // Exécuter la requête avec l'email fourni
  $stmt->execute([$email]);
  
  // Récupérer le résultat
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  // Vérifier si l'utilisateur existe ET si le mot de passe est correct
  if (!$user || !password_verify($password, $user['password_hash'])) {
    // Mauvais identifiants
    $_SESSION['flash'] = ['errors' => ['general' => 'Identifiants invalides'], 'old' => $old];
    file_put_contents('/tmp/GL_login.log', date('c') . " BAD_CRED\n", FILE_APPEND);
    header('Location: /PAGE/AUTH.php?tab=login', true, 303);
    exit;
  }

  // ÉTAPE 6 : Connexion réussie ! Sauvegarder les infos temporairement
  // -------------------------------------------------------------------
  $_SESSION['pending_user_id'] = (int)$user['id_joueur'];
  $_SESSION['pending_user_pseudo'] = $user['pseudo'];

  // Écrire dans le journal (log)
  file_put_contents('/tmp/GL_login.log', date('c') . " OK uid=" . $_SESSION['pending_user_id'] . "\n", FILE_APPEND);

  // ÉTAPE 7 : Calculer le chemin vers la page captcha
  // --------------------------------------------------
  $script = $_SERVER['SCRIPT_NAME'];
  $incDir = rtrim(dirname($script), '/');
  $siteDir = rtrim(dirname($incDir), '/');
  $captcha = $siteDir . '/PAGE/captcha.php';

  // Rediriger vers la page du captcha
  header('Location: ' . $captcha, true, 303);
  exit;

} catch (Throwable $e) {
  // Si une erreur se produit, on la note et on renvoie vers la page de connexion
  error_log('LOGIN ERROR: ' . $e->getMessage());
  file_put_contents('/tmp/GL_login.log', date('c') . " EXC=" . $e->getMessage() . "\n", FILE_APPEND);

  $_SESSION['flash'] = ['errors' => ['general' => 'Erreur serveur'], 'old' => $old];
  header('Location: /PAGE/AUTH.php?tab=login', true, 303);
  exit;
}