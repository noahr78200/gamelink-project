<?php
//data 
session_start();    
require_once __DIR__ . '/../DATA/DBConfig.php';

// Récupérer les informations de l'utilisateur connecté
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, email, bio FROM users WHERE id = ?");   
$stmt->execute([$user_id]);
$user = $stmt->fetch(); 
?>