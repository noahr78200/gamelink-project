<?php
// AUTH.php
session_start();



// Quel onglet afficher par défaut ? (ex: AUTH.php?tab=signup)
$tab = $_GET['tab'] ?? 'login';

// Flash messages (ex: définis par auth_login.php puis redirection)
$flash = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);

// CSRF simple (on le vérifiera côté handlers plus tard)
if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(32)); }
$csrf = $_SESSION['csrf'];
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <title>Connexion | GameLink</title>
  <link rel="stylesheet" href="../CSS/HEADER.css" />
  <link rel="stylesheet" href="../CSS/AUTH.css" />
</head>
<body class="auth-body">
  <header class="auth-header">
    <nav class="Menu">
      <a href="../index.php"><img class="logo" src="../ICON/LogoComplet.svg" alt="Logo GameLink"></a>
    </nav>
  </header>

  <main class="auth-main">
    <section class="auth-card">

      <!-- Onglets -->
      <div class="auth-tabs" role="tablist">
        <button class="auth-tab <?= $tab==='login' ? 'is-active' : '' ?>" data-target="#login" aria-selected="<?= $tab==='login'?'true':'false' ?>">Connexion</button>
        <button class="auth-tab <?= $tab==='signup' ? 'is-active' : '' ?>" data-target="#signup" aria-selected="<?= $tab==='signup'?'true':'false' ?>">Inscription</button>
      </div>

      <!-- Connexion -->
      <form id="login" class="auth-form <?= $tab==='login' ? 'is-active' : '' ?>" method="post" action="../INCLUDE/auth_login.php" novalidate>
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
        <div class="field">
          <label for="loginEmail">Email</label>
          <input id="loginEmail" name="email" type="email" required placeholder="exemple@mail.com" value="<?= htmlspecialchars($flash['old']['email'] ?? '') ?>">
          <p class="error" data-for="loginEmail"><?= $flash['errors']['loginEmail'] ?? '' ?></p>
        </div>
        <div class="field">
          <label for="loginPassword">Mot de passe</label>
          <div class="password-wrap">
            <input id="loginPassword" name="password" type="password" required minlength="6" placeholder="•••••••">
            <button type="button" class="toggle-visibility" aria-label="Afficher/masquer le mot de passe">👁️</button>
          </div>
          <p class="error" data-for="loginPassword"><?= $flash['errors']['loginPassword'] ?? '' ?></p>
        </div>
        <button type="submit" class="btn primary full">Se connecter</button>
      </form>

      <!-- Inscription -->
      <form id="signup" class="auth-form <?= $tab==='signup' ? 'is-active' : '' ?>" method="post" action="../INCLUDE/auth_register.php" novalidate>
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
        <div class="field">
          <label for="suName">Pseudo</label>
          <input id="suName" name="name" type="text" required minlength="3" placeholder="Votre pseudo" value="<?= htmlspecialchars($flash['old']['name'] ?? '') ?>">
          <p class="error" data-for="suName"><?= $flash['errors']['suName'] ?? '' ?></p>
        </div>
        <div class="field">
          <label for="suEmail">Email</label>
          <input id="suEmail" name="email" type="email" required placeholder="exemple@mail.com" value="<?= htmlspecialchars($flash['old']['email'] ?? '') ?>">
          <p class="error" data-for="suEmail"><?= $flash['errors']['suEmail'] ?? '' ?></p>
        </div>
        <div class="field">
          <label for="suPass">Mot de passe</label>
          <div class="password-wrap">
            <input id="suPass" name="password" type="password" required minlength="6" placeholder="Minimum 6 caractères">
            <button type="button" class="toggle-visibility" aria-label="Afficher/masquer le mot de passe">👁️</button>
          </div>
          <p class="error" data-for="suPass"><?= $flash['errors']['suPass'] ?? '' ?></p>
        </div>
        <div class="field">
          <label for="suConfirm">Confirmer le mot de passe</label>
          <div class="password-wrap">
            <input id="suConfirm" name="password_confirm" type="password" required minlength="6" placeholder="Répétez le mot de passe">
            <button type="button" class="toggle-visibility" aria-label="Afficher/masquer le mot de passe">👁️</button>
          </div>
          <p class="error" data-for="suConfirm"><?= $flash['errors']['suConfirm'] ?? '' ?></p>
        </div>
        <label class="terms"><input type="checkbox" name="cgu" required> J’accepte les CGU</label>
        <button type="submit" class="btn primary full">Créer le compte</button>
      </form>

    </section>
  </main>

  <script src="../JS/AUTH.js" defer></script>
</body>
</html>
