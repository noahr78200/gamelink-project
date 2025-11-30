<?php

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecte']);
    exit;
}

require_once __DIR__ . '/../DATA/DBConfig.php';

$mon_id = $_SESSION['user_id'];
$id_groupe = isset($_POST['groupe_id']) ? (int)$_POST['groupe_id'] : 0;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if ($id_groupe <= 0) {
    echo json_encode(['success' => false, 'message' => 'Groupe invalide']);
    exit;
}

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message vide']);
    exit;
}

if (strlen($message) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Message trop long (max 1000 caracteres)']);
    exit;
}

try {
    $requete = $pdo->prepare("
        SELECT COUNT(*) as nb
        FROM adhesion 
        WHERE id_joueur = ? AND id_communaute = ? AND statut = 'actif'
    ");
    $requete->execute([$mon_id, $id_groupe]);
    $resultat = $requete->fetch();
    
    if ($resultat['nb'] == 0) {
        echo json_encode(['success' => false, 'message' => 'Pas membre de ce groupe']);
        exit;
    }
    
    $requete = $pdo->prepare("
        INSERT INTO publication (id_joueur, id_communaute, titre, contenu, date_creation) 
        VALUES (?, ?, NULL, ?, NOW())
    ");
    $requete->execute([$mon_id, $id_groupe, $message]);
    
    $id_message = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Message envoye !',
        'id_message' => $id_message
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}