<?php
// ==========================================
// FICHIER : check_admin.php
// BUT : Vérifier si l'utilisateur est admin
// ==========================================

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =======================
// FONCTION : Est-ce un admin ?
// =======================
function is_admin() {
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Vérifier si c'est l'ID 7
    if ($_SESSION['user_id'] == 7) {
        return true;
    }
    
    return false;
}

// =======================
// FONCTION : Bloquer si pas admin
// =======================
function require_admin() {
    // Si l'utilisateur n'est pas admin
    if (!is_admin()) {
        // Rediriger vers l'accueil avec un message d'erreur
        $_SESSION['flash'] = [
            'error' => 'Accès refusé. Cette page est réservée aux administrateurs.'
        ];
        header('Location: /PAGE/ACCUEIL.php');
        exit;
    }
}