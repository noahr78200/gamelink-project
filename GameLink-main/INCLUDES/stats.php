<?php
// ==========================================
// 📊 STATS AVEC TON dbconfig.php
// ==========================================
// Mets ce fichier dans INCLUDES/stats.php

// ==========================================
// MODE DEBUG
// ==========================================

// Mettre à true pour voir les valeurs
$DEBUG_MODE = true;

// ==========================================
// CONNEXION EN UTILISANT TON FICHIER
// ==========================================

if (!isset($pdo)) {
    require_once __DIR__ . '/dbconfig.php';
}

// Initialiser les variables
$joueurs_actifs = 0;
$connectes_maintenant = 0;
$pages_vues = 0;
$top_pages = [];
$total_joueurs = 0;

// ==========================================
// COMPTEUR 1 : Joueurs actifs aujourd'hui
// ==========================================

try {
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT id_joueur) as count 
        FROM user_activity 
        WHERE DATE(last_activity) = CURDATE()
        AND id_joueur IS NOT NULL
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $joueurs_actifs = (int)$result['count'];
    
    if ($DEBUG_MODE) {
        echo "<!-- DEBUG Joueurs actifs : $joueurs_actifs -->\n";
    }
} catch (Exception $e) {
    if ($DEBUG_MODE) {
        echo "<!-- ERREUR Joueurs actifs : " . $e->getMessage() . " -->\n";
    }
}

// ==========================================
// COMPTEUR 2 : Connectés maintenant
// ==========================================

try {
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT id_joueur) as count 
        FROM user_activity 
        WHERE last_activity >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        AND id_joueur IS NOT NULL
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $connectes_maintenant = (int)$result['count'];
    
    if ($DEBUG_MODE) {
        echo "<!-- DEBUG Connectés maintenant : $connectes_maintenant -->\n";
    }
} catch (Exception $e) {
    if ($DEBUG_MODE) {
        echo "<!-- ERREUR Connectés : " . $e->getMessage() . " -->\n";
    }
}

// ==========================================
// COMPTEUR 3 : Pages vues aujourd'hui
// ==========================================

try {
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM page_views 
        WHERE DATE(viewed_at) = CURDATE()
    ");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $pages_vues = (int)$result['count'];
    
    if ($DEBUG_MODE) {
        echo "<!-- DEBUG Pages vues : $pages_vues -->\n";
    }
} catch (Exception $e) {
    if ($DEBUG_MODE) {
        echo "<!-- ERREUR Pages vues : " . $e->getMessage() . " -->\n";
    }
}

// ==========================================
// BONUS : Top 5 des pages
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
    
    if ($DEBUG_MODE) {
        echo "<!-- DEBUG Top pages : " . count($top_pages) . " pages trouvées -->\n";
    }
} catch (Exception $e) {
    if ($DEBUG_MODE) {
        echo "<!-- ERREUR Top pages : " . $e->getMessage() . " -->\n";
    }
    $top_pages = [];
}

// ==========================================
// BONUS : Total de joueurs
// ==========================================

try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM joueur");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_joueurs = (int)$result['count'];
    
    if ($DEBUG_MODE) {
        echo "<!-- DEBUG Total joueurs : $total_joueurs -->\n";
    }
} catch (Exception $e) {
    if ($DEBUG_MODE) {
        echo "<!-- ERREUR Total joueurs : " . $e->getMessage() . " -->\n";
    }
}

// ==========================================
// VÉRIFIER LES DONNÉES
// ==========================================

if ($DEBUG_MODE) {
    try {
        // Compter les lignes dans user_activity
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM user_activity");
        $count_activity = $stmt->fetch()['count'];
        echo "<!-- Total lignes dans user_activity : $count_activity -->\n";
        
        // Compter les lignes dans page_views
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM page_views");
        $count_views = $stmt->fetch()['count'];
        echo "<!-- Total lignes dans page_views : $count_views -->\n";
        
        // Voir les dernières activités
        $stmt = $pdo->query("
            SELECT id_joueur, last_activity, page_url 
            FROM user_activity 
            ORDER BY last_activity DESC 
            LIMIT 3
        ");
        $recent = $stmt->fetchAll();
        echo "<!-- Dernières activités : " . print_r($recent, true) . " -->\n";
        
    } catch (Exception $e) {
        echo "<!-- ERREUR debug tables : " . $e->getMessage() . " -->\n";
    }
}

// ==========================================
// RÉSUMÉ DEBUG
// ==========================================

if ($DEBUG_MODE) {
    echo "\n<!-- ========================================== -->\n";
    echo "<!-- RÉSUMÉ DES STATISTIQUES -->\n";
    echo "<!-- Joueurs actifs aujourd'hui : $joueurs_actifs -->\n";
    echo "<!-- Connectés maintenant : $connectes_maintenant -->\n";
    echo "<!-- Pages vues aujourd'hui : $pages_vues -->\n";
    echo "<!-- Total joueurs inscrits : $total_joueurs -->\n";
    echo "<!-- ========================================== -->\n\n";
}