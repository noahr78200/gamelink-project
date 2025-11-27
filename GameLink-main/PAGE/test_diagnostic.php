<?php
// ==========================================
// PAGE DE TEST DIAGNOSTIC - NE PAS UTILISER EN PRODUCTION
// ==========================================

session_start();

// Vérification admin
require_once __DIR__ . '/../INCLUDES/check_admin.php';
require_admin();

// Connexion BDD
require_once __DIR__ . '/../DATA/DBConfig.php';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test Diagnostic</title>
    <style>
        body {
            font-family: monospace;
            background: #1a1e27;
            color: #e7e9ed;
            padding: 20px;
            line-height: 1.6;
        }
        .test-block {
            background: #0f1115;
            border: 2px solid #2a3140;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .success {
            color: #34d399;
        }
        .error {
            color: #f87171;
        }
        .info {
            color: #6ea8fe;
        }
        pre {
            background: #000;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        h2 {
            color: #6ea8fe;
            border-bottom: 2px solid #2a3140;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
    <h1>🔍 Diagnostic GameLink - Table Joueur</h1>

    <!-- TEST 1 : Connexion PDO -->
    <div class="test-block">
        <h2>TEST 1 : Connexion à la base de données</h2>
        <?php
        if (isset($pdo)) {
            echo '<p class="success">✅ Variable $pdo existe</p>';
            echo '<p class="info">Type : ' . get_class($pdo) . '</p>';
        } else {
            echo '<p class="error">❌ Variable $pdo n\'existe pas !</p>';
            echo '<p>Le fichier DBConfig.php ne crée pas la variable $pdo correctement.</p>';
        }
        ?>
    </div>

    <!-- TEST 2 : Requête simple COUNT -->
    <div class="test-block">
        <h2>TEST 2 : Compter les joueurs</h2>
        <?php
        if (isset($pdo)) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM joueur");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $total = $result['total'];
                
                if ($total > 0) {
                    echo '<p class="success">✅ ' . $total . ' joueur(s) trouvé(s) dans la base</p>';
                } else {
                    echo '<p class="error">⚠️ La table joueur existe mais est vide (0 joueurs)</p>';
                }
            } catch (PDOException $e) {
                echo '<p class="error">❌ Erreur SQL : ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
        } else {
            echo '<p class="error">❌ Impossible de tester sans connexion PDO</p>';
        }
        ?>
    </div>

    <!-- TEST 3 : Récupérer un joueur -->
    <div class="test-block">
        <h2>TEST 3 : Récupérer les données d'un joueur</h2>
        <?php
        if (isset($pdo)) {
            try {
                $stmt = $pdo->query("SELECT * FROM joueur LIMIT 1");
                $joueur = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($joueur) {
                    echo '<p class="success">✅ Joueur récupéré avec succès</p>';
                    echo '<pre>';
                    print_r($joueur);
                    echo '</pre>';
                } else {
                    echo '<p class="error">⚠️ Aucun joueur à afficher (table vide)</p>';
                }
            } catch (PDOException $e) {
                echo '<p class="error">❌ Erreur SQL : ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
        } else {
            echo '<p class="error">❌ Impossible de tester sans connexion PDO</p>';
        }
        ?>
    </div>

    <!-- TEST 4 : Liste de tous les joueurs -->
    <div class="test-block">
        <h2>TEST 4 : Liste de tous les joueurs</h2>
        <?php
        if (isset($pdo)) {
            try {
                $stmt = $pdo->query("SELECT id_joueur, pseudo, email, date_inscription FROM joueur ORDER BY date_inscription DESC");
                $joueurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($joueurs) > 0) {
                    echo '<p class="success">✅ ' . count($joueurs) . ' joueur(s) récupéré(s)</p>';
                    echo '<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">';
                    echo '<thead><tr style="background: #2a3140;">';
                    echo '<th style="padding: 10px; text-align: left;">ID</th>';
                    echo '<th style="padding: 10px; text-align: left;">Pseudo</th>';
                    echo '<th style="padding: 10px; text-align: left;">Email</th>';
                    echo '<th style="padding: 10px; text-align: left;">Date inscription</th>';
                    echo '</tr></thead><tbody>';
                    
                    foreach ($joueurs as $j) {
                        echo '<tr style="border-bottom: 1px solid #2a3140;">';
                        echo '<td style="padding: 10px;">' . htmlspecialchars($j['id_joueur']) . '</td>';
                        echo '<td style="padding: 10px;">' . htmlspecialchars($j['pseudo']) . '</td>';
                        echo '<td style="padding: 10px;">' . htmlspecialchars($j['email']) . '</td>';
                        echo '<td style="padding: 10px;">' . htmlspecialchars($j['date_inscription']) . '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody></table>';
                } else {
                    echo '<p class="error">⚠️ Aucun joueur trouvé</p>';
                }
            } catch (PDOException $e) {
                echo '<p class="error">❌ Erreur SQL : ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
        } else {
            echo '<p class="error">❌ Impossible de tester sans connexion PDO</p>';
        }
        ?>
    </div>

    <!-- TEST 5 : Structure de la table -->
    <div class="test-block">
        <h2>TEST 5 : Structure de la table joueur</h2>
        <?php
        if (isset($pdo)) {
            try {
                $stmt = $pdo->query("DESCRIBE joueur");
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo '<p class="success">✅ Structure de la table :</p>';
                echo '<table style="width: 100%; border-collapse: collapse;">';
                echo '<thead><tr style="background: #2a3140;">';
                echo '<th style="padding: 10px; text-align: left;">Colonne</th>';
                echo '<th style="padding: 10px; text-align: left;">Type</th>';
                echo '<th style="padding: 10px; text-align: left;">Null</th>';
                echo '<th style="padding: 10px; text-align: left;">Clé</th>';
                echo '</tr></thead><tbody>';
                
                foreach ($columns as $col) {
                    echo '<tr style="border-bottom: 1px solid #2a3140;">';
                    echo '<td style="padding: 10px;">' . htmlspecialchars($col['Field']) . '</td>';
                    echo '<td style="padding: 10px;">' . htmlspecialchars($col['Type']) . '</td>';
                    echo '<td style="padding: 10px;">' . htmlspecialchars($col['Null']) . '</td>';
                    echo '<td style="padding: 10px;">' . htmlspecialchars($col['Key']) . '</td>';
                    echo '</tr>';
                }
                
                echo '</tbody></table>';
            } catch (PDOException $e) {
                echo '<p class="error">❌ Erreur SQL : ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
        } else {
            echo '<p class="error">❌ Impossible de tester sans connexion PDO</p>';
        }
        ?>
    </div>

    <!-- TEST 6 : Test du fichier users_management.php -->
    <div class="test-block">
        <h2>TEST 6 : Fichier users_management.php</h2>
        <?php
        $users_file = __DIR__ . '/users_management.php';
        if (file_exists($users_file)) {
            echo '<p class="success">✅ Fichier existe : ' . $users_file . '</p>';
            echo '<p class="info">Taille : ' . filesize($users_file) . ' octets</p>';
        } else {
            echo '<p class="error">❌ Fichier introuvable : ' . $users_file . '</p>';
        }
        ?>
    </div>

    <div class="test-block">
        <h2>📋 Résumé et Actions</h2>
        <p>Si tous les tests sont verts ✅, le problème vient de l'intégration dans ADMIN.php</p>
        <p>Si un test est rouge ❌, il faut corriger ce problème en premier</p>
        <br>
        <p><a href="ADMIN.php?tab=users" style="color: #6ea8fe;">← Retour à la page admin (onglet utilisateurs)</a></p>
    </div>

</body>
</html>