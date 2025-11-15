<?php
// ==========================================
// 🔒 CHECK ADMIN - user_id = 7
// ==========================================
// Fichier : INCLUDES/check_admin.php

/**
 * Vérifie que l'utilisateur est admin (user_id = 7)
 */
function require_admin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Pas connecté
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../index.php');
        exit;
    }
    
    // Connecté mais pas admin
    if ($_SESSION['user_id'] != 7) {
        header('Location: ACCUEIL.php');
        exit;
    }
}

/**
 * Vérifie si l'utilisateur est admin (sans redirection)
 */
function is_admin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] == 7;
}