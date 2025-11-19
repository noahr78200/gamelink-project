<?php
// PAGE/TEST_API.php
// Script de diagnostic pour tester les fichiers PHP

session_start();

// Simuler une connexion (POUR TEST SEULEMENT)
if (!isset($_SESSION['user_id'])) {
    // Recuperer le premier utilisateur de la BDD
    require_once __DIR__ . '/../DATA/DBConfig.php';
    $stmt = $pdo->query("SELECT id_joueur, pseudo FROM joueur LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $_SESSION['user_id'] = $user['id_joueur'];
        $_SESSION['user_pseudo'] = $user['pseudo'];
        echo "<p style='color: orange;'>Session simulee : {$user['pseudo']}</p>";
    } else {
        die("<p style='color: red;'>Aucun utilisateur dans la BDD !</p>");
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Test API Communaute</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 20px; margin: 20px 0; border-radius: 10px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        button { padding: 10px 20px; margin: 5px; cursor: pointer; }
        pre { background: #333; color: #0f0; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .result { margin-top: 10px; }
    </style>
</head>
<body>

<h1>🔍 TEST API COMMUNAUTE</h1>

<div class="box">
    <h2>📊 Informations Session</h2>
    <p><strong>User ID :</strong> <?= $_SESSION['user_id'] ?? 'NON DEFINI' ?></p>
    <p><strong>Pseudo :</strong> <?= $_SESSION['user_pseudo'] ?? 'NON DEFINI' ?></p>
</div>

<div class="box">
    <h2>🧪 Test 1 : Rejoindre un groupe</h2>
    <p>Teste le fichier <code>groupe_join.php</code></p>
    <button onclick="testRejoindre()">Tester</button>
    <div id="result-rejoindre" class="result"></div>
</div>

<div class="box">
    <h2>🧪 Test 2 : Quitter un groupe</h2>
    <p>Teste le fichier <code>groupe_leave.php</code></p>
    <button onclick="testQuitter()">Tester</button>
    <div id="result-quitter" class="result"></div>
</div>

<div class="box">
    <h2>🧪 Test 3 : Recuperer les messages</h2>
    <p>Teste le fichier <code>group_messages.php</code></p>
    <button onclick="testMessages()">Tester</button>
    <div id="result-messages" class="result"></div>
</div>

<div class="box">
    <h2>🧪 Test 4 : Envoyer un message</h2>
    <p>Teste le fichier <code>group_message.php</code></p>
    <button onclick="testEnvoyer()">Tester</button>
    <div id="result-envoyer" class="result"></div>
</div>

<div class="box">
    <h2>📄 Verification des fichiers</h2>
    <?php
    $fichiers = [
        '/INCLUDES/groupe_join.php',
        '/INCLUDES/groupe_leave.php',
        '/INCLUDES/group_messages.php',
        '/INCLUDES/group_message.php'
    ];
    
    foreach ($fichiers as $fichier) {
        $chemin = __DIR__ . '/..' . $fichier;
        if (file_exists($chemin)) {
            $taille = filesize($chemin);
            $perms = substr(sprintf('%o', fileperms($chemin)), -4);
            echo "<p class='success'>✅ $fichier existe ($taille octets, perms: $perms)</p>";
        } else {
            echo "<p class='error'>❌ $fichier MANQUANT !</p>";
        }
    }
    ?>
</div>

<div class="box">
    <h2>🗄️ Verification base de donnees</h2>
    <?php
    require_once __DIR__ . '/../DATA/DBConfig.php';
    
    try {
        // Compter les groupes
        $stmt = $pdo->query("SELECT COUNT(*) as nb FROM communaute");
        $nb_groupes = $stmt->fetch()['nb'];
        echo "<p class='success'>✅ $nb_groupes groupes dans la BDD</p>";
        
        // Compter les adhesions
        $stmt = $pdo->query("SELECT COUNT(*) as nb FROM adhesion");
        $nb_adhesions = $stmt->fetch()['nb'];
        echo "<p class='success'>✅ $nb_adhesions adhesions dans la BDD</p>";
        
        // Compter les publications
        $stmt = $pdo->query("SELECT COUNT(*) as nb FROM publication");
        $nb_publications = $stmt->fetch()['nb'];
        echo "<p class='success'>✅ $nb_publications messages dans la BDD</p>";
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erreur BDD : " . $e->getMessage() . "</p>";
    }
    ?>
</div>

<script>
function afficherResultat(elementId, data, erreur) {
    const div = document.getElementById(elementId);
    
    if (erreur) {
        div.innerHTML = '<p class="error">❌ ERREUR : ' + erreur + '</p>';
    } else {
        div.innerHTML = '<p class="success">✅ SUCCES</p><pre>' + JSON.stringify(data, null, 2) + '</pre>';
    }
}

function testRejoindre() {
    console.log('Test rejoindre groupe');
    
    fetch('/INCLUDES/groupe_join.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'groupe_id=1'
    })
    .then(response => {
        console.log('Status:', response.status);
        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }
        return response.text();
    })
    .then(text => {
        console.log('Reponse brute:', text);
        try {
            const data = JSON.parse(text);
            afficherResultat('result-rejoindre', data);
        } catch (e) {
            afficherResultat('result-rejoindre', null, 'JSON invalide : ' + text);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        afficherResultat('result-rejoindre', null, error.message);
    });
}

function testQuitter() {
    console.log('Test quitter groupe');
    
    fetch('/INCLUDES/groupe_leave.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'groupe_id=1'
    })
    .then(response => {
        console.log('Status:', response.status);
        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }
        return response.text();
    })
    .then(text => {
        console.log('Reponse brute:', text);
        try {
            const data = JSON.parse(text);
            afficherResultat('result-quitter', data);
        } catch (e) {
            afficherResultat('result-quitter', null, 'JSON invalide : ' + text);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        afficherResultat('result-quitter', null, error.message);
    });
}

function testMessages() {
    console.log('Test recuperer messages');
    
    fetch('/INCLUDES/group_messages.php?groupe_id=1')
    .then(response => {
        console.log('Status:', response.status);
        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }
        return response.text();
    })
    .then(text => {
        console.log('Reponse brute:', text);
        try {
            const data = JSON.parse(text);
            afficherResultat('result-messages', data);
        } catch (e) {
            afficherResultat('result-messages', null, 'JSON invalide : ' + text);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        afficherResultat('result-messages', null, error.message);
    });
}

function testEnvoyer() {
    console.log('Test envoyer message');
    
    fetch('/INCLUDES/group_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'groupe_id=1&message=Message de test'
    })
    .then(response => {
        console.log('Status:', response.status);
        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }
        return response.text();
    })
    .then(text => {
        console.log('Reponse brute:', text);
        try {
            const data = JSON.parse(text);
            afficherResultat('result-envoyer', data);
        } catch (e) {
            afficherResultat('result-envoyer', null, 'JSON invalide : ' + text);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        afficherResultat('result-envoyer', null, error.message);
    });
}
</script>

</body>
</html>