<?php
// ==========================================
// 🔒 VÉRIFICATION ADMIN - ID 7 SEULEMENT
// ==========================================
// Mets ce fichier dans INCLUDES/check_admin.php

/**
 * Vérifie que l'utilisateur est admin (id_joueur = 7)
 * Redirige vers ACCUEIL.php si ce n'est pas le cas
 */
function require_admin() {
    // Démarrer la session si pas déjà fait
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['id_joueur'])) {
        // Pas connecté → Redirection vers la page de connexion
        header('Location: AUTH.php');
        exit;
    }
    
    // Vérifier si c'est l'admin (id_joueur = 7)
    if ($_SESSION['id_joueur'] != 7) {
        // Connecté mais pas admin → Redirection vers l'accueil
        header('Location: ACCUEIL.php');
        exit;
    }
    
    // Si on arrive ici, c'est bon ! L'utilisateur est l'admin (id = 7)
}

/**
 * Vérifie si l'utilisateur est admin (sans redirection)
 * @return bool True si id_joueur = 7, False sinon
 */
function is_admin() {
    // Démarrer la session si pas déjà fait
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Vérifier si connecté ET si c'est l'ID 7
    return isset($_SESSION['id_joueur']) && $_SESSION['id_joueur'] == 7;
}