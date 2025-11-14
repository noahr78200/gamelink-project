<?php
// ==========================================
// 📊 FICHIER : 3_get_stats.php
// ==========================================
// Ce fichier va compter combien de personnes visitent ton site
// C'est comme un compteur qui compte les visiteurs !

// ⚠️ IMPORTANT : Mets ce fichier dans le dossier INCLUDES de ton projet

// ==========================================
// CONNEXION À LA BASE DE DONNÉES
// ==========================================
session_start();
require_once __DIR__ . '/../DATA/DBConfig.php';



// ==========================================
// COMPTEUR 1 : Combien de personnes sont connectées MAINTENANT ?
// ==========================================

// On compte les personnes actives dans les 5 dernières minutes
try {
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT user_id) as count 
        FROM user_activity 
        WHERE last_activity >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
    ");
    $online_now = $stmt->fetch()['count'] ?? 0;
    
    // On compte aussi combien il y en avait hier à la même heure
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT user_id) as count 
        FROM user_activity 
        WHERE last_activity >= DATE_SUB(NOW(), INTERVAL 1 DAY + INTERVAL 5 MINUTE)
        AND last_activity < DATE_SUB(NOW(), INTERVAL 1 DAY)
    ");
    $online_yesterday = $stmt->fetch()['count'] ?? 0;
    
    // On calcule si c'est plus ou moins qu'hier (en %)
    if ($online_yesterday > 0) {
        $online_delta = round((($online_now - $online_yesterday) / $online_yesterday) * 100, 1);
    } else {
        $online_delta = 0;
    }
} catch (Exception $e) {
    $online_now = 0;
    $online_delta = 0;
}

// ==========================================
// COMPTEUR 2 : Combien de nouvelles inscriptions AUJOURD'HUI ?
// ==========================================

try {
    // Inscriptions aujourd'hui
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM users 
        WHERE DATE(created_at) = CURDATE()
    ");
    $new_users_today = $stmt->fetch()['count'] ?? 0;
    
    // Inscriptions hier
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM users 
        WHERE DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
    ");
    $new_users_yesterday = $stmt->fetch()['count'] ?? 0;
    
    // On calcule la différence en %
    if ($new_users_yesterday > 0) {
        $new_users_delta = round((($new_users_today - $new_users_yesterday) / $new_users_yesterday) * 100, 1);
    } else {
        $new_users_delta = 0;
    }
} catch (Exception $e) {
    $new_users_today = 0;
    $new_users_delta = 0;
}

// ==========================================
// COMPTEUR 3 : Quelles pages sont les plus visitées ?
// ==========================================

try {
    // On cherche les 5 pages les plus visitées aujourd'hui
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
    $top_pages = $stmt->fetchAll();
    
    // On compte aussi le total de vues aujourd'hui
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM page_views 
        WHERE DATE(viewed_at) = CURDATE()
    ");
    $total_views_today = $stmt->fetch()['count'] ?? 0;
    
    // On compte le total de vues hier
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM page_views 
        WHERE DATE(viewed_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
    ");
    $total_views_yesterday = $stmt->fetch()['count'] ?? 0;
    
    // On calcule la différence en %
    if ($total_views_yesterday > 0) {
        $page_views_delta = round((($total_views_today - $total_views_yesterday) / $total_views_yesterday) * 100, 1);
    } else {
        $page_views_delta = 0;
    }
} catch (Exception $e) {
    $top_pages = [];
    $total_views_today = 0;
    $page_views_delta = 0;
}

// ==========================================
// COMPTEUR 4 : Combien de personnes ont visité le site aujourd'hui ?
// ==========================================

try {
    // Nombre de visiteurs aujourd'hui
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT user_id) as count 
        FROM user_activity 
        WHERE DATE(last_activity) = CURDATE()
    ");
    $dau_today = $stmt->fetch()['count'] ?? 0;
    
    // Nombre de visiteurs hier
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT user_id) as count 
        FROM user_activity 
        WHERE DATE(last_activity) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
    ");
    $dau_yesterday = $stmt->fetch()['count'] ?? 0;
    
    // On calcule la différence en %
    if ($dau_yesterday > 0) {
        $dau_delta = round((($dau_today - $dau_yesterday) / $dau_yesterday) * 100, 1);
    } else {
        $dau_delta = 0;
    }
} catch (Exception $e) {
    $dau_today = 0;
    $dau_delta = 0;
}

// ==========================================
// ON PRÉPARE TOUT DANS UN TABLEAU
// ==========================================

$stats = [
    'online_users' => [
        'value' => $online_now,
        'delta' => $online_delta,
        'trend' => $online_delta >= 0 ? 'up' : 'down'
    ],
    'new_registrations' => [
        'value' => $new_users_today,
        'delta' => $new_users_delta,
        'trend' => $new_users_delta >= 0 ? 'up' : 'down'
    ],
    'page_views' => [
        'value' => $total_views_today,
        'delta' => $page_views_delta,
        'trend' => $page_views_delta >= 0 ? 'up' : 'down',
        'top_pages' => $top_pages
    ],
    'dau' => [
        'value' => $dau_today,
        'delta' => $dau_delta,
        'trend' => $dau_delta >= 0 ? 'up' : 'down'
    ]
];

// ==========================================
// C'EST FINI ! 🎉
// ==========================================
// Maintenant $stats contient toutes les informations !