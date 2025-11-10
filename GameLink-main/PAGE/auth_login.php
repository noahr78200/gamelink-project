<?php
// auth_login.php - Traitement de la connexion
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
    header('Location: AUTH.php?tab=login');
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
        // Message d'erreur générique pour la sécurité (ne pas indiquer si c'est l'email ou le mdp qui est faux)
        $_SESSION['flash'] = [
            'errors' => ['loginEmail' => 'Email ou mot de passe incorrect'],
            'old' => $old
        ];
        header('Location: AUTH.php?tab=login');
        exit;
    }

    // Mise à jour du hash si nécessaire (si l'algorithme a changé)
    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE joueur SET password_hash = ? WHERE id_joueur = ?");
        $stmt->execute([$new_hash, $user['id_joueur']]);
    }

    // Régénération de l'ID de session pour la sécurité
    session_regenerate_id(true);

    // Création de la session utilisateur
    $_SESSION['user_id'] = $user['id_joueur'];
    $_SESSION['user_pseudo'] = $user['pseudo'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();

    // Message de succès
    $_SESSION['flash'] = [
        'success' => 'Connexion réussie ! Bienvenue, ' . htmlspecialchars($user['pseudo']) . ' !'
    ];

    // Redirection vers le captcha
    header('Location: /PAGE/captcha.php');
    exit;

} catch (PDOException $e) {
    // Log de l'erreur (en production, utiliser un système de log approprié)
    error_log("Erreur connexion : " . $e->getMessage());
    
    $_SESSION['flash'] = [
        'errors' => ['general' => 'Une erreur est survenue lors de la connexion. Veuillez réessayer.'],
        'old' => $old
    ];
    header('Location: AUTH.php?tab=login');
    exit;
}
