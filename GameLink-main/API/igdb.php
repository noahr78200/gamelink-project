<?php
// API/igdb.php
// Ce fichier appelle l'API IGDB et renvoie une liste de jeux en JSON.

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

// ⚠️ Mets bien ton client_id et ton access_token ici :
$CLIENT_ID = 'spy0n0vev24kqu6gg3m6t9gh0a9d6r';
$TOKEN     = 'jmapwgfaw3021u1ce2zdrqix57gxhz'; // ton access_token actuel

// Lecture du texte de recherche (peut être vide)
$search = isset($_POST['search']) ? trim($_POST['search']) : '';
$search = str_replace('"', '', $search); // on enlève les guillemets pour éviter les soucis

// Si on a un texte → recherche par nom
if ($search !== '') {
    $body = 'search "' . $search . '";
             fields id, name, cover.image_id, first_release_date, rating,
                    genres.name, platforms.name, involved_companies.company.name;
             sort name asc;
             limit 50;';
} else {
    // Sinon, on charge juste une liste de jeux triés A → Z
    $body = 'fields id, name, cover.image_id, first_release_date, rating,
                    genres.name, platforms.name, involved_companies.company.name;
             sort name asc;
             limit 50;';
}

// Vérification rapide que cURL existe
if (!function_exists('curl_init')) {
    http_response_code(500);
    echo json_encode(['error' => 'cURL PHP n\'est pas installé.']);
    exit;
}

// Appel à IGDB
$ch = curl_init('https://api.igdb.com/v4/games');

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        'Client-ID: ' . $CLIENT_ID,
        'Authorization: Bearer ' . $TOKEN,
        'Accept: application/json'
    ],
    CURLOPT_POSTFIELDS     => $body
]);

$response = curl_exec($ch);
$status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error    = curl_error($ch);

curl_close($ch);

// Si cURL a complètement échoué
if ($response === false) {
    http_response_code(502);
    echo json_encode([
        'error'   => 'Requête IGDB échouée',
        'details' => $error
    ]);
    exit;
}

// On renvoie tel quel ce que répond IGDB
http_response_code($status);
echo $response;
