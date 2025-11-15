<?php

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

$CLIENT_ID = 'spy0n0vev24kqu6gg3m6t9gh0a9d6r';
$TOKEN     = ' lilj1h9k3hhug3bcmkch3ujuraj8y3'; // token client_credentials : expire regulierement

$rawBody = file_get_contents('php://input');
if ($rawBody === false || trim($rawBody) === '') {
  http_response_code(400);
  echo json_encode(['error' => 'Body vide (attendu: texte IGDB, ex: "fields name;")']);
  exit;
}

$ch = curl_init('https://api.igdb.com/v4/games');
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST           => true,
  CURLOPT_HTTPHEADER     => [
    "Client-ID: $CLIENT_ID",
    "Authorization: Bearer $TOKEN",
    "Accept: application/json",
    "Content-Type: text/plain"
  ],
  CURLOPT_POSTFIELDS     => $rawBody,
  CURLOPT_TIMEOUT        => 12
]);

$response = curl_exec($ch);
$status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error    = curl_error($ch);
curl_close($ch);

if ($response === false) {
  http_response_code(502);
  echo json_encode(['error' => 'Requête IGDB échouée', 'details' => $error]);
  exit;
}

http_response_code($status);
echo $response;
