<?php
/**
 * 🔧 SCRIPT DE RÉPARATION AUTOMATIQUE DES PERMISSIONS
 * 
 * Ce script va :
 * 1. Créer le dossier DATA s'il n'existe pas
 * 2. Changer les permissions automatiquement
 * 3. Créer le fichier captcha_bank.json
 * 4. Tester que tout fonctionne
 * 
 * UTILISATION :
 * 1. Mets ce fichier à la racine de ton projet
 * 2. Va sur : http://localhost/ton-projet/fixer_permissions.php
 * 3. Clique sur "Réparer maintenant"
 */

$dataDir = __DIR__ . '/DATA';
$captchaFile = $dataDir . '/captcha_bank.json';
$logs = [];
$allGood = false;

// Questions par défaut
$defaultQuestions = [
    ['q' => 'Quelle est la capitale de la France ?', 'a' => 'paris|Paris|PARIS', 'enabled' => true],
    ['q' => 'Langue officielle du Brésil ?', 'a' => 'portugais|Portugais', 'enabled' => true],
    ['q' => 'Capitale de l\'Espagne ?', 'a' => 'madrid|Madrid|MADRID', 'enabled' => true]
];

function addLog($emoji, $message, $isError = false) {
    global $logs;
    $logs[] = [
        'emoji' => $emoji,
        'message' => $message,
        'error' => $isError
    ];
}

