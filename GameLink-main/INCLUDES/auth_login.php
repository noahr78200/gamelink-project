<?php
// INCLUDES/auth_login.php - Traitement de la connexion
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

// Récupération des données
$email = validate_input($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$errors = [];
$old = ['email' => $email];

// Validation de l'email
if (empty($email)) {
    $errors['loginEmail'] = 'L\'email est requis';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['loginEmail'] = 'Format d\'email invalide';
}

// Validation du mot de passe
if (empty($password)) {
    $errors['loginPassword'] = 'Le mot de passe est requis';
} elseif (strlen($password) < 6) {
    $errors['loginPassword'] = 'Le mot de passe doit contenir au moins 6 caractères';
}

// Si erreurs de validation, retour au formulaire
if (!empty($errors)) {
    $_SESSION['flash'] = [
        'errors' => $errors,
        'old' => $old
    ];
    header('Location: ../PAGE/AUTH.php?tab=login');
    exit;
}

try {
    // Recherche de l'utilisateur par email
    $stmt = $pdo->prepare("
        SELECT id_joueur, pseudo, email, password_hash 
        FROM joueur 
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Vérification de l'existence de l'utilisateur et du mot de passe
    if (!$user || !password_verify($password, $user['password_hash'])) {
        // Message d'erreur générique pour la sécurité
        $_SESSION['flash'] = [
            'errors' => ['loginEmail' => 'Email ou mot de passe incorrect'],
            'old' => $old
        ];
        header('Location: ../PAGE/AUTH.php?tab=login');
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

    // Stocker l'utilisateur en attente de validation captcha
    $_SESSION['pending_user_id'] = $user['id_joueur'];
    $_SESSION['pending_user_pseudo'] = $user['pseudo'];
    $_SESSION['pending_user_email'] = $user['email'];
    
    // Initialiser le compteur de tentatives captcha
    $_SESSION['captcha_attempts'] = 0;

    // Redirection vers le captcha
    header('Location: ../PAGE/captcha.php');
    exit;

} catch (PDOException $e) {
    // Log de l'erreur
    error_log("Erreur connexion : " . $e->getMessage());
    
    $_SESSION['flash'] = [
        'errors' => ['general' => 'Une erreur est survenue lors de la connexion. Veuillez réessayer.'],
        'old' => $old
    ];
    header('Location: ../PAGE/AUTH.php?tab=login');
    exit;
}