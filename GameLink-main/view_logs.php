<?php

$log_file = __DIR__ . '/INCLUDES/track_debug.log';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Logs du Tracker</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            margin: 0;
        }
        h1 {
            color: #4ec9b0;
            border-bottom: 2px solid #4ec9b0;
            padding-bottom: 10px;
        }
        .log-container {
            background: #252526;
            padding: 20px;
            border-radius: 8px;
            max-height: 80vh;
            overflow-y: auto;
        }
        .log-line {
            margin: 5px 0;
            padding: 5px;
            border-left: 3px solid #444;
        }
        .log-line.success {
            border-left-color: #4ec9b0;
            color: #4ec9b0;
        }
        .log-line.error {
            border-left-color: #f48771;
            color: #f48771;
        }
        .log-line.warning {
            border-left-color: #dcdcaa;
            color: #dcdcaa;
        }
        .log-line.separator {
            border-left-color: #569cd6;
            color: #569cd6;
            font-weight: bold;
        }
        .buttons {
            margin: 20px 0;
        }
        button, a {
            background: #0e639c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
        }
        button:hover, a:hover {
            background: #1177bb;
        }
        .info {
            background: #264f78;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background: #252526;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #4ec9b0;
        }
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #4ec9b0;
        }
        .stat-label {
            color: #858585;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <h1>📖 Logs du Tracker - Diagnostic</h1>
    
    <div class="info">
        <strong>ℹ️ Comment utiliser cette page :</strong><br>
        1. Navigue sur ton site (2-3 pages minimum)<br>
        2. Rafraîchis cette page (F5)<br>
        3. Lis les logs ci-dessous pour voir ce qui se passe
    </div>

    <?php
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=gamelink;charset=utf8mb4", "root", "");
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM user_activity");
        $count_activity = $stmt->fetchColumn();
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM page_views");
        $count_views = $stmt->fetchColumn();
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM page_views WHERE DATE(viewed_at) = CURDATE()");
        $count_today = $stmt->fetchColumn();
        
        echo '<div class="stats">';
        echo '<div class="stat-card">';
        echo '<div class="stat-value">' . $count_activity . '</div>';
        echo '<div class="stat-label">Lignes dans user_activity</div>';
        echo '</div>';
        
        echo '<div class="stat-card">';
        echo '<div class="stat-value">' . $count_views . '</div>';
        echo '<div class="stat-label">Lignes dans page_views</div>';
        echo '</div>';
        
        echo '<div class="stat-card">';
        echo '<div class="stat-value">' . $count_today . '</div>';
        echo '<div class="stat-label">Vues aujourd\'hui</div>';
        echo '</div>';
        echo '</div>';
        
    } catch (Exception $e) {
        echo '<div style="color: #f48771; padding: 10px; background: #3d1f1f; border-radius: 4px;">';
        echo '❌ Erreur de connexion à la base : ' . htmlspecialchars($e->getMessage());
        echo '</div>';
    }
    ?>

    <div class="buttons">
        <a href="?refresh=1">🔄 Rafraîchir</a>
        <a href="?clear=1" onclick="return confirm('Supprimer tous les logs ?')">🗑️ Vider les logs</a>
        <a href="PAGE/ADMIN.php">📊 Voir la page Admin</a>
    </div>

    <div class="log-container">
        <?php
        if (isset($_GET['clear'])) {
            file_put_contents($log_file, '');
            echo '<div style="color: #4ec9b0;">✅ Logs vidés !</div>';
        }
        
        if (file_exists($log_file)) {
            $logs = file($log_file);
            
            if (empty($logs)) {
                echo '<div style="color: #dcdcaa; padding: 20px; text-align: center;">';
                echo '⚠️ Aucun log pour le moment<br><br>';
                echo 'Navigue sur ton site, puis reviens ici et rafraîchis la page !';
                echo '</div>';
            } else {
                echo '<strong>' . count($logs) . ' lignes de log</strong><br><br>';
                
                foreach ($logs as $line) {
                    $line = htmlspecialchars(trim($line));
                    
                    $class = 'log-line';
                    if (strpos($line, '✅') !== false) {
                        $class .= ' success';
                    } elseif (strpos($line, '❌') !== false) {
                        $class .= ' error';
                    } elseif (strpos($line, '⚠️') !== false) {
                        $class .= ' warning';
                    } elseif (strpos($line, '====') !== false) {
                        $class .= ' separator';
                    }
                    
                    echo '<div class="' . $class . '">' . $line . '</div>';
                }
            }
        } else {
            echo '<div style="color: #f48771; padding: 20px; text-align: center;">';
            echo '❌ Fichier de log introuvable : ' . htmlspecialchars($log_file) . '<br><br>';
            echo 'Assure-toi que track_DEBUG.php est bien dans /INCLUDES/ et qu\'il a été exécuté au moins une fois.';
            echo '</div>';
        }
        ?>
    </div>
</body>
</html>