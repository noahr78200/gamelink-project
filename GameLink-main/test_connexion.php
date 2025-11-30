<?php
echo "<h1>🔍 Test de Configuration GameLink</h1>";
echo "<hr>";

echo "<h2>✅ Test 1 : Version PHP</h2>";
echo "Version PHP : " . phpversion();
if (version_compare(phpversion(), '7.4.0', '>=')) {
    echo " ✅ OK (>= 7.4)<br>";
} else {
    echo " ❌ ERREUR : PHP 7.4 minimum requis<br>";
}
echo "<br>";

echo "<h2>✅ Test 2 : Extensions PHP</h2>";
$required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'session'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext : Installé<br>";
    } else {
        echo "❌ $ext : MANQUANT<br>";
    }
}
echo "<br>";

echo "<h2>✅ Test 3 : Connexion Base de Données</h2>";
try {
    require_once __DIR__ . '/DATA/DBConfig.php';
    echo "✅ Connexion réussie à la base de données 'gamelink'<br>";
    
    $tables = ['joueur', 'jeu', 'genre', 'plateforme', 'playlist', 'communaute'];
    echo "<br><strong>Tables vérifiées :</strong><br>";
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Table '$table' existe<br>";
        } else {
            echo "❌ Table '$table' MANQUANTE<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ ERREUR de connexion : " . $e->getMessage() . "<br>";
    echo "<br><strong>⚠️ Actions à faire :</strong><br>";
    echo "1. Vérifiez DATA/DBConfig.php<br>";
    echo "2. Vérifiez que MariaDB est démarré<br>";
    echo "3. Exécutez le fichier DATA/gamelink_schema_mariadb.sql<br>";
}
echo "<br>";

echo "<h2>✅ Test 4 : Fichiers importants</h2>";
$files = [
    'index.php' => 'Page d\'accueil publique',
    'PAGE/AUTH.php' => 'Page connexion/inscription',
    'PAGE/auth_login.php' => 'Traitement connexion',
    'PAGE/auth_register.php' => 'Traitement inscription',
    'PAGE/captcha.php' => 'Système captcha',
    'PAGE/ACCUEIL.php' => 'Page accueil connecté',
    'DATA/DBConfig.php' => 'Configuration BDD',
    '.htaccess' => 'Configuration Apache'
];

foreach ($files as $file => $desc) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✅ $file : OK ($desc)<br>";
    } else {
        echo "❌ $file : MANQUANT ($desc)<br>";
    }
}
echo "<br>";

echo "<h2>✅ Test 5 : Permissions fichiers</h2>";
$writable_files = ['PAGE/captcha_bank.json'];
foreach ($writable_files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        if (is_writable($path)) {
            echo "✅ $file : Écriture OK<br>";
        } else {
            echo "⚠️ $file : PAS d'écriture (chmod 666 requis)<br>";
        }
    } else {
        echo "❌ $file : Fichier MANQUANT<br>";
    }
}
echo "<br>";

echo "<h2>✅ Test 6 : Configuration PHP</h2>";
echo "upload_max_filesize : " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size : " . ini_get('post_max_size') . "<br>";
echo "max_execution_time : " . ini_get('max_execution_time') . "s<br>";
echo "display_errors : " . (ini_get('display_errors') ? 'ON ⚠️' : 'OFF ✅') . "<br>";
echo "session.cookie_httponly : " . (ini_get('session.cookie_httponly') ? 'ON ✅' : 'OFF ⚠️') . "<br>";
echo "<br>";

echo "<hr>";
echo "<h2>🎯 Résumé</h2>";
echo "<p>Si tous les tests sont ✅, votre installation est prête !</p>";
echo "<p><strong>Prochaines étapes :</strong></p>";
echo "<ol>";
echo "<li>Supprimez ce fichier test_connexion.php pour la sécurité</li>";
echo "<li>Accédez à <a href='index.php'>index.php</a> pour voir la page d'accueil</li>";
echo "<li>Créez un compte de test via <a href='PAGE/AUTH.php?tab=signup'>Inscription</a></li>";
echo "</ol>";

?>
<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 900px;
        margin: 50px auto;
        padding: 20px;
        background: #f5f5f5;
    }
    h1 {
        color: #667eea;
        border-bottom: 3px solid #667eea;
        padding-bottom: 10px;
    }
    h2 {
        color: #333;
        margin-top: 30px;
    }
    hr {
        border: none;
        border-top: 2px solid #ddd;
        margin: 30px 0;
    }
</style>