<?php
// INCLUDES/auth_register.php - Traitement de l'inscription
session_start();
require_once __DIR__ . '/../DATA/DBConfig.php';

// Vérification CSRF
if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
    die("Erreur CSRF");
}

$errors = [];
$old = [];

// Récupération et validation des données
$pseudo = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';
$cgu = isset($_POST['cgu']);

// Stocker les anciennes valeurs pour préremplir le formulaire
$old['name'] = $pseudo;
$old['email'] = $email;

// Validation
if (strlen($pseudo) < 3) {
    $errors['suName'] = "Le pseudo doit contenir au moins 3 caractères";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['suEmail'] = "Email invalide";
}

if (strlen($password) < 6) {
    $errors['suPass'] = "Le mot de passe doit contenir au moins 6 caractères";
}

if ($password !== $password_confirm) {
    $errors['suConfirm'] = "Les mots de passe ne correspondent pas";
}

if (!$cgu) {
    $errors['suCgu'] = "Vous devez accepter les CGU";
}

// Vérifier si l'email ou le pseudo existe déjà
if (empty($errors)) {
    try {
        $stmt = $pdo->prepare("SELECT id_joueur FROM joueur WHERE email = ? OR pseudo = ?");
        $stmt->execute([$email, $pseudo]);
        
        if ($stmt->fetch()) {
            $errors['suEmail'] = "Cet email ou pseudo est déjà utilisé";
        }
    } catch (PDOException $e) {
        error_log("Erreur vérification user : " . $e->getMessage());
        $errors['general'] = "Erreur lors de la vérification";
    }
}

// S'il y a des erreurs, retour au formulaire
if (!empty($errors)) {
    $_SESSION['flash'] = [
        'errors' => $errors,
        'old' => $old
    ];
    header('Location: ../PAGE/AUTH.php?tab=signup');
    exit;
}

// Inscription réussie - Hasher le mot de passe
$password_hash = password_hash($password, PASSWORD_BCRYPT);

try {
    $stmt = $pdo->prepare("
        INSERT INTO joueur (pseudo, email, password_hash) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$pseudo, $email, $password_hash]);
    
    // Récupérer l'ID du nouvel utilisateur
    $user_id = $pdo->lastInsertId();
    
    // Stocker temporairement l'ID pour le captcha
    $_SESSION['pending_user_id'] = $user_id;
    $_SESSION['pending_user_pseudo'] = $pseudo;
    
    // Redirection vers le captcha
    header('Location: ../PAGE/captcha.php');
    exit;
    
} catch (PDOException $e) {
    error_log("Erreur inscription : " . $e->getMessage());
    $_SESSION['flash'] = [
        'errors' => ['general' => "Erreur lors de l'inscription"],
        'old' => $old
    ];
    header('Location: ../PAGE/AUTH.php?tab=signup');
    exit;
}
