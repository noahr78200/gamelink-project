<?php
// API/igdb.php
// Ce fichier parle avec l'API IGDB pour récupérer les jeux

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

// Tes identifiants IGDB
$CLIENT_ID = 'spy0n0vev24kqu6gg3m6t9gh0a9d6r';
$TOKEN = 'jmapwgfaw3021u1ce2zdrqix57gxhz';

// On regarde ce qu'on doit faire
$mode = isset($_POST['mode']) ? $_POST['mode'] : '';

// Le texte de recherche
$search = isset($_POST['search']) ? trim($_POST['search']) : '';
$search = str_replace('"', '', $search); // on enlève les guillemets

// On prépare la requête IGDB
$body = '';

if ($mode === 'search' && $search !== '') {
    // MODE RECHERCHE : on cherche des jeux par nom
    $body = 'search "' . $search . '";
             fields id, name, first_release_date, rating,
                    cover.image_id,
                    genres.name,
                    platforms.name,
                    involved_companies.company.name;
             limit 500;';
    
} elseif ($mode === 'all') {
    // MODE TOUS LES JEUX : on récupère TOUS les jeux disponibles
    // On va faire plusieurs requêtes pour avoir plus de jeux
    
    // On crée un tableau pour stocker tous les jeux
    $allGames = array();
    
    // On fait 10 requêtes de 500 jeux = 1000 jeux au total
    for ($offset = 0; $offset < 1000; $offset += 500) {
        $body = 'fields id, name, first_release_date, rating,
                        cover.image_id,
                        genres.name,
                        platforms.name,
                        involved_companies.company.name;
                 where rating != null;
                 sort rating desc;
                 limit 500;
                 offset ' . $offset . ';';
        
        // On appelle IGDB
        $ch = curl_init('https://api.igdb.com/v4/games');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Client-ID: ' . $CLIENT_ID,
                'Authorization: Bearer ' . $TOKEN,
                'Accept: application/json'
            ],
            CURLOPT_POSTFIELDS => $body
        ]);
        
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Si ça a marché
        if ($status === 200 && $response !== false) {
            $games = json_decode($response, true);
            if (is_array($games) && count($games) > 0) {
                $allGames = array_merge($allGames, $games);
            }
            
            // Si on a reçu moins de 500 jeux, c'est qu'il n'y en a plus
            if (is_array($games) && count($games) < 500) {
                break;
            }
        } else {
            // Si erreur, on arrête
            break;
        }
        
        // On attend un peu pour ne pas surcharger l'API
        usleep(250000); // 0.25 seconde
    }
    
    // On renvoie tous les jeux
    echo json_encode($allGames);
    exit;
    
} else {
    // Si on ne sait pas quoi faire, on renvoie une erreur
    http_response_code(400);
    echo json_encode(['error' => 'Mode invalide']);
    exit;
}

// Vérifier que cURL existe
if (!function_exists('curl_init')) {
    http_response_code(500);
    echo json_encode(['error' => 'cURL PHP n\'est pas installé.']);
    exit;
}

// On appelle IGDB
$ch = curl_init('https://api.igdb.com/v4/games');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Client-ID: ' . $CLIENT_ID,
        'Authorization: Bearer ' . $TOKEN,
        'Accept: application/json'
    ],
    CURLOPT_POSTFIELDS => $body
]);

$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

// Si erreur
if ($response === false) {
    http_response_code(502);
    echo json_encode([
        'error' => 'Requête IGDB échouée',
        'details' => $error
    ]);
    exit;
}

// On renvoie la réponse
http_response_code($status);
echo $response;
?>