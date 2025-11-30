<?php

if (!isset($pdo)) {
    require_once __DIR__ . '/../DATA/DBConfig.php';
}

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

try {
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT user_id) as count 
        FROM user_activity 
        WHERE last_activity >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        AND user_id IS NOT NULL
    ");
    $connectes_24h = (int)$stmt->fetch()['count'];
} catch (Exception $e) {
    $connectes_24h = 0;
}

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

try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM joueur");
    $total_joueurs = (int)$stmt->fetch()['count'];
} catch (Exception $e) {
    $total_joueurs = 0;
}