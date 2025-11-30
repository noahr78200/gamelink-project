<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

$CLIENT_ID = 'spy0n0vev24kqu6gg3m6t9gh0a9d6r';
$TOKEN = 'jmapwgfaw3021u1ce2zdrqix57gxhz';

$mode = isset($_POST['mode']) ? $_POST['mode'] : '';

$search = isset($_POST['search']) ? trim($_POST['search']) : '';
$search = str_replace('"', '', $search);

$body = '';

if ($mode === 'search' && $search !== '') {
    $body = 'search "' . $search . '";
             fields id, name, first_release_date, rating,
                    cover.image_id,
                    genres.name,
                    platforms.name,
                    involved_companies.company.name;
             limit 500;';
    
} elseif ($mode === 'all') {
    $allGames = array();
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
        
        if ($status === 200 && $response !== false) {
            $games = json_decode($response, true);
            if (is_array($games) && count($games) > 0) {
                $allGames = array_merge($allGames, $games);
            }
            
            if (is_array($games) && count($games) < 500) {
                break;
            }
        } else {
            break;
        }
        usleep(250000);
    }
    
    echo json_encode($allGames);
    exit;
    
} else {

    http_response_code(400);
    echo json_encode(['error' => 'Mode invalide']);
    exit;
}

if (!function_exists('curl_init')) {
    http_response_code(500);
    echo json_encode(['error' => 'cURL PHP n\'est pas installé.']);
    exit;
}

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

http_response_code($status);
echo $response;
?>