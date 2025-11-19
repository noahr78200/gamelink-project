<?php
// INCLUDES/groupe_join.php - VERSION DEBUG
// Rejoindre un groupe avec logs detailles

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/groupe_join_errors.log');

session_start();
header('Content-Type: application/json');

error_log("=== groupe_join.php appele ===");
error_log("SESSION: " . json_encode($_SESSION));
error_log("POST: " . json_encode($_POST));

// Verifier connexion
if (!isset($_SESSION['user_id'])) {
    error_log("ERREUR: User non connecte");
    echo json_encode(['success' => false, 'message' => 'Non connecte', 'debug' => 'session manquante']);
    exit;
}

try {
    require_once __DIR__ . '/../DATA/DBConfig.php';
    error_log("DBConfig charge OK");
} catch (Exception $e) {
    error_log("ERREUR DBConfig: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur DB config', 'debug' => $e->getMessage()]);
    exit;
}

$mon_id = $_SESSION['user_id'];
$id_groupe = isset($_POST['groupe_id']) ? (int)$_POST['groupe_id'] : 0;

error_log("User ID: $mon_id, Groupe ID: $id_groupe");

if ($id_groupe <= 0) {
    error_log("ERREUR: Groupe ID invalide");
    echo json_encode(['success' => false, 'message' => 'Groupe invalide', 'debug' => 'id=' . $id_groupe]);
    exit;
}

try {
    // Verifier que le groupe existe
    $requete = $pdo->prepare("SELECT id_communaute FROM communaute WHERE id_communaute = ?");
    $requete->execute([$id_groupe]);
    $groupe = $requete->fetch();
    
    error_log("Groupe existe? " . ($groupe ? 'OUI' : 'NON'));
    
    if (!$groupe) {
        error_log("ERREUR: Groupe inexistant");
        echo json_encode(['success' => false, 'message' => 'Groupe inexistant', 'debug' => 'groupe_id=' . $id_groupe]);
        exit;
    }
    
    // Verifier que je ne suis pas deja membre
    $requete = $pdo->prepare("SELECT id_adhesion FROM adhesion WHERE id_joueur = ? AND id_communaute = ?");
    $requete->execute([$mon_id, $id_groupe]);
    $adhesion = $requete->fetch();
    
    error_log("Deja membre? " . ($adhesion ? 'OUI' : 'NON'));
    
    if ($adhesion) {
        error_log("ERREUR: Deja membre");
        echo json_encode(['success' => false, 'message' => 'Deja membre', 'debug' => 'adhesion existe']);
        exit;
    }
    
    // M'ajouter au groupe
    $requete = $pdo->prepare("
        INSERT INTO adhesion (id_joueur, id_communaute, role, statut, date_entree) 
        VALUES (?, ?, 'membre', 'actif', NOW())
    ");
    $resultat = $requete->execute([$mon_id, $id_groupe]);
    
    error_log("Insertion OK? " . ($resultat ? 'OUI' : 'NON'));
    
    if ($resultat) {
        error_log("SUCCES: Groupe rejoint");
        echo json_encode([
            'success' => true, 
            'message' => 'Groupe rejoint !',
            'debug' => 'insertion OK'
        ]);
    } else {
        error_log("ERREUR: Insertion failed");
        echo json_encode(['success' => false, 'message' => 'Erreur insertion', 'debug' => 'execute failed']);
    }
    
} catch (PDOException $e) {
    error_log("ERREUR PDO: " . $e->getMessage());
    error_log("SQL State: " . $e->getCode());
    echo json_encode([
        'success' => false, 
        'message' => 'Erreur serveur',
        'debug' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
} catch (Exception $e) {
    error_log("ERREUR: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur serveur', 'debug' => $e->getMessage()]);
}