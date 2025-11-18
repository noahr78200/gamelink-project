<?php
require "dbconfig.php";
session_start();

$id = $_SESSION['user_id']; 
$email = trim($_POST['email']);

$stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
$stmt->execute([$email, $id]);

header("Location: profile.php?success=email");
exit;