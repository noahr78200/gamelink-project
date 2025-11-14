<?php
// ==========================================
// FICHIER : manage_captcha.php (VERSION 100% CORRIGÉE)
// BUT : Gérer les questions du captcha (pour les admins)
// ==========================================

// Protection admin
require_once __DIR__ . '/../INCLUDES/check_admin.php';
require_admin();

// Chemin du fichier JSON
define('CAPTCHA_JSON', __DIR__ . '/../DATA/captcha_bank.json');

// --------- Fonctions améliorées ---------

// Charger les questions depuis le JSON
function load_captcha_bank() {
    // Vérifier si le dossier DATA existe
    $dir = dirname(CAPTCHA_JSON);
    if (!is_dir($dir)) {
        // Créer le dossier avec les bonnes permissions
        if (!@mkdir($dir, 0777, true)) {
            return ['error' => 'Impossible de créer le dossier DATA'];
        }
        @chmod($dir, 0777);
    }
    
    // Si le fichier n'existe pas, créer un fichier avec une question par défaut
    if (!file_exists(CAPTCHA_JSON)) {
        $defaultBank = [
            [
                'q' => 'Quelle est la capitale de la France ?',
                'a' => 'paris|Paris|PARIS',
                'enabled' => true
            ]
        ];
        $result = @file_put_contents(
            CAPTCHA_JSON,
            json_encode($defaultBank, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
        
        if ($result === false) {
            return ['error' => 'Impossible de créer le fichier captcha_bank.json'];
        }
        
        @chmod(CAPTCHA_JSON, 0666);
        return $defaultBank;
    }

    // Lire le fichier
    $txt = @file_get_contents(CAPTCHA_JSON);
    if ($txt === false) {
        return ['error' => 'Impossible de lire le fichier captcha_bank.json'];
    }
    
    $data = @json_decode($txt, true);
    
    if (!is_array($data)) {
        return [];
    }

    return $data;
}

// Sauvegarder les questions dans le JSON (avec gestion d'erreur)
function save_captcha_bank(array $bank) {
    // Vérifier que le dossier existe
    $dir = dirname(CAPTCHA_JSON);
    if (!is_dir($dir)) {
        if (!@mkdir($dir, 0777, true)) {
            return false;
        }
        @chmod($dir, 0777);
    }
    
    // Préparer le contenu JSON
    $jsonContent = json_encode($bank, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    if ($jsonContent === false) {
        return false;
    }
    
    // Écrire dans le fichier
    $result = @file_put_contents(CAPTCHA_JSON, $jsonContent);
    
    if ($result === false) {
        return false;
    }
    
    // Changer les permissions du fichier pour qu'il soit accessible
    @chmod(CAPTCHA_JSON, 0666);
    
    return true;
}

// --------- Variables de base ---------
$bank    = load_captcha_bank();
$message = '';
$error   = '';

// Vérifier s'il y a une erreur de chargement
if (is_array($bank) && isset($bank['error'])) {
    $error = $bank['error'];
    $bank = [];
}

// Informations de débogage
$fileExists = file_exists(CAPTCHA_JSON);
$dirExists = is_dir(dirname(CAPTCHA_JSON));
$dirWritable = is_writable(dirname(CAPTCHA_JSON));
$fileWritable = $fileExists ? is_writable(CAPTCHA_JSON) : false;

// --------- Gestion du formulaire ---------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // On recharge à chaque action pour être sûr
    $bank = load_captcha_bank();
    if (is_array($bank) && isset($bank['error'])) {
        $error = $bank['error'];
        $bank = [];
    }

    // 1) Ajouter une nouvelle question
    if ($action === 'add') {
        $question = trim($_POST['question'] ?? '');
        $answer   = trim($_POST['answer'] ?? '');

        if ($question === '' || $answer === '') {
            $error = "Merci de remplir la question ET la réponse 😊";
        } else {
            // On ajoute une nouvelle entrée
            $bank[] = [
                'q'       => $question,
                'a'       => $answer,
                'enabled' => true
            ];
            
            // Sauvegarder avec gestion d'erreur
            if (save_captcha_bank($bank)) {
                $message = "✅ Question ajoutée avec succès !";
                $bank = load_captcha_bank();
            } else {
                $error = "❌ Erreur lors de l'ajout : impossible d'écrire dans le fichier. Vérifie les permissions du dossier DATA (doit être en 777).";
            }
        }
    }

    // 2) Supprimer une question
    if ($action === 'delete') {
        $index = isset($_POST['index']) ? (int) $_POST['index'] : -1;

        if (isset($bank[$index])) {
            array_splice($bank, $index, 1);
            
            if (save_captcha_bank($bank)) {
                $message = "🗑️ Question supprimée avec succès !";
                $bank = load_captcha_bank();
            } else {
                $error = "❌ Erreur lors de la suppression : impossible d'écrire dans le fichier. Vérifie les permissions du dossier DATA (doit être en 777).";
            }
        }
    }

    // 3) Activer / désactiver une question
    if ($action === 'toggle') {
        $index = isset($_POST['index']) ? (int) $_POST['index'] : -1;

        if (isset($bank[$index])) {
            $bank[$index]['enabled'] = !($bank[$index]['enabled'] ?? false);
            
            if (save_captcha_bank($bank)) {
                $message = "🔄 Statut de la question mis à jour !";
                $bank = load_captcha_bank();
            } else {
                $error = "❌ Erreur lors de la mise à jour : impossible d'écrire dans le fichier. Vérifie les permissions du dossier DATA (doit être en 777).";
            }
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Gestion du Captcha - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body.admin {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #0f172a;
            color: #e5e7eb;
        }

        header {
            background: #020617;
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
        }

        .Menu {
            display: flex;
            align-items: center;
            gap: 24px;
            padding: 12px 32px;
        }

        .Menu a {
            color: #e5e7eb;
            text-decoration: none;
            font-size: 15px;
            letter-spacing: 0.03em;
        }

        .Menu a:hover {
            color: #93c5fd;
        }

        .logo {
            height: 32px;
        }

        main {
            padding: 32px 16px 48px;
        }

        .captcha-wrapper {
            max-width: 1000px;
            margin: 0 auto;
        }

        .captcha-card {
            background: #111827;
            border-radius: 16px;
            padding: 24px 24px 28px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.75);
            border: 1px solid rgba(148, 163, 184, 0.25);
        }

        .captcha-title {
            font-size: 22px;
            margin-bottom: 4px;
        }

        .captcha-sub {
            font-size: 14px;
            color: #9ca3af;
            margin-bottom: 20px;
        }

        .message {
            margin-bottom: 20px;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 14px;
            background: rgba(22, 163, 74, 0.1);
            border: 1px solid rgba(22, 163, 74, 0.6);
            color: #bbf7d0;
        }

        .error {
            margin-bottom: 20px;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 14px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.6);
            color: #fecaca;
        }

        .debug-box {
            margin-bottom: 20px;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            background: rgba(168, 85, 247, 0.1);
            border: 1px solid rgba(168, 85, 247, 0.5);
            color: #e9d5ff;
        }

        .debug-box code {
            background: rgba(15, 23, 42, 0.7);
            padding: 2px 4px;
            border-radius: 4px;
            font-size: 12px;
        }

        .fix-button {
            margin-top: 10px;
            padding: 8px 16px;
            border-radius: 999px;
            border: none;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .fix-button:hover {
            filter: brightness(1.1);
        }

        .info-box {
            margin-bottom: 20px;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            background: rgba(37, 99, 235, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.5);
            color: #dbeafe;
        }

        .info-box code {
            background: rgba(15, 23, 42, 0.7);
            padding: 2px 4px;
            border-radius: 4px;
            font-size: 12px;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 16px;
        }

        .form-group {
            flex: 1;
            min-width: 200px;
        }

        label {
            display: block;
            font-size: 13px;
            margin-bottom: 4px;
            color: #9ca3af;
        }

        input[type="text"] {
            width: 100%;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid rgba(75, 85, 99, 0.8);
            background: #020617;
            color: #e5e7eb;
            font-size: 14px;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #60a5fa;
            box-shadow: 0 0 0 1px rgba(37, 99, 235, 0.7);
        }

        .btn-primary {
            padding: 10px 16px;
            border-radius: 999px;
            border: none;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            color: white;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
        }

        .btn-primary:hover {
            filter: brightness(1.1);
        }

        .questions-title {
            margin-top: 24px;
            margin-bottom: 10px;
            font-size: 18px;
        }

        .captcha-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            font-size: 14px;
        }

        .captcha-table th,
        .captcha-table td {
            padding: 8px 10px;
            border-bottom: 1px solid rgba(31, 41, 55, 0.9);
            vertical-align: top;
        }

        .captcha-table th {
            text-align: left;
            font-size: 13px;
            color: #9ca3af;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
        }

        .badge-on {
            background: rgba(22, 163, 74, 0.2);
            color: #bbf7d0;
        }

        .badge-off {
            background: rgba(239, 68, 68, 0.2);
            color: #fecaca;
        }

        .action-buttons {
            display: flex;
            gap: 6px;
        }

        .btn-small {
            border-radius: 999px;
            border: none;
            font-size: 12px;
            padding: 6px 10px;
            cursor: pointer;
        }

        .btn-toggle {
            background: rgba(234, 179, 8, 0.15);
            color: #facc15;
            border: 1px solid rgba(234, 179, 8, 0.6);
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.15);
            color: #fecaca;
            border: 1px solid rgba(239, 68, 68, 0.6);
        }

        .back-link {
            display: inline-block;
            margin-top: 18px;
            font-size: 14px;
            color: #93c5fd;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 640px) {
            .captcha-card {
                padding: 18px 14px 20px;
            }
            .captcha-title {
                font-size: 19px;
            }
        }
    </style>
</head>
<body class="admin">


<main>
    <div class="captcha-wrapper">
        <div class="captcha-card">
            <h1 class="captcha-title">🔒 Gestion des questions Captcha</h1>
            <p class="captcha-sub">
                Ajoute, active/désactive ou supprime les questions utilisées pour vérifier que l'utilisateur est humain.
            </p>

            <!-- Boîte de débogage -->
            <div class="debug-box">
                <strong>🔍 Diagnostic du système :</strong><br>
                📁 Dossier DATA : <?= $dirExists ? '✅ Existe' : '❌ N\'existe pas' ?><br>
                📝 Dossier accessible en écriture : <?= $dirWritable ? '✅ Oui' : '❌ Non' ?><br>
                📄 Fichier captcha_bank.json : <?= $fileExists ? '✅ Existe' : '❌ N\'existe pas' ?><br>
                ✏️ Fichier accessible en écriture : <?= $fileWritable ? '✅ Oui' : '❌ Non' ?><br>
                📊 Questions chargées : <strong><?= count($bank) ?></strong><br>
                <code><?= htmlspecialchars(CAPTCHA_JSON) ?></code>
                
                <?php if (!$dirWritable || !$fileWritable): ?>
                    <br><br>
                    <strong>⚠️ Action requise :</strong> Le dossier ou le fichier n'est pas accessible en écriture !<br>
                    <strong>Solution rapide :</strong> Dans ton Terminal, tape :
                    <code style="display: block; margin-top: 8px;">chmod -R 777 <?= htmlspecialchars(dirname(CAPTCHA_JSON)) ?></code>
                <?php endif; ?>
            </div>

            <?php if ($message): ?>
                <div class="message">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="info-box">
                <strong>💡 Astuce :</strong> pour accepter plusieurs réponses valides,
                sépare-les avec le caractère <code>|</code>.<br>
                Exemple : <code>paris|Paris|PARIS</code>
            </div>

            <!-- Formulaire d'ajout -->
            <form method="post">
                <input type="hidden" name="action" value="add">

                <div class="form-row">
                    <div class="form-group">
                        <label for="question">Question</label>
                        <input type="text" id="question" name="question"
                               placeholder="Ex : Quelle est la capitale de la France ?"
                               required>
                    </div>
                    <div class="form-group">
                        <label for="answer">Réponse(s) attendue(s)</label>
                        <input type="text" id="answer" name="answer"
                               placeholder="Ex : paris|Paris|PARIS"
                               required>
                    </div>
                </div>

                <button type="submit" class="btn-primary">➕ Ajouter la question</button>
            </form>

            <!-- Liste des questions -->
            <h2 class="questions-title">📋 Questions existantes (<?= count($bank) ?>)</h2>

            <?php if (empty($bank)): ?>
                <p style="font-size: 14px; color: #9ca3af;">Aucune question définie pour le moment.</p>
            <?php else: ?>
                <table class="captcha-table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Question</th>
                        <th>Réponse(s)</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($bank as $i => $item): ?>
                        <tr>
                            <td><?= $i ?></td>
                            <td><?= htmlspecialchars($item['q'] ?? '') ?></td>
                            <td><?= htmlspecialchars($item['a'] ?? '') ?></td>
                            <td>
                                <?php
                                $enabled = $item['enabled'] ?? false;
                                if ($enabled):
                                    ?>
                                    <span class="badge badge-on">Activée</span>
                                <?php else: ?>
                                    <span class="badge badge-off">Désactivée</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <!-- Bouton activer/désactiver -->
                                    <form method="post" style="margin:0;">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="index" value="<?= $i ?>">
                                        <button type="submit" class="btn-small btn-toggle">
                                            <?= $enabled ? 'Désactiver' : 'Activer' ?>
                                        </button>
                                    </form>

                                    <!-- Bouton supprimer -->
                                    <form method="post" style="margin:0;"
                                          onsubmit="return confirm('Supprimer cette question ?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="index" value="<?= $i ?>">
                                        <button type="submit" class="btn-small btn-delete">
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <a href="ADMIN.php" class="back-link">← Retour à l'administration</a>
        </div>
    </div>
</main>

</body>
</html>