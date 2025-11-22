<?php
session_start();
require_once __DIR__ . '/../DATA/DBConfig.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT pseudo, email, bio FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>