<?php

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecte']);
    exit;
}

require_once __DIR__ . '/../DATA/DBConfig.php';

$mon_id = $_SESSION['user_id'];
$id_groupe = isset($_GET['groupe_id']) ? (int)$_GET['groupe_id'] : 0;

if ($id_groupe <= 0) {
    echo json_encode(['success' => false, 'message' => 'Groupe invalide']);
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
        echo json_encode(['success' => false, 'message' => 'Pas membre']);
        exit;
    }
    
    $requete = $pdo->prepare("
        SELECT p.id_publication, p.contenu, p.date_creation, j.pseudo
        FROM publication p
        JOIN joueur j ON p.id_joueur = j.id_joueur
        WHERE p.id_communaute = ? AND p.titre IS NULL
        ORDER BY p.date_creation ASC
        LIMIT 50
    ");
    $requete->execute([$id_groupe]);
    $messages_bruts = $requete->fetchAll(PDO::FETCH_ASSOC);
    
    $messages = [];
    foreach ($messages_bruts as $msg) {
        $messages[] = [
            'id' => $msg['id_publication'],
            'pseudo' => $msg['pseudo'],
            'contenu' => $msg['contenu'],
            'heure' => date('H:i', strtotime($msg['date_creation']))
        ];
    }
    
    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'nombre' => count($messages)
    ]);
    
} catch (Exception $erreur) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}