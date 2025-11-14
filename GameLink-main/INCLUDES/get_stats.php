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
// COMPTEUR 1 : Combien de joueurs sont connectés MAINTENANT ?
// ==========================================

try {
    // On compte les joueurs actifs dans les 5 dernières minutes
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT id_joueur) as count 
        FROM user_activity 
        WHERE last_activity >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
    ");
    $online_now = $stmt->fetch()['count'] ?? 0;
    
    // Combien il y en avait hier à la même heure
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT id_joueur) as count 
        FROM user_activity 
        WHERE last_activity >= DATE_SUB(NOW(), INTERVAL 1 DAY + INTERVAL 5 MINUTE)
        AND last_activity < DATE_SUB(NOW(), INTERVAL 1 DAY)
    ");
    $online_yesterday = $stmt->fetch()['count'] ?? 0;
    
    // On calcule la différence en %
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
// COMPTEUR 2 : Nouvelles inscriptions aujourd'hui
// ==========================================

try {
    // Inscriptions aujourd'hui
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM joueur 
        WHERE DATE(date_inscription) = CURDATE()
    ");
    $new_users_today = $stmt->fetch()['count'] ?? 0;
    
    // Inscriptions hier
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM joueur 
        WHERE DATE(date_inscription) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
    ");
    $new_users_yesterday = $stmt->fetch()['count'] ?? 0;
    
    // Calcul du delta
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
// COMPTEUR 3 : Pages les plus visitées
// ==========================================

try {
    // Top 5 des pages aujourd'hui
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
    
    // Total de vues aujourd'hui
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM page_views 
        WHERE DATE(viewed_at) = CURDATE()
    ");
    $total_views_today = $stmt->fetch()['count'] ?? 0;
    
    // Total de vues hier
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM page_views 
        WHERE DATE(viewed_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
    ");
    $total_views_yesterday = $stmt->fetch()['count'] ?? 0;
    
    // Calcul du delta
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
// COMPTEUR 4 : Joueurs actifs aujourd'hui (DAU)
// ==========================================

try {
    // Joueurs actifs aujourd'hui
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT id_joueur) as count 
        FROM user_activity 
        WHERE DATE(last_activity) = CURDATE()
    ");
    $dau_today = $stmt->fetch()['count'] ?? 0;
    
    // Joueurs actifs hier
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT id_joueur) as count 
        FROM user_activity 
        WHERE DATE(last_activity) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
    ");
    $dau_yesterday = $stmt->fetch()['count'] ?? 0;
    
    // Calcul du delta
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
// BONUS : Total de joueurs inscrits
// ==========================================

try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM joueur");
    $total_joueurs = $stmt->fetch()['count'] ?? 0;
} catch (Exception $e) {
    $total_joueurs = 0;
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
    ],
    'total_joueurs' => $total_joueurs
];

// ==========================================
// C'EST FINI ! 🎉
// ==========================================












?>