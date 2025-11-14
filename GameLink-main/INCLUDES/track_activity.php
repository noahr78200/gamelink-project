<?php
// ==========================================
// 📝 FICHIER : 2_track_activity.php
// ==========================================
// Ce fichier compte automatiquement qui visite ton site
// ADAPTÉ pour ta table JOUEUR existante

// ⚠️ IMPORTANT : Mets ce fichier dans le dossier INCLUDES de ton projet

// ==========================================
// ÉTAPE 1 : On se connecte à la base de données
// ==========================================

if (!isset($pdo)) {
    try {
        // 👇 CHANGE CES INFORMATIONS SI BESOIN
        $host = 'localhost';
        $dbname = 'gamelink';
        $username = 'root';
        $password = '';
        
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password
        );
    } catch (Exception $e) {
        $pdo = null;
    }
}

// Si pas de connexion, on arrête
if (!$pdo) {
    return;
}

// ==========================================
// ÉTAPE 2 : On note quelle page est visitée
// ==========================================

$current_page = $_SERVER['REQUEST_URI'];

// On récupère l'ID du joueur (de ta table joueur)
$id_joueur = isset($_SESSION['id_joueur']) ? $_SESSION['id_joueur'] : null;

try {
    // On ajoute une ligne dans page_views
    $stmt = $pdo->prepare("
        INSERT INTO page_views (id_joueur, page_url, viewed_at)
        VALUES (?, ?, NOW())
    ");
    $stmt->execute([$id_joueur, $current_page]);
} catch (Exception $e) {
    // Erreur silencieuse
}

// ==========================================
// ÉTAPE 3 : On met à jour l'activité du joueur
// ==========================================

if ($id_joueur) {
    try {
        // On vérifie si ce joueur existe déjà dans user_activity
        $stmt = $pdo->prepare("SELECT id FROM user_activity WHERE id_joueur = ?");
        $stmt->execute([$id_joueur]);
        
        if ($stmt->rowCount() > 0) {
            // Il existe, on met à jour
            $stmt = $pdo->prepare("
                UPDATE user_activity 
                SET last_activity = NOW(), page_url = ?
                WHERE id_joueur = ?
            ");
            $stmt->execute([$current_page, $id_joueur]);
        } else {
            // Il n'existe pas, on le crée
            $stmt = $pdo->prepare("
                INSERT INTO user_activity (id_joueur, last_activity, page_url)
                VALUES (?, NOW(), ?)
            ");
            $stmt->execute([$id_joueur, $current_page]);
        }
    } catch (Exception $e) {
        // Erreur silencieuse
    }
}

// ==========================================
// C'EST FINI ! 🎉
// ==========================================
// Maintenant chaque visite est enregistrée !