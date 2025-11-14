<?php
// ==========================================
// 📝 FICHIER : 2_track_activity.php
// ==========================================
// Ce fichier compte automatiquement qui visite ton site
// Il est comme un compteur de personnes à l'entrée d'un magasin !

// ⚠️ IMPORTANT : Mets ce fichier dans le dossier INCLUDES de ton projet

// ==========================================
// ÉTAPE 1 : On se connecte à la base de données
// ==========================================

// Si tu n'as pas encore de connexion, on la fait maintenant
if (!isset($pdo)) {
    try {
        // 👇 CHANGE CES INFORMATIONS SI BESOIN
        $host = 'localhost';      // L'adresse de ta base de données
        $dbname = 'gamelink';     // Le nom de ta base de données
        $username = 'root';       // Ton nom d'utilisateur (souvent "root")
        $password = '';           // Ton mot de passe (souvent vide sur ton ordi)
        
        // On crée la connexion (comme ouvrir la porte de la base de données)
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password
        );
    } catch (Exception $e) {
        // Si ça marche pas, on fait rien (pour pas casser le site)
        $pdo = null;
    }
}

// Si on n'a pas réussi à se connecter, on arrête ici
if (!$pdo) {
    return;
}

// ==========================================
// ÉTAPE 2 : On note quelle page est visitée
// ==========================================

// On récupère l'adresse de la page actuelle
$current_page = $_SERVER['REQUEST_URI'];

// On note qui a visité (si la personne est connectée)
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

try {
    // On ajoute une ligne dans la boîte "page_views"
    $stmt = $pdo->prepare("
        INSERT INTO page_views (user_id, page_url, viewed_at)
        VALUES (?, ?, NOW())
    ");
    $stmt->execute([$user_id, $current_page]);
} catch (Exception $e) {
    // Si ça marche pas, c'est pas grave, on continue
}

// ==========================================
// ÉTAPE 3 : On note que l'utilisateur est actif
// ==========================================

// Si quelqu'un est connecté, on note qu'il est là maintenant
if ($user_id) {
    try {
        // On vérifie si cet utilisateur a déjà une ligne dans la boîte
        $stmt = $pdo->prepare("SELECT id FROM user_activity WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        if ($stmt->rowCount() > 0) {
            // Il existe déjà, on met à jour l'heure
            $stmt = $pdo->prepare("
                UPDATE user_activity 
                SET last_activity = NOW(), page_url = ?
                WHERE user_id = ?
            ");
            $stmt->execute([$current_page, $user_id]);
        } else {
            // Il n'existe pas encore, on le crée
            $stmt = $pdo->prepare("
                INSERT INTO user_activity (user_id, last_activity, page_url)
                VALUES (?, NOW(), ?)
            ");
            $stmt->execute([$user_id, $current_page]);
        }
    } catch (Exception $e) {
        // Si ça marche pas, c'est pas grave
    }
}

// ==========================================
// C'EST FINI ! 🎉
// ==========================================
// Maintenant, chaque fois que quelqu'un visite une page,
// c'est noté automatiquement dans la base de données !