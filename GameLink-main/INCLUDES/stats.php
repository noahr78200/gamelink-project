<?php
// ==========================================
// 📊 STATISTIQUES - Version finale
// ==========================================
// Fichier : INCLUDES/stats.php

// Connexion à la base
if (!isset($pdo)) {
    require_once __DIR__ . '/../DATA/DBConfig.php';
}

// ==========================================
// STAT 1 : Inscriptions aujourd'hui
// ==========================================

try {
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM joueur 
        WHERE DATE(date_inscription) = CURDATE()
    ");
    $inscriptions_aujourd_hui = (int)$stmt->fetch()['count'];
} catch (Exception $e) {
    $inscriptions_aujourd_hui = 0;
}

// ==========================================
// STAT 2 : Connectés EN CE MOMENT (5 min)
// ==========================================

try {
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT user_id) as count 
        FROM user_activity 
        WHERE last_activity >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        AND user_id IS NOT NULL
    ");
    $connectes_maintenant = (int)$stmt->fetch()['count'];
} catch (Exception $e) {
    $connectes_maintenant = 0;
}

// ==========================================
// STAT 3 : Pages vues aujourd'hui
// ==========================================

try {
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM page_views 
        WHERE DATE(viewed_at) = CURDATE()
    ");
    $pages_vues_aujourd_hui = (int)$stmt->fetch()['count'];
} catch (Exception $e) {
    $pages_vues_aujourd_hui = 0;
}

// ==========================================
// STAT 4 : Top 5 des pages les plus visitées
// ==========================================

try {
    $stmt = $pdo->query("
        SELECT 
            page_url, 
            COUNT(*) as views
        FROM page_views 
        WHERE DATE(viewed_at) = CURDATE()
        GROUP BY page_url
        ORDER BY views DESC
        LIMIT 5
    ");
    $top_pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $top_pages = [];
}

// ==========================================
// BONUS : Total de joueurs inscrits
// ==========================================

try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM joueur");
    $total_joueurs = (int)$stmt->fetch()['count'];
} catch (Exception $e) {
    $total_joueurs = 0;
}