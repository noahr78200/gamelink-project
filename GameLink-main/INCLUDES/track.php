<?php
// ==========================================
// 📝 TRACKER AVEC TON dbconfig.php
// ==========================================
// Mets ce fichier dans INCLUDES/track.php

// ==========================================
// CONNEXION EN UTILISANT TON FICHIER
// ==========================================

if (!isset($pdo)) {
    require_once __DIR__ . '/dbconfig.php';
}

// Créer un fichier de log pour voir ce qui se passe
$log_file = __DIR__ . '/track_debug.log';

function write_log($message) {
    global $log_file;
    $time = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$time] $message\n", FILE_APPEND);
}

write_log("========== TRACKER APPELÉ ==========");

// ==========================================
// RÉCUPÉRER LES INFOS
// ==========================================

// Vérifier toutes les variables de session possibles
$id_joueur = null;

if (isset($_SESSION['id_joueur'])) {
    $id_joueur = $_SESSION['id_joueur'];
    write_log("✅ Session id_joueur trouvée: $id_joueur");
} elseif (isset($_SESSION['user_id'])) {
    $id_joueur = $_SESSION['user_id'];
    write_log("⚠️ Utilise user_id au lieu de id_joueur: $id_joueur");
} elseif (isset($_SESSION['joueur_id'])) {
    $id_joueur = $_SESSION['joueur_id'];
    write_log("⚠️ Utilise joueur_id au lieu de id_joueur: $id_joueur");
} else {
    write_log("❌ AUCUNE session trouvée - Variables disponibles: " . print_r(array_keys($_SESSION), true));
}

// Page actuelle
$page = $_SERVER['REQUEST_URI'] ?? '/unknown';
write_log("📄 Page visitée: $page");

// ==========================================
// ENREGISTRER LA VUE DE PAGE
// ==========================================

try {
    $stmt = $pdo->prepare("
        INSERT INTO page_views (id_joueur, page_url, viewed_at)
        VALUES (?, ?, NOW())
    ");
    $stmt->execute([$id_joueur, $page]);
    write_log("✅ Vue de page enregistrée (id_joueur: " . ($id_joueur ?? 'NULL') . ")");
} catch (Exception $e) {
    write_log("❌ Erreur insertion page_views: " . $e->getMessage());
}

// ==========================================
// METTRE À JOUR L'ACTIVITÉ
// ==========================================

if ($id_joueur) {
    try {
        // Vérifier si le joueur existe
        $stmt = $pdo->prepare("SELECT id_joueur FROM joueur WHERE id_joueur = ?");
        $stmt->execute([$id_joueur]);
        
        if ($stmt->rowCount() === 0) {
            write_log("❌ Le joueur $id_joueur n'existe PAS dans la table joueur !");
        } else {
            write_log("✅ Le joueur $id_joueur existe dans la table joueur");
            
            // Insérer ou mettre à jour
            $stmt = $pdo->prepare("
                INSERT INTO user_activity (id_joueur, last_activity, page_url)
                VALUES (?, NOW(), ?)
                ON DUPLICATE KEY UPDATE 
                    last_activity = NOW(), 
                    page_url = VALUES(page_url)
            ");
            $stmt->execute([$id_joueur, $page]);
            write_log("✅ Activité mise à jour pour le joueur $id_joueur");
        }
    } catch (Exception $e) {
        write_log("❌ Erreur user_activity: " . $e->getMessage());
    }
} else {
    write_log("⚠️ Pas d'id_joueur → Activité non enregistrée (visiteur anonyme)");
}

write_log("========== FIN TRACKER ==========\n");