<?php
// INCLUDES/auth_login.php - Traitement de la connexion
session_start();
require_once __DIR__ . '/../DATA/DBConfig.php';

// Vérification CSRF
if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
    die("Erreur CSRF");
}

$errors = [];
$old = [];

// Récupération des données
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$old['email'] = $email;

// Validation basique
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['loginEmail'] = "Email invalide";
}

if (strlen($password) < 6) {
    $errors['loginPassword'] = "Mot de passe requis (6 caractères minimum)";
}

// S'il y a des erreurs de format, retour au formulaire
if (!empty($errors)) {
    $_SESSION['flash'] = [
        'errors' => $errors,
        'old' => $old
    ];
    header('Location: ../PAGE/AUTH.php?tab=login');
    exit;
}

// Vérification dans la base de données
try {
    $stmt = $pdo->prepare("SELECT id_joueur, pseudo, password_hash FROM joueur WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($password, $user['password_hash'])) {
        $errors['loginPassword'] = "Email ou mot de passe incorrect";
        $_SESSION['flash'] = [
            'errors' => $errors,
            'old' => $old
        ];
        header('Location: ../PAGE/AUTH.php?tab=login');
        exit;
    }
    
    // Connexion réussie - Stocker temporairement pour le captcha
    $_SESSION['pending_user_id'] = $user['id_joueur'];
    $_SESSION['pending_user_pseudo'] = $user['pseudo'];
    
    // Redirection vers le captcha
    header('Location: ../PAGE/captcha.php');
    exit;
    
} catch (PDOException $e) {
    error_log("Erreur connexion : " . $e->getMessage());
    $_SESSION['flash'] = [
        'errors' => ['general' => "Erreur lors de la connexion"],
        'old' => $old
    ];
    header('Location: ../PAGE/AUTH.php?tab=login');
    exit;
}
