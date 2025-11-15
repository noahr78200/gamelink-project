<?php
// ==========================================
// 🔒 VÉRIFICATION ADMIN SIMPLE
// ==========================================
// Mets ce fichier dans INCLUDES/check_admin.php

/**
 * Vérifie que l'utilisateur est admin
 * Redirige vers AUTH.php sinon
 */
function require_admin() {
    // Démarrer la session si pas déjà fait
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['id_joueur'])) {
        header('Location: AUTH.php');
        exit;
    }
    
    // Optionnel : Vérifier si c'est vraiment un admin
    // Si tu as un champ 'is_admin' dans ta table joueur, décommente :
    /*
    require_once __DIR__ . '/dbconfig.php';
    $stmt = $pdo->prepare("SELECT is_admin FROM joueur WHERE id_joueur = ?");
    $stmt->execute([$_SESSION['id_joueur']]);
    $user = $stmt->fetch();
    
    if (!$user || !$user['is_admin']) {
        header('Location: ACCUEIL.php');
        exit;
    }
    */
}

/**
 * Vérifie si l'utilisateur est admin (sans redirection)
 * @return bool
 */
function is_admin() {
    // Démarrer la session si pas déjà fait
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Pour l'instant, on considère que tous les connectés sont admin
    // Change cette logique selon tes besoins
    return isset($_SESSION['id_joueur']);
    
    // Optionnel : Vérifier dans la base de données
    /*
    if (!isset($_SESSION['id_joueur'])) {
        return false;
    }
    
    require_once __DIR__ . '/dbconfig.php';
    $stmt = $pdo->prepare("SELECT is_admin FROM joueur WHERE id_joueur = ?");
    $stmt->execute([$_SESSION['id_joueur']]);
    $user = $stmt->fetch();
    
    return $user && $user['is_admin'];
    */
}