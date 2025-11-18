<?php
// INCLUDES/groupe_message.php - Envoyer un message dans un groupe
session_start();
header('Content-Type: application/json');

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Tu dois être connecté']);
    exit;
}

require_once __DIR__ . '/../DATA/DBConfig.php';

$user_id = $_SESSION['user_id'];
$groupe_id = isset($_POST['groupe_id']) ? (int)$_POST['groupe_id'] : 0;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Vérifications
if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Le message ne peut pas être vide']);
    exit;
}

if (strlen($message) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Le message est trop long (max 1000 caractères)']);
    exit;
}

try {
    // Vérifier que l'utilisateur est membre du groupe
    $stmt = $pdo->prepare("
        SELECT id_joueur 
        FROM adhesion 
        WHERE id_joueur = ? AND id_communaute = ? AND statut = 'actif'
    ");
    $stmt->execute([$user_id, $groupe_id]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Tu dois être membre du groupe pour envoyer un message']);
        exit;
    }
    
    // Insérer le message
    $stmt = $pdo->prepare("
        INSERT INTO publication (id_joueur, id_communaute, titre, contenu, date_creation) 
        VALUES (?, ?, NULL, ?, NOW())
    ");
    $stmt->execute([$user_id, $groupe_id, $message]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Message envoyé !',
        'message_id' => $pdo->lastInsertId()
    ]);
    
} catch (PDOException $e) {
    error_log("Erreur envoi message : " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}