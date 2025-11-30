<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../DATA/DBConfig.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'savescore':
            $data = json_decode(file_get_contents('php://input'), true);
            $score = intval($data['score'] ?? 0);
            
            if ($score <= 0) {
                echo json_encode(['success' => false, 'message' => 'Score invalide']);
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO snake_scores (id_joueur, score) VALUES (?, ?)");
            $stmt->execute([$userId, $score]);

            $stmt = $pdo->prepare("
                SELECT MAX(score) as max_score 
                FROM snake_scores 
                WHERE id_joueur = ?
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $isNewHighScore = ($score >= $result['max_score']);

            echo json_encode([
                'success' => true,
                'message' => 'Score sauvegardé',
                'isNewHighScore' => $isNewHighScore
            ]);
            break;

        case 'gethighscore':
            $stmt = $pdo->prepare("
                SELECT MAX(score) as high_score 
                FROM snake_scores 
                WHERE id_joueur = ?
            ");
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'highScore' => intval($result['high_score'] ?? 0)
            ]);
            break;

        case 'getleaderboard':
            $limit = intval($_GET['limit'] ?? 10);
            $limit = min(max($limit, 1), 100);
            
            $stmt = $pdo->prepare("
                SELECT 
                    j.pseudo,
                    MAX(s.score) as score,
                    MAX(s.date_score) as date_score
                FROM snake_scores s
                INNER JOIN joueur j ON s.id_joueur = j.id_joueur
                GROUP BY s.id_joueur, j.pseudo
                ORDER BY score DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'leaderboard' => $leaderboard
            ]);
            break;

        case 'getstats':
            $stmt = $pdo->prepare("
                SELECT 
                    COUNT(*) as total_games,
                    MAX(score) as best_score,
                    AVG(score) as avg_score,
                    MIN(date_score) as first_game,
                    MAX(date_score) as last_game
                FROM snake_scores 
                WHERE id_joueur = ?
            ");
            $stmt->execute([$userId]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'stats' => $stats
            ]);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Action inconnue'
            ]);
            break;
    }

} catch (PDOException $e) {
    error_log("Erreur Snake API: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur'
    ]);
}
?>