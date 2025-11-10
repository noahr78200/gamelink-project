<?php
// auth_register.php - Traitement de l'inscription
session_start();
require_once __DIR__ . '/../DATA/DBConfig.php';

// Vérification CSRF
if (empty($_POST['csrf']) || $_POST['csrf'] !== ($_SESSION['csrf'] ?? '')) {
    die('Erreur CSRF - Veuillez réessayer');
}

// Fonction de validation et nettoyage
function validate_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Récupération et validation des données
$pseudo = validate_input($_POST['name'] ?? '');
$email = validate_input($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';
$cgu = isset($_POST['cgu']);

$errors = [];
$old = ['name' => $pseudo, 'email' => $email];

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

// Vérification de la confirmation du mot de passe
if ($password !== $password_confirm) {
    $errors['suConfirm'] = 'Les mots de passe ne correspondent pas';
}

// Vérification CGU
if (!$cgu) {
    $errors['suCgu'] = 'Vous devez accepter les conditions générales d\'utilisation';
}

// Si erreurs, retour au formulaire
if (!empty($errors)) {
    $_SESSION['flash'] = [
        'errors' => $errors,
        'old' => $old
    ];
    header('Location: AUTH.php?tab=signup');
    exit;
}

try {
    // Vérification si l'email existe déjà
    $stmt = $pdo->prepare("SELECT id_joueur FROM joueur WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['flash'] = [
            'errors' => ['suEmail' => 'Cet email est déjà utilisé'],
            'old' => $old
        ];
        header('Location: AUTH.php?tab=signup');
        exit;
    }

    // Vérification si le pseudo existe déjà
    $stmt = $pdo->prepare("SELECT id_joueur FROM joueur WHERE pseudo = ?");
    $stmt->execute([$pseudo]);
    if ($stmt->fetch()) {
        $_SESSION['flash'] = [
            'errors' => ['suName' => 'Ce pseudo est déjà utilisé'],
            'old' => $old
        ];
        header('Location: AUTH.php?tab=signup');
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

    // Création de la session utilisateur
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_pseudo'] = $pseudo;
    $_SESSION['user_email'] = $email;
    $_SESSION['logged_in'] = true;

    // Message de succès
    $_SESSION['flash'] = [
        'success' => 'Compte créé avec succès ! Bienvenue sur GameLink, ' . htmlspecialchars($pseudo) . ' !'
    ];

    // Redirection vers le captcha
    header('Location: /PAGE/captcha.php');
    exit;

} catch (PDOException $e) {
    // Log de l'erreur (en production, utiliser un système de log approprié)
    error_log("Erreur inscription : " . $e->getMessage());
    
    $_SESSION['flash'] = [
        'errors' => ['general' => 'Une erreur est survenue lors de l\'inscription. Veuillez réessayer.'],
        'old' => $old
    ];
    header('Location: AUTH.php?tab=signup');
    exit;
}
