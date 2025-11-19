<?php
// INCLUDES/forum_create.php
// Creer une discussion sur le forum

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecte']);
    exit;
}

require_once __DIR__ . '/../DATA/DBConfig.php';

$mon_id = $_SESSION['user_id'];
$titre = isset($_POST['titre']) ? trim($_POST['titre']) : '';
$contenu = isset($_POST['contenu']) ? trim($_POST['contenu']) : '';

if (empty($titre) || empty($contenu)) {
    echo json_encode(['success' => false, 'message' => 'Titre et message requis']);
    exit;
}

try {
    // Verifier si communaute Forum existe
    $stmt = $pdo->query("SELECT id_communaute FROM communaute WHERE nom = 'Forum General' LIMIT 1");
    $forum = $stmt->fetch();
    
    if (!$forum) {
        $stmt = $pdo->prepare("INSERT INTO communaute (nom, description, visibilite) VALUES ('Forum General', 'Forum de discussion', 'public')");
        $stmt->execute();
        $id_communaute = $pdo->lastInsertId();
    } else {
        $id_communaute = $forum['id_communaute'];
    }
    
    // Creer la discussion
    $stmt = $pdo->prepare("INSERT INTO publication (id_joueur, id_communaute, titre, contenu, date_creation) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$mon_id, $id_communaute, $titre, $contenu]);
    
    echo json_encode(['success' => true, 'message' => 'Discussion creee !', 'id_discussion' => $pdo->lastInsertId()]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}