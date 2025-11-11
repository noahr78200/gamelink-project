<?php

// Configuration de la connexion à la base de données
$host = "localhost";
$dbname = "gamelink";
$user = "gamelink_user";
$pass = "toor"; // À modifier avec votre mot de passe MySQL sur le serveur OVH

$dsn  = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
  $pdo = new PDO($dsn, $user, $pass, $options);
} catch (Throwable $e) {
  http_response_code(500);
  error_log("Erreur DB : " . $e->getMessage());
  die("Erreur de connexion à la base de données");
}

file_put_contents('/tmp/GL_trace.log', date('c')." DB_OK\n", FILE_APPEND);
