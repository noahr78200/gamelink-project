<?php
session_start();

require_once __DIR__ . '/../INCLUDES/track.php';
require_once __DIR__ . '/../DATA/DBConfig.php';
require_once __DIR__ . '/../INCLUDES/avatar_svg.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT pseudo, email, bio, avatar_config FROM joueur WHERE id_joueur = ?");$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    die("Utilisateur non trouvé");
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="description"content="Profil GameLink">
        <title>Profil | GameLink</title>
        <link rel="stylesheet" href="../CSS/HEADER.css" type="text/css"/>
        <link rel="stylesheet" href="../CSS/STYLE_PROFIL.css" type="text/css"/>
        <link rel="icon" type="image/png" sizes="32x32" href="../ICON/LogoSimple.svg">
    </head>
    <body>
    <?php 
    
    include __DIR__ . '/../INCLUDES/header.php'; 
    ?>
        <main>
            <section class="profile-card">
                <a href="avatar.php" class="edit-btn">
                    <img src="../ICON/edit.svg" alt="Logo edit" width="30px">
                </a>
                <div class="profile-info">
                <?php echo generateAvatarSVG($user['avatar_config'], 100); ?>
                <div class="name-bio">
                    <h2><?= htmlspecialchars($user['pseudo']) ?></h2>
                    <p><?= htmlspecialchars($user['bio']) ?></p>
                </div>
                </div>

                <div class="stats">
                    <div class="stat">
                        <span>123</span>
                        <small>Jeux joués</small>
                    </div>
                    <div class="stat">
                        <span>1,255</span>
                        <small>Abonnements</small>
                    </div>
                    <div class="stat">
                        <span>653</span>
                        <small>Abonnés</small>
                    </div>
                    <div class="stat">
                        <span>60</span>
                        <small>Posts</small>
                    </div>
                </div>
            </section>

            <section class="profile">
                <div class="tabs">
                    <button class="tab active" data-tab="act"><h2>Activité</h2></button>
                    <button class="tab" data-tab="set"><h2>Paramètres</h2></button>
                </div>

                <section id="act" class="box active">
                    <div>
                        <h2>Mes Jeux Notés :</h2>
                        <div class="favorites-grid">
                            <?php
                            // On récupère les jeux que l'utilisateur a notés
                            $stmt_jeux_notes = $pdo->prepare("SELECT j.id_jeu, j.titre, j.cover_url, a.valeur, a.date_notation
                                FROM avis a
                                JOIN jeu j ON a.id_jeu = j.id_jeu
                                WHERE a.id_joueur = ? AND a.valeur IS NOT NULL
                                ORDER BY a.date_notation DESC
                                LIMIT 15");
                            $stmt_jeux_notes->execute([$user_id]);
                            $jeux_notes = $stmt_jeux_notes->fetchAll();
                            
                            if (empty($jeux_notes)): ?>
                                <p class="no-favorites">Aucun jeu noté pour le moment. Notez des jeux pour les voir apparaître ici !</p>
                            <?php else: ?>
                                <?php foreach ($jeux_notes as $jeu): ?>
                                    <div class="game-card">
                                        <a href="game.php?id=<?= $jeu['id_jeu'] ?>">
                                            <img src="<?= htmlspecialchars($jeu['cover_url']) ?>" 
                                                 alt="<?= htmlspecialchars($jeu['titre']) ?>"
                                                 onerror="this.src='../ICON/placeholder.jpg'">
                                            <p><?= htmlspecialchars($jeu['titre']) ?></p>
                                            <div class="rating-stars">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <span class="star <?= $i <= $jeu['valeur'] ? 'filled' : '' ?>">★</span>
                                                <?php endfor; ?>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
                <section id="set" class="box">
                    <h2>Paramètres du compte</h2>

                    <form action="../INCLUDES/update_email.php" method="POST" class="form">
                        <label for="email">Nouvel e-mail</label>
                        <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>

                        <button type="submit" class="btn">Modifier l'e-mail</button>
                    </form>

                    <form action="../INCLUDES/update_mdp.php" method="POST" class="form">
                        <label for="old_mdp">Ancien mot de passe</label>
                        <input type="password" name="old_mdp" id="old_mdp" required>
                        <br>
                        <label for="new_mdp">Nouveau mot de passe</label>
                        <input type="password" name="new_mdp" id="new_mdp" required>

                        <button type="submit" class="btn red">Modifier le mot de passe</button>
                    </form>

                    <h2>Apparence</h2>
                    <button id="themeToggle" class="btn">Mode Sombre / Mode Clair</button>
                </section>

            </section>
        </main>
        <script src="../JS/PROFIL.js"></script>
    </body>
</html>