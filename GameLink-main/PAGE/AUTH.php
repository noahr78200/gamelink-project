<?php
// PAGE/AUTH.php
session_start();

$flash = $_SESSION['flash'] ?? ['errors'=>[], 'old'=>[]];
unset($_SESSION['flash']);

if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

/* ► Détecte le chemin web du site (ex: /gamelink/GameLink-main) depuis /PAGE/AUTH.php */
$script    = $_SERVER['SCRIPT_NAME'];               // ex: /gamelink/GameLink-main/PAGE/AUTH.php
$pageDir   = rtrim(dirname($script), '/');          // ex: /gamelink/GameLink-main/PAGE
$siteDir   = rtrim(dirname($pageDir), '/');         // ex: /gamelink/GameLink-main
$loginURL  = $siteDir . '/INCLUDES/auth_login.php';
$signupURL = $siteDir . '/INCLUDES/auth_register.php';

/* ---------------- Flash (erreurs + anciens champs) ---------------- */
$flash = $_SESSION['flash'] ?? ['errors' => [], 'old' => []];
unset($_SESSION['flash']);

/* ---------------- CSRF simple (optionnel mais conseillé) ----------- */
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

// Helper pour afficher proprement une valeur précédente
function old($key, $default = '') {
  global $flash;
  return htmlspecialchars($flash['old'][$key] ?? $default, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <title>GameLink – Connexion / Inscription</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    :root {
      --bg:#0e0f12; --card:#151922; --muted:#99a1b3; --txt:#e9ecf1; --primary:#6ea8fe; --danger:#ff6b6b;
      --radius:14px;
    }
    * { box-sizing: border-box; }
    body { margin:0; font-family: system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji"; background:var(--bg); color:var(--txt); }
    a { color:var(--primary); text-decoration: none; }
    header { display:flex; align-items:center; justify-content:space-between; padding:16px 20px; background:#0b0d10; border-bottom:1px solid #222833; position:sticky; top:0; }
    header .brand { display:flex; gap:12px; align-items:center; }
    header .brand img { height:32px; }
    header .brand span { font-weight:700; letter-spacing:.3px; }
    main { max-width:980px; margin:40px auto; padding:0 16px; display:grid; grid-template-columns: 1fr; gap:24px; }
    .card { background:var(--card); border:1px solid #222833; border-radius:var(--radius); padding:24px; }
    .title { font-size:1.4rem; margin:0 0 14px; }
    .muted { color:var(--muted); font-size:.95rem; }

    /* Onglets */
    .tabbar { display:flex; gap:10px; margin:12px 0 18px; }
    .auth-tab { padding:10px 14px; border-radius:10px; border:1px solid #2a3140; background:#10141c; cursor:pointer; color:#cfd6e4; }
    .auth-tab.is-active { background:#172033; border-color:#2f3a51; color:#fff; }

    /* Form */
    .auth-form { display:none; }
    .auth-form.is-active { display:block; }
    .field { margin:14px 0; }
    label { display:block; margin-bottom:6px; color:#d9deea; font-size:.95rem; }
    .password-wrap { display:flex; gap:6px; align-items:stretch; }
    input[type="text"], input[type="email"], input[type="password"] {
      width:100%; padding:12px 12px; border-radius:10px; border:1px solid #2a3140; background:#0f131a; color:#e9ecf1;
      outline:none;
    }
    input::placeholder { color:#7d889c; }
    .toggle-visibility { padding:0 12px; border-radius:10px; border:1px solid #2a3140; background:#0f131a; color:#cfd6e4; cursor:pointer; }
    .btn-primary { margin-top:10px; width:100%; padding:12px 14px; border-radius:12px; border:1px solid #3a65d0; background:#1e3d85; color:#fff; font-weight:600; cursor:pointer; }
    .btn-primary:disabled { opacity:.7; cursor:default; }
    .error { color:var(--danger); font-size:.9rem; min-height:1.1em; margin:6px 2px 0; }

    .grid { display:grid; grid-template-columns: 1fr 1fr; gap:24px; }
    @media (max-width: 900px) { .grid { grid-template-columns: 1fr; } }
    .note { margin-top:8px; }
  </style>
</head>
<body>
<header>
  <div class="brand">
    <a href="../index.php"><img src="../ICON/LogoComplet.svg" alt="GameLink"></a>
    <span>GameLink</span>
  </div>
  <nav>
    <a href="../index.php">Accueil</a>
  </nav>
</header>

<main>
  <section class="card">
    <h1 class="title">Bienvenue 👋</h1>
    <p class="muted">Connecte-toi ou crée un compte pour accéder à GameLink. Le captcha sera demandé après une inscription ou une connexion réussie.</p>
  </section>

  <section class="grid">
    <!-- ==================== CONNEXION ==================== -->
    <div class="card">
      <div class="tabbar">
        <button class="auth-tab is-active" data-target="#loginForm">Connexion</button>
        <button class="auth-tab" data-target="#signupForm">Inscription</button>
      </div>

      <form id="loginForm"  class="auth-form is-active" action="<?= htmlspecialchars($loginURL) ?>"  method="post" novalidate>
        <h2 class="title" style="margin-top:0;">Connexion</h2>

        <div class="field">
          <label for="loginEmail">Email</label>
          <input id="loginEmail" name="email" type="email" required value="<?= old('email') ?>">
          <p class="error" data-for="loginEmail"><?= $flash['errors']['loginEmail'] ?? '' ?></p>
        </div>

        <div class="field">
          <label for="loginPassword">Mot de passe</label>
          <div class="password-wrap">
            <input id="loginPassword" name="password" type="password" required minlength="6">
            <button type="button" class="toggle-visibility" aria-label="Afficher/masquer">👁️</button>
          </div>
          <p class="error" data-for="loginPassword"><?= $flash['errors']['loginPassword'] ?? '' ?></p>
        </div>

        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

        <button type="submit" class="btn-primary">Se connecter</button>
        <p class="error note"><?= $flash['errors']['general'] ?? '' ?></p>
      </form>

      <!-- ==================== INSCRIPTION ==================== -->
      <form id="signupForm" class="auth-form"            action="<?= htmlspecialchars($signupURL) ?>" method="post" novalidate>
        <h2 class="title" style="margin-top:0;">Créer un compte</h2>

        <div class="field">
          <label for="suName">Pseudo</label>
          <input id="suName" name="name" type="text" required minlength="3" value="<?= old('name') ?>">
          <p class="error" data-for="suName"><?= $flash['errors']['suName'] ?? '' ?></p>
        </div>

        <div class="field">
          <label for="suEmail">Email</label>
          <input id="suEmail" name="email" type="email" required value="<?= old('email') ?>">
          <p class="error" data-for="suEmail"><?= $flash['errors']['suEmail'] ?? '' ?></p>
        </div>

        <div class="field">
          <label for="suPass">Mot de passe</label>
          <div class="password-wrap">
            <input id="suPass" name="password" type="password" required minlength="6" placeholder="Minimum 6 caractères">
            <button type="button" class="toggle-visibility" aria-label="Afficher/masquer">👁️</button>
          </div>
          <p class="error" data-for="suPass"><?= $flash['errors']['suPass'] ?? '' ?></p>
        </div>

        <div class="field">
          <label for="suConfirm">Confirmer le mot de passe</label>
          <div class="password-wrap">
            <!-- IMPORTANT : name="confirm" pour matcher le PHP -->
            <input id="suConfirm" name="confirm" type="password" required minlength="6" placeholder="Répétez le mot de passe">
            <button type="button" class="toggle-visibility" aria-label="Afficher/masquer">👁️</button>
          </div>
          <p class="error" data-for="suConfirm"><?= $flash['errors']['suConfirm'] ?? '' ?></p>
        </div>

        <div class="field">
          <label><input id="suCgu" type="checkbox" required> J’accepte les CGU</label>
        </div>

        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

        <button type="submit" class="btn-primary">Créer le compte</button>
        <p class="error note"><?= $flash['errors']['general'] ?? '' ?></p>
      </form>
    </div>

    <!-- Panneau d’info latéral (facultatif) -->
    <div class="card">
      <h3 class="title">Conseils</h3>
      <ul class="muted">
        <li>Utilise un email valide (ex : nom@domaine.com).</li>
        <li>Mot de passe : au moins 6 caractères.</li>
        <li>Après connexion/inscription réussie, tu seras redirigé vers un <strong>captcha</strong>, puis vers l’accueil.</li>
      </ul>
    </div>
  </section>
</main>

<!-- JS onglets + visibilité + submit non bloqué -->
<script>
// Mini-logiciel d’onglets
document.addEventListener('DOMContentLoaded', () => {
  const tabs  = document.querySelectorAll('.auth-tab');
  const forms = document.querySelectorAll('.auth-form');
  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('is-active'));
      forms.forEach(f => f.classList.remove('is-active'));
      tab.classList.add('is-active');
      const target = document.querySelector(tab.dataset.target);
      if (target) target.classList.add('is-active');
    });
  });

  // Afficher/masquer mots de passe
  document.querySelectorAll('.toggle-visibility').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = btn.previousElementSibling;
      if (!input) return;
      input.type = input.type === 'password' ? 'text' : 'password';
    });
  });

  // Validation légère côté client (NE BLOQUE PAS si tout est OK)
  function setError(id, msg){
    const el = document.querySelector(`.error[data-for="${id}"]`);
    if (el) el.textContent = msg || '';
  }
  function lockSubmit(form){
    const btn = form.querySelector('button[type="submit"], input[type="submit"]');
    if (!btn) return;
    btn.dataset._txt = btn.textContent || btn.value || '';
    if (btn.tagName === 'BUTTON') btn.textContent = 'Envoi…'; else btn.value = 'Envoi…';
    btn.disabled = true;
  }

  const loginForm = document.getElementById('loginForm');
  if (loginForm){
    loginForm.addEventListener('submit', (e) => {
      const email = document.getElementById('loginEmail');
      const pass  = document.getElementById('loginPassword');
      setError('loginEmail',''); setError('loginPassword','');

      let ok = true;
      if (!email || !email.value || !email.checkValidity()) { ok=false; setError('loginEmail','Email invalide'); }
      if (!pass || !pass.value || pass.value.length < 6)     { ok=false; setError('loginPassword','6 caractères minimum'); }

      if (!ok){ e.preventDefault(); loginForm.reportValidity?.(); return; }
      lockSubmit(loginForm); // puis LAISSE partir le POST vers ../INCLUDES/auth_login.php
    });
  }

  const signupForm = document.getElementById('signupForm');
  if (signupForm){
    signupForm.addEventListener('submit', (e) => {
      const name  = document.getElementById('suName');
      const mail  = document.getElementById('suEmail');
      const pass  = document.getElementById('suPass');
      const conf  = document.getElementById('suConfirm');
      const cgu   = document.getElementById('suCgu');

      setError('suName',''); setError('suEmail',''); setError('suPass',''); setError('suConfirm','');

      let ok = true;
      if (!name || !name.value || name.value.length < 3) { ok=false; setError('suName','3 caractères minimum'); }
      if (!mail || !mail.value || !mail.checkValidity()) { ok=false; setError('suEmail','Email invalide'); }
      if (!pass || !pass.value || pass.value.length < 6) { ok=false; setError('suPass','6 caractères minimum'); }
      if (!conf || pass.value !== conf.value)            { ok=false; setError('suConfirm','Les mots de passe ne correspondent pas'); }
      if (cgu && !cgu.checked)                           { ok=false; alert('Vous devez accepter les CGU.'); }

      if (!ok){ e.preventDefault(); signupForm.reportValidity?.(); return; }
      lockSubmit(signupForm); // puis LAISSE partir le POST vers ../INCLUDES/auth_register.php
    });
  }
});
</script>

<!-- Ton JS externe (si tu préfères le séparer) -->
<!-- <script src="../JS/AUTH.js" defer></script> -->
</body>
</html>
