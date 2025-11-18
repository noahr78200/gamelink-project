<?php
require "dbconfig.php";
session_start();

$id = $_SESSION['user_id'];
$old = $_POST['old_mdp'];
$new = $_POST['new_mdp'];

$stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!password_verify($old, $user['password'])) {
    die("Mot de passe actuel incorrect");
}

$newHash = password_hash($new, PASSWORD_BCRYPT);

$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->execute([$newHash, $id]);

header("Location: profile.php?success=mdp");
exit;