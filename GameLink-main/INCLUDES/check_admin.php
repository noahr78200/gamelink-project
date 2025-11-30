<?php

function require_admin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../index.php');
        exit;
    }
    
    if ($_SESSION['user_id'] != 7) {
        header('Location: ACCUEIL.php');
        exit;
    }
}

function is_admin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] == 7;
}