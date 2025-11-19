<?php
/*
 * ========================================
 * ENVOYER UN MESSAGE - VERSION SIMPLE
 * ========================================
 * 
 * Ce fichier permet d'envoyer un message dans un groupe
 * 
 */

// ÉTAPE 1 : Démarrer la session
session_start();

// ÉTAPE 2 : Dire qu'on envoie du JSON
header('Content-Type: application/json');

// ÉTAPE 3 : Vérifier que tu es connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Tu dois être connecté']);
    exit;
}

// ÉTAPE 4 : Se connecter à la base de données
require_once __DIR__ . '/../DATA/DBConfig.php';

// ÉTAPE 5 : Récupérer les infos
$mon_id = $_SESSION['user_id'];
$id_groupe = isset($_POST['groupe_id']) ? (int)$_POST['groupe_id'] : 0;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// ÉTAPE 6 : Vérifier que tout est OK
if ($id_groupe <= 0) {
    echo json_encode(['success' => false, 'message' => 'Groupe invalide']);
    exit;
}

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Le message ne peut pas être vide']);
    exit;
}

if (strlen($message) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Le message est trop long (max 1000 caractères)']);
    exit;
}

try {
    // ÉTAPE 7 : Vérifier que je suis membre du groupe
    $requete = $pdo->prepare("
        SELECT id_adhesion 
        FROM adhesion 
        WHERE id_joueur = ? AND id_communaute = ? AND statut = 'actif'
    ");
    $requete->execute([$mon_id, $id_groupe]);
    
    if (!$requete->fetch()) {
        // Je ne suis pas membre
        echo json_encode(['success' => false, 'message' => 'Tu dois être membre pour envoyer un message']);
        exit;
    }
    
    // ÉTAPE 8 : Enregistrer le message dans la base de données
    $requete = $pdo->prepare("
        INSERT INTO publication (id_joueur, id_communaute, titre, contenu, date_creation) 
        VALUES (?, ?, NULL, ?, NOW())
    ");
    $requete->execute([$mon_id, $id_groupe, $message]);
    
    // Récupérer l'ID du message créé
    $id_message = $pdo->lastInsertId();
    
    // ÉTAPE 9 : Répondre que c'est bon
    echo json_encode([
        'success' => true, 
        'message' => 'Message envoyé !',
        'id_message' => $id_message
    ]);
    
} catch (Exception $erreur) {
    // Problème
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}