<?php
// INCLUDES/group_message.php - VERSION DEBUG
// Envoyer un message avec logs detailles

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/group_message_errors.log');

session_start();
header('Content-Type: application/json');

error_log("=== group_message.php appele ===");
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
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

error_log("User ID: $mon_id, Groupe ID: $id_groupe, Message: $message");

// Verifications
if ($id_groupe <= 0) {
    error_log("ERREUR: Groupe invalide");
    echo json_encode(['success' => false, 'message' => 'Groupe invalide', 'debug' => 'id=' . $id_groupe]);
    exit;
}

if (empty($message)) {
    error_log("ERREUR: Message vide");
    echo json_encode(['success' => false, 'message' => 'Message vide', 'debug' => 'message empty']);
    exit;
}

if (strlen($message) > 1000) {
    error_log("ERREUR: Message trop long");
    echo json_encode(['success' => false, 'message' => 'Message trop long', 'debug' => 'length=' . strlen($message)]);
    exit;
}

try {
    // Verifier que je suis membre
    $requete = $pdo->prepare("
        SELECT id_adhesion 
        FROM adhesion 
        WHERE id_joueur = ? AND id_communaute = ? AND statut = 'actif'
    ");
    $requete->execute([$mon_id, $id_groupe]);
    $adhesion = $requete->fetch();
    
    error_log("Est membre? " . ($adhesion ? 'OUI' : 'NON'));
    
    if (!$adhesion) {
        error_log("ERREUR: Pas membre");
        echo json_encode(['success' => false, 'message' => 'Pas membre', 'debug' => 'adhesion manquante']);
        exit;
    }
    
    // Enregistrer le message
    $requete = $pdo->prepare("
        INSERT INTO publication (id_joueur, id_communaute, titre, contenu, date_creation) 
        VALUES (?, ?, NULL, ?, NOW())
    ");
    $resultat = $requete->execute([$mon_id, $id_groupe, $message]);
    
    error_log("Insertion OK? " . ($resultat ? 'OUI' : 'NON'));
    
    if ($resultat) {
        $id_message = $pdo->lastInsertId();
        error_log("SUCCES: Message envoye, ID=$id_message");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Envoye !',
            'id_message' => $id_message,
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