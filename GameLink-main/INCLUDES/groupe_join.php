<?php
/*
 * ========================================
 * REJOINDRE UN GROUPE - VERSION SIMPLE
 * ========================================
 * 
 * Ce fichier permet de rejoindre un groupe
 * 
 */

// ÉTAPE 1 : Démarrer la session
session_start();

// ÉTAPE 2 : Dire au navigateur qu'on envoie du JSON
header('Content-Type: application/json');

// ÉTAPE 3 : Vérifier que tu es connecté
if (!isset($_SESSION['user_id'])) {
    // Si tu n'es pas connecté, on arrête
    echo json_encode(['success' => false, 'message' => 'Tu dois être connecté']);
    exit;
}

// ÉTAPE 4 : Se connecter à la base de données
require_once __DIR__ . '/../DATA/DBConfig.php';

// ÉTAPE 5 : Récupérer les informations
$mon_id = $_SESSION['user_id'];           // Mon ID
$id_groupe = isset($_POST['groupe_id']) ? (int)$_POST['groupe_id'] : 0;  // ID du groupe

// ÉTAPE 6 : Vérifier que le groupe existe
try {
    // Demander à la base de données si le groupe existe
    $requete = $pdo->prepare("SELECT id_communaute FROM communaute WHERE id_communaute = ?");
    $requete->execute([$id_groupe]);
    
    if (!$requete->fetch()) {
        // Le groupe n'existe pas
        echo json_encode(['success' => false, 'message' => 'Ce groupe n\'existe pas']);
        exit;
    }
    
    // ÉTAPE 7 : Vérifier que je ne suis pas déjà membre
    $requete = $pdo->prepare("SELECT id_adhesion FROM adhesion WHERE id_joueur = ? AND id_communaute = ?");
    $requete->execute([$mon_id, $id_groupe]);
    
    if ($requete->fetch()) {
        // Je suis déjà membre
        echo json_encode(['success' => false, 'message' => 'Tu es déjà membre de ce groupe']);
        exit;
    }
    
    // ÉTAPE 8 : M'ajouter au groupe
    $requete = $pdo->prepare("
        INSERT INTO adhesion (id_joueur, id_communaute, role, statut, date_entree) 
        VALUES (?, ?, 'membre', 'actif', NOW())
    ");
    $requete->execute([$mon_id, $id_groupe]);
    
    // ÉTAPE 9 : Répondre que c'est bon
    echo json_encode([
        'success' => true, 
        'message' => 'Tu as rejoint le groupe !'
    ]);
    
} catch (Exception $erreur) {
    // S'il y a un problème
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}