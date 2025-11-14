<?php
// ==========================================
// 🔍 FICHIER DE TEST
// ==========================================
// Mets ce fichier à la racine de ton projet
// Va sur : localhost/ton-projet/test_stats.php

session_start();

// Connexion à la base
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=gamelink;charset=utf8mb4",
        "root",
        ""
    );
    echo "✅ Connexion à la base OK<br><br>";
} catch (Exception $e) {
    die("❌ Erreur de connexion : " . $e->getMessage());
}

// ==========================================
// TEST 1 : Les tables existent-elles ?
// ==========================================

echo "<h2>📋 TEST 1 : Vérification des tables</h2>";

$tables = ['user_activity', 'page_views', 'joueur'];
foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT 1 FROM $table LIMIT 1");
        echo "✅ Table '$table' existe<br>";
    } catch (Exception $e) {
        echo "❌ Table '$table' n'existe PAS<br>";
    }
}

echo "<br>";

// ==========================================
// TEST 2 : Y a-t-il des données ?
// ==========================================

echo "<h2>📊 TEST 2 : Données dans les tables</h2>";

// Comptage dans user_activity
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM user_activity");
    $count = $stmt->fetch()['count'];
    echo "📝 Lignes dans 'user_activity' : <strong>$count</strong><br>";
} catch (Exception $e) {
    echo "❌ Erreur user_activity : " . $e->getMessage() . "<br>";
}

// Comptage dans page_views
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM page_views");
    $count = $stmt->fetch()['count'];
    echo "📝 Lignes dans 'page_views' : <strong>$count</strong><br>";
} catch (Exception $e) {
    echo "❌ Erreur page_views : " . $e->getMessage() . "<br>";
}

// Comptage des joueurs
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM joueur");
    $count = $stmt->fetch()['count'];
    echo "📝 Nombre de joueurs : <strong>$count</strong><br>";
} catch (Exception $e) {
    echo "❌ Erreur joueur : " . $e->getMessage() . "<br>";
}

echo "<br>";

// ==========================================
// TEST 3 : La session fonctionne-t-elle ?
// ==========================================

echo "<h2>🔐 TEST 3 : Session utilisateur</h2>";

if (isset($_SESSION['id_joueur'])) {
    echo "✅ Session 'id_joueur' existe : <strong>" . $_SESSION['id_joueur'] . "</strong><br>";
} else {
    echo "❌ Session 'id_joueur' N'EXISTE PAS<br>";
    echo "ℹ️ Variables de session disponibles :<br>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
}

echo "<br>";

// ==========================================
// TEST 4 : Calcul des stats
// ==========================================

echo "<h2>📊 TEST 4 : Calcul des statistiques</h2>";

// Joueurs actifs aujourd'hui
try {
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT id_joueur) as count 
        FROM user_activity 
        WHERE DATE(last_activity) = CURDATE()
    ");
    $joueurs_actifs = $stmt->fetch()['count'];
    echo "👥 Joueurs actifs aujourd'hui : <strong>$joueurs_actifs</strong><br>";
} catch (Exception $e) {
    echo "❌ Erreur joueurs actifs : " . $e->getMessage() . "<br>";
}

// Connectés maintenant
try {
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT id_joueur) as count 
        FROM user_activity 
        WHERE last_activity >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
    ");
    $connectes = $stmt->fetch()['count'];
    echo "🟢 Connectés maintenant (5 min) : <strong>$connectes</strong><br>";
} catch (Exception $e) {
    echo "❌ Erreur connectés : " . $e->getMessage() . "<br>";
}

// Pages vues aujourd'hui
try {
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM page_views 
        WHERE DATE(viewed_at) = CURDATE()
    ");
    $pages = $stmt->fetch()['count'];
    echo "📄 Pages vues aujourd'hui : <strong>$pages</strong><br>";
} catch (Exception $e) {
    echo "❌ Erreur pages vues : " . $e->getMessage() . "<br>";
}

echo "<br>";

// ==========================================
// TEST 5 : Voir les dernières activités
// ==========================================

echo "<h2>🕐 TEST 5 : Dernières activités</h2>";

try {
    $stmt = $pdo->query("
        SELECT id_joueur, page_url, last_activity 
        FROM user_activity 
        ORDER BY last_activity DESC 
        LIMIT 5
    ");
    $activities = $stmt->fetchAll();
    
    if (empty($activities)) {
        echo "⚠️ Aucune activité enregistrée<br>";
    } else {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID Joueur</th><th>Page</th><th>Date/Heure</th></tr>";
        foreach ($activities as $act) {
            echo "<tr>";
            echo "<td>" . $act['id_joueur'] . "</td>";
            echo "<td>" . htmlspecialchars($act['page_url']) . "</td>";
            echo "<td>" . $act['last_activity'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "<br>";
}

echo "<br>";

// ==========================================
// TEST 6 : Tester l'insertion manuelle
// ==========================================

echo "<h2>✍️ TEST 6 : Test d'insertion</h2>";

if (isset($_GET['test_insert']) && isset($_SESSION['id_joueur'])) {
    try {
        // Insérer une activité
        $stmt = $pdo->prepare("
            INSERT INTO user_activity (id_joueur, last_activity, page_url)
            VALUES (?, NOW(), ?)
            ON DUPLICATE KEY UPDATE last_activity = NOW(), page_url = ?
        ");
        $stmt->execute([$_SESSION['id_joueur'], '/test', '/test']);
        
        // Insérer une vue de page
        $stmt = $pdo->prepare("
            INSERT INTO page_views (id_joueur, page_url, viewed_at)
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$_SESSION['id_joueur'], '/test']);
        
        echo "✅ Données insérées avec succès !<br>";
        echo "<a href='test_stats.php'>Rafraîchir la page</a>";
    } catch (Exception $e) {
        echo "❌ Erreur insertion : " . $e->getMessage() . "<br>";
    }
} else {
    if (isset($_SESSION['id_joueur'])) {
        echo "<a href='?test_insert=1'>➡️ Cliquez ici pour tester une insertion</a>";
    } else {
        echo "⚠️ Connecte-toi d'abord pour tester l'insertion";
    }
}

echo "<br><br>";
echo "<hr>";
echo "<h3>💡 Que faire maintenant ?</h3>";
echo "<ul>";
echo "<li>Si les tables n'existent pas → Exécute le fichier SQL dans phpMyAdmin</li>";
echo "<li>Si la session n'existe pas → Vérifie ton système de connexion</li>";
echo "<li>Si les stats sont à 0 → Ajoute 'track.php' dans tes pages</li>";
echo "<li>Si tout est OK mais pas de données → Navigue sur le site puis reviens ici</li>";
echo "</ul>";
?>