// Si on clique sur "Réparer"
if (isset($_POST['fix'])) {
    addLog('🚀', 'Démarrage de la réparation...');
    
    // Étape 1 : Créer le dossier DATA
    if (!is_dir($dataDir)) {
        if (@mkdir($dataDir, 0777, true)) {
            addLog('✅', 'Dossier DATA créé avec succès !');
        } else {
            addLog('❌', 'Impossible de créer le dossier DATA automatiquement. Tu devras le créer manuellement.', true);
        }
    } else {
        addLog('ℹ️', 'Le dossier DATA existe déjà.');
    }
    
    // Étape 2 : Changer les permissions du dossier (essayer plusieurs méthodes)
    if (is_dir($dataDir)) {
        $permFixed = false;
        
        // Méthode 1 : chmod 777
        if (@chmod($dataDir, 0777)) {
            addLog('✅', 'Permissions du dossier DATA changées en 777 (lecture + écriture + exécution pour tous)');
            $permFixed = true;
        }
        
        // Méthode 2 : chown (uniquement sur Linux/Mac)
        if (!$permFixed && function_exists('posix_getpwuid')) {
            $processUser = posix_getpwuid(posix_geteuid());
            if (@chown($dataDir, $processUser['name'])) {
                addLog('✅', 'Propriétaire du dossier changé');
                $permFixed = true;
            }
        }
        
        if (!$permFixed) {
            addLog('⚠️', 'Impossible de changer les permissions automatiquement. Tu devras le faire manuellement avec : chmod -R 777 DATA', true);
        }
    }
    
    // Étape 3 : Créer le fichier JSON
    if (is_dir($dataDir)) {
        $jsonContent = json_encode($defaultQuestions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if (@file_put_contents($captchaFile, $jsonContent)) {
            addLog('✅', 'Fichier captcha_bank.json créé avec ' . count($defaultQuestions) . ' questions !');
            
            // Changer les permissions du fichier
            if (@chmod($captchaFile, 0666)) {
                addLog('✅', 'Permissions du fichier configurées (666 = lecture + écriture pour tous)');
            }
        } else {
            addLog('❌', 'Impossible de créer le fichier captcha_bank.json. Vérifie les permissions du dossier DATA.', true);
        }
    }
    
    // Étape 4 : Tester l'écriture
    if (file_exists($captchaFile)) {
        $testContent = @file_get_contents($captchaFile);
        if ($testContent !== false) {
            addLog('✅', 'Test de lecture : OK !');
            
            // Test d'écriture
            $testData = json_decode($testContent, true);
            if (is_array($testData)) {
                if (@file_put_contents($captchaFile, json_encode($testData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
                    addLog('✅', 'Test d\'écriture : OK !');
                    addLog('🎉', 'TOUT FONCTIONNE ! Tu peux maintenant gérer tes captchas sans problème !');
                    $allGood = true;
                } else {
                    addLog('❌', 'Test d\'écriture : ÉCHEC. Le fichier existe mais n\'est pas accessible en écriture.', true);
                }
            }
        } else {
            addLog('❌', 'Impossible de lire le fichier captcha_bank.json', true);
        }
    }
}

// État actuel
$dirExists = is_dir($dataDir);
$dirWritable = $dirExists && is_writable($dataDir);
$fileExists = file_exists($captchaFile);
$fileWritable = $fileExists && is_writable($captchaFile);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔧 Réparation Permissions - GameLink</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 800px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        h1 {
            color: #333;
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }
        
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .status-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #6c757d;
        }
        
        .status-card.good {
            border-left-color: #28a745;
            background: #d4edda;
        }
        
        .status-card.bad {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        
        .status-label {
            font-size: 13px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .status-value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        
        .logs-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .log-item {
            padding: 10px;
            margin-bottom: 8px;
            border-radius: 8px;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .log-item.success {
            background: #d4edda;
            color: #155724;
        }
        
        .log-item.error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .log-item.info {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .log-emoji {
            font-size: 20px;
        }
        
        .btn {
            padding: 15px 40px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #00b09b 0%, #96c93d 100%);
            color: white;
        }
        
        .success-banner {
            background: linear-gradient(135deg, #00b09b 0%, #96c93d 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 25px;
        }
        
        .success-banner h2 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .link-button {
            display: inline-block;
            background: white;
            color: #667eea;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 15px;
            transition: transform 0.2s;
        }
        
        .link-button:hover {
            transform: scale(1.05);
        }
        
        .command-box {
            background: #2d3748;
            color: #48bb78;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            margin-top: 15px;
            overflow-x: auto;
        }
        
        .help-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 20px;
            margin-top: 25px;
        }
        
        .help-title {
            font-weight: bold;
            color: #856404;
            margin-bottom: 10px;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Réparation des permissions</h1>
        <p class="subtitle">Cet outil va corriger automatiquement les problèmes de permissions du système de captcha</p>
        
        <?php if ($allGood): ?>
            <div class="success-banner">
                <h2>🎉 Réparation réussie !</h2>
                <p>Tout fonctionne parfaitement ! Tu peux maintenant gérer tes captchas.</p>
                <a href="PAGE/ADMIN.php?tab=captcha" class="link-button">
                    Aller à la gestion du captcha →
                </a>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($logs)): ?>
            <div class="logs-box">
                <strong style="display: block; margin-bottom: 15px; font-size: 16px;">📋 Journal de réparation :</strong>
                <?php foreach ($logs as $log): ?>
                    <div class="log-item <?= $log['error'] ? 'error' : ($log['emoji'] === '✅' ? 'success' : 'info') ?>">
                        <span class="log-emoji"><?= $log['emoji'] ?></span>
                        <span><?= htmlspecialchars($log['message']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="status-grid">
            <div class="status-card <?= $dirExists ? 'good' : 'bad' ?>">
                <div class="status-label">Dossier DATA</div>
                <div class="status-value"><?= $dirExists ? '✅ Existe' : '❌ Manquant' ?></div>
            </div>
            
            <div class="status-card <?= $dirWritable ? 'good' : 'bad' ?>">
                <div class="status-label">Écriture dossier</div>
                <div class="status-value"><?= $dirWritable ? '✅ OK' : '❌ Bloqué' ?></div>
            </div>
            
            <div class="status-card <?= $fileExists ? 'good' : 'bad' ?>">
                <div class="status-label">Fichier JSON</div>
                <div class="status-value"><?= $fileExists ? '✅ Existe' : '❌ Manquant' ?></div>
            </div>
            
            <div class="status-card <?= $fileWritable ? 'good' : 'bad' ?>">
                <div class="status-label">Écriture fichier</div>
                <div class="status-value"><?= $fileWritable ? '✅ OK' : '❌ Bloqué' ?></div>
            </div>
        </div>
        
        <?php if (!$allGood): ?>
            <form method="POST">
                <button type="submit" name="fix" class="btn btn-primary">
                    🚀 Réparer maintenant
                </button>
            </form>
        <?php else: ?>
            <a href="PAGE/ADMIN.php?tab=captcha" style="text-decoration: none;">
                <button class="btn btn-success">
                    ✅ Aller gérer les captchas
                </button>
            </a>
        <?php endif; ?>
        
        <?php if (!$dirWritable && !$allGood): ?>
            <div class="help-box">
                <div class="help-title">🆘 Besoin d'aide manuelle ?</div>
                <p style="margin-bottom: 10px;">Si la réparation automatique ne fonctionne pas, tu peux régler les permissions manuellement :</p>
                
                <strong>Sur Mac/Linux, ouvre le Terminal et tape :</strong>
                <div class="command-box">chmod -R 777 <?= htmlspecialchars($dataDir) ?></div>
                
                <p style="margin-top: 15px;"><strong>Sur Windows :</strong></p>
                <ol style="margin-left: 20px; color: #856404;">
                    <li>Clic droit sur le dossier DATA</li>
                    <li>Propriétés → Sécurité</li>
                    <li>Modifier → Cocher toutes les cases "Autoriser"</li>
                    <li>Appliquer → OK</li>
                </ol>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>