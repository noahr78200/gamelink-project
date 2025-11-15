<?php
// ==========================================
// 📝 TRACKER - Version finale
// ==========================================
// Fichier : INCLUDES/track.php

// Connexion à la base
if (!isset($pdo)) {
    require_once __DIR__ . '/../DATA/DBConfig.php';
}

// Récupérer l'ID de l'utilisateur depuis la session
$user_id = $_SESSION['user_id'] ?? null;

// Page actuelle
$page = $_SERVER['REQUEST_URI'] ?? '/';

// ==========================================
// 1. ENREGISTRER LA VUE DE PAGE
// ==========================================

try {
    $stmt = $pdo->prepare("
        INSERT INTO page_views (user_id, page_url, viewed_at)
        VALUES (?, ?, NOW())
    ");
    $stmt->execute([$user_id, $page]);
} catch (Exception $e) {
    // Erreur silencieuse
}

// ==========================================
// 2. METTRE À JOUR L'ACTIVITÉ
// ==========================================

if ($user_id) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO user_activity (user_id, last_activity, page_url)
            VALUES (?, NOW(), ?)
            ON DUPLICATE KEY UPDATE 
                last_activity = NOW(), 
                page_url = VALUES(page_url)
        ");
        $stmt->execute([$user_id, $page]);
    } catch (Exception $e) {
        // Erreur silencieuse
    }
}