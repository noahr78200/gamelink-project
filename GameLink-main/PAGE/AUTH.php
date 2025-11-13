<?php
// =====================================
// FICHIER : AUTH.php (VERSION DÉBUTANT)
// Page de connexion et inscription
// =====================================

// Démarre la session
session_start();

// 1) RÉCUPÉRER LES MESSAGES D'ERREUR (s'il y en a)
$messages_flash = $_SESSION['flash'] ?? ['errors' => [], 'old' => []];
unset($_SESSION['flash']); // Effacer après lecture

// 2) CRÉER UN TOKEN CSRF (sécurité)
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

// 3) CALCULER LES CHEMINS VERS LES SCRIPTS DE TRAITEMENT
$chemin_script = $_SERVER['SCRIPT_NAME'];
$dossier_page = dirname($chemin_script);
$dossier_site = dirname($dossier_page);
$url_login = $dossier_site . '../INCLUDES/auth_login.php';
$url_signup = $dossier_site . '../INCLUDES/auth_register.php';

// FONCTION : Récupérer une ancienne valeur du formulaire
function ancienne_valeur($nom_champ, $valeur_par_defaut = '') {
    global $messages_flash;
    $valeur = $messages_flash['old'][$nom_champ] ?? $valeur_par_defaut;
    return htmlspecialchars($valeur, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>GameLink — Connexion / Inscription</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <style>
    /* ===== STYLE GÉNÉRAL ===== */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    
    body {
      font-family: Arial, sans-serif;
      background-color: #0e0f12;
      color: #e9ecf1;
    }
    
    /* ===== EN-TÊTE ===== */
    header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 16px 20px;
      background-color: #0b0d10;
      border-bottom: 1px solid #222833;
    }
    
    header .brand {
      display: flex;
      gap: 12px;
      align-items: center;
    }
    
    header .brand img {
      height: 32px;
    }
    
    header .brand span {
      font-weight: bold;
      font-size: 18px;
    }
    
    header a {
      color: #6ea8fe;
      text-decoration: none;
    }
    
    /* ===== CONTENU PRINCIPAL ===== */
    main {
      max-width: 980px;
      margin: 40px auto;
      padding: 0 16px;
      display: grid;
      grid-template-columns: 1fr;
      gap: 24px;
    }
    
    .card {
      background-color: #151922;
      border: 1px solid #222833;
      border-radius: 14px;
      padding: 24px;
    }
    
    .title {
      font-size: 1.4rem;
      margin: 0 0 14px;
    }
    
    .muted {
      color: #99a1b3;
      font-size: 0.95rem;
    }
    
    /* ===== ONGLETS ===== */
    .tabbar {
      display: flex;
      gap: 10px;
      margin: 12px 0 18px;
    }
    
    .auth-tab {
      padding: 10px 14px;
      border-radius: 10px;
      border: 1px solid #2a3140;
      background-color: #10141c;
      cursor: pointer;
      color: #cfd6e4;
    }
    
    .auth-tab.is-active {
      background-color: #172033;
      border-color: #2f3a51;
      color: #fff;
    }
    
    /* ===== FORMULAIRES ===== */
    .auth-form {
      display: none;
    }
    
    .auth-form.is-active {
      display: block;
    }
    
    .field {
      margin: 14px 0;
    }
    
    label {
      display: block;
      margin-bottom: 6px;
      color: #d9deea;
      font-size: 0.95rem;
    }
    
    input[type="text"],
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 12px;
      border-radius: 10px;
      border: 1px solid #2a3140;
      background-color: #0f131a;
      color: #e9ecf1;
      outline: none;
      font-size: 16px;
    }
    
    input::placeholder {
      color: #7d889c;
    }
    
    .password-wrap {
      display: flex;
      gap: 6px;
      align-items: stretch;
    }
    
    .toggle-visibility {
      padding: 0 12px;
      border-radius: 10px;
      border: 1px solid #2a3140;
      background-color: #0f131a;
      color: #cfd6e4;
      cursor: pointer;
    }
    
    .btn-primary {
      margin-top: 10px;
      width: 100%;
      padding: 12px 14px;
      border-radius: 12px;
      border: 1px solid #3a65d0;
      background-color: #1e3d85;
      color: #fff;
      font-weight: 600;
      cursor: pointer;
      font-size: 16px;
    }
    
    .btn-primary:hover {
      background-color: #2a4a95;
    }
    
    .btn-primary:disabled {
      opacity: 0.7;
      cursor: default;
    }
    
    .error {
      color: #ff6b6b;
      font-size: 0.9rem;
      min-height: 1.1em;
      margin: 6px 2px 0;
    }
    
    /* ===== GRILLE ===== */
    .grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
    }
    
    @media (max-width: 900px) {
      .grid {
        grid-template-columns: 1fr;
      }
    }
    
    .note {
      margin-top: 8px;
    }
  </style>
</head>
<body>

<!-- EN-TÊTE -->
<header>
  <div class="brand">
    <a href="../index.php">
      <img src="../ICON/LogoComplet.svg" alt="GameLink">
    </a>
    <span>GameLink</span>
  </div>
  <nav>
    <a href="../index.php">Accueil</a>
  </nav>
</header>

<!-- CONTENU PRINCIPAL -->
<main>
  <!-- Carte de bienvenue -->
  <section class="card">
    <h1 class="title">Bienvenue 👋</h1>
    <p class="muted">
      Connecte-toi ou crée un compte pour accéder à GameLink. 
      Le captcha sera demandé après une inscription ou une connexion réussie.
    </p>
  </section>

  <!-- Grille avec formulaires -->
  <section class="grid">
    
    <!-- ========== CARTE AVEC FORMULAIRES ========== -->
    <div class="card">
      
      <!-- Onglets -->
      <div class="tabbar">
        <button class="auth-tab is-active" data-target="#loginForm">Connexion</button>
        <button class="auth-tab" data-target="#signupForm">Inscription</button>
      </div>

      <!-- ========== FORMULAIRE DE CONNEXION ========== -->
      <form id="loginForm" class="auth-form is-active" action="<?php echo htmlspecialchars($url_login); ?>" method="post" novalidate>
        <h2 class="title" style="margin-top:0;">Connexion</h2>

        <!-- Champ Email -->
        <div class="field">
          <label for="loginEmail">Email</label>
          <input id="loginEmail" name="email" type="email" required value="<?php echo ancienne_valeur('email'); ?>">
          <p class="error" data-for="loginEmail">
            <?php echo $messages_flash['errors']['loginEmail'] ?? ''; ?>
          </p>
        </div>

        <!-- Champ Mot de passe -->
        <div class="field">
          <label for="loginPassword">Mot de passe</label>
          <div class="password-wrap">
            <input id="loginPassword" name="password" type="password" required minlength="6">
            <button type="button" class="toggle-visibility" aria-label="Afficher/masquer">👁️</button>
          </div>
          <p class="error" data-for="loginPassword">
            <?php echo $messages_flash['errors']['loginPassword'] ?? ''; ?>
          </p>
        </div>

        <!-- Token CSRF caché -->
        <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf']; ?>">

        <!-- Bouton de soumission -->
        <button type="submit" class="btn-primary">Se connecter</button>
        
        <!-- Message d'erreur général -->
        <p class="error note">
          <?php echo $messages_flash['errors']['general'] ?? ''; ?>
        </p>
      </form>

      <!-- ========== FORMULAIRE D'INSCRIPTION ========== -->
      <form id="signupForm" class="auth-form" action="<?php echo htmlspecialchars($url_signup); ?>" method="post" novalidate>
        <h2 class="title" style="margin-top:0;">Créer un compte</h2>

        <!-- Champ Pseudo -->
        <div class="field">
          <label for="suName">Pseudo</label>
          <input id="suName" name="name" type="text" required minlength="3" value="<?php echo ancienne_valeur('name'); ?>">
          <p class="error" data-for="suName">
            <?php echo $messages_flash['errors']['suName'] ?? ''; ?>
          </p>
        </div>

        <!-- Champ Email -->
        <div class="field">
          <label for="suEmail">Email</label>
          <input id="suEmail" name="email" type="email" required value="<?php echo ancienne_valeur('email'); ?>">
          <p class="error" data-for="suEmail">
            <?php echo $messages_flash['errors']['suEmail'] ?? ''; ?>
          </p>
        </div>

        <!-- Champ Mot de passe -->
        <div class="field">
          <label for="suPass">Mot de passe</label>
          <div class="password-wrap">
            <input id="suPass" name="password" type="password" required minlength="6" placeholder="Minimum 6 caractères">
            <button type="button" class="toggle-visibility" aria-label="Afficher/masquer">👁️</button>
          </div>
          <p class="error" data-for="suPass">
            <?php echo $messages_flash['errors']['suPass'] ?? ''; ?>
          </p>
        </div>

        <!-- Champ Confirmation mot de passe -->
        <div class="field">
          <label for="suConfirm">Confirmer le mot de passe</label>
          <div class="password-wrap">
            <input id="suConfirm" name="confirm" type="password" required minlength="6" placeholder="Répétez le mot de passe">
            <button type="button" class="toggle-visibility" aria-label="Afficher/masquer">👁️</button>
          </div>
          <p class="error" data-for="suConfirm">
            <?php echo $messages_flash['errors']['suConfirm'] ?? ''; ?>
          </p>
        </div>

        <!-- Case à cocher CGU -->
        <div class="field">
          <label>
            <input id="suCgu" type="checkbox" required> 
            J'accepte les CGU
          </label>
        </div>

        <!-- Token CSRF caché -->
        <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf']; ?>">

        <!-- Bouton de soumission -->
        <button type="submit" class="btn-primary">Créer le compte</button>
        
        <!-- Message d'erreur général -->
        <p class="error note">
          <?php echo $messages_flash['errors']['general'] ?? ''; ?>
        </p>
      </form>
    </div>

    <!-- ========== PANNEAU D'INFORMATION ========== -->
    <div class="card">
      <h3 class="title">Conseils</h3>
      <ul class="muted">
        <li>Utilise un email valide (ex : nom@domaine.com).</li>
        <li>Mot de passe : au moins 6 caractères.</li>
        <li>Après connexion/inscription réussie, tu seras redirigé vers un <strong>captcha</strong>, puis vers l'accueil.</li>
      </ul>
    </div>
  </section>
</main>

<!-- ========== JAVASCRIPT ========== -->
<script>
// Attendre que la page soit complètement chargée
document.addEventListener('DOMContentLoaded', function() {
  
  // ===== GESTION DES ONGLETS =====
  var onglets = document.querySelectorAll('.auth-tab');
  var formulaires = document.querySelectorAll('.auth-form');
  
  onglets.forEach(function(onglet) {
    onglet.addEventListener('click', function() {
      // Enlever la classe "active" de tous les onglets et formulaires
      onglets.forEach(function(o) {
        o.classList.remove('is-active');
      });
      formulaires.forEach(function(f) {
        f.classList.remove('is-active');
      });
      
      // Ajouter la classe "active" à l'onglet cliqué
      onglet.classList.add('is-active');
      
      // Afficher le formulaire correspondant
      var cible = document.querySelector(onglet.dataset.target);
      if (cible) {
        cible.classList.add('is-active');
      }
    });
  });

  // ===== AFFICHER/MASQUER LES MOTS DE PASSE =====
  var boutonsVisibilite = document.querySelectorAll('.toggle-visibility');
  
  boutonsVisibilite.forEach(function(bouton) {
    bouton.addEventListener('click', function() {
      // Trouver le champ de mot de passe juste avant le bouton
      var champMotDePasse = bouton.previousElementSibling;
      
      if (champMotDePasse) {
        // Alterner entre "password" et "text"
        if (champMotDePasse.type === 'password') {
          champMotDePasse.type = 'text';
        } else {
          champMotDePasse.type = 'password';
        }
      }
    });
  });

  // ===== FONCTION : Afficher un message d'erreur =====
  function afficherErreur(idChamp, message) {
    var elementErreur = document.querySelector('.error[data-for="' + idChamp + '"]');
    if (elementErreur) {
      elementErreur.textContent = message || '';
    }
  }

  // ===== FONCTION : Bloquer le bouton pendant l'envoi =====
  function bloquerBouton(formulaire) {
    var bouton = formulaire.querySelector('button[type="submit"]');
    if (bouton) {
      bouton.textContent = 'Envoi…';
      bouton.disabled = true;
    }
  }

  // ===== VALIDATION DU FORMULAIRE DE CONNEXION =====
  var formulaireConnexion = document.getElementById('loginForm');
  
  if (formulaireConnexion) {
    formulaireConnexion.addEventListener('submit', function(evenement) {
      var champEmail = document.getElementById('loginEmail');
      var champPassword = document.getElementById('loginPassword');
      
      // Effacer les anciennes erreurs
      afficherErreur('loginEmail', '');
      afficherErreur('loginPassword', '');
      
      var formulaireValide = true;
      
      // Vérifier l'email
      if (!champEmail || !champEmail.value || !champEmail.checkValidity()) {
        formulaireValide = false;
        afficherErreur('loginEmail', 'Email invalide');
      }
      
      // Vérifier le mot de passe (minimum 6 caractères)
      if (!champPassword || !champPassword.value || champPassword.value.length < 6) {
        formulaireValide = false;
        afficherErreur('loginPassword', '6 caractères minimum');
      }
      
      // Si le formulaire n'est pas valide, empêcher l'envoi
      if (!formulaireValide) {
        evenement.preventDefault();
        return;
      }
      
      // Bloquer le bouton pour éviter les doubles clics
      bloquerBouton(formulaireConnexion);
    });
  }

  // ===== VALIDATION DU FORMULAIRE D'INSCRIPTION =====
  var formulaireInscription = document.getElementById('signupForm');
  
  if (formulaireInscription) {
    formulaireInscription.addEventListener('submit', function(evenement) {
      var champNom = document.getElementById('suName');
      var champEmail = document.getElementById('suEmail');
      var champPassword = document.getElementById('suPass');
      var champConfirmation = document.getElementById('suConfirm');
      var caseACocherCGU = document.getElementById('suCgu');
      
      // Effacer les anciennes erreurs
      afficherErreur('suName', '');
      afficherErreur('suEmail', '');
      afficherErreur('suPass', '');
      afficherErreur('suConfirm', '');
      
      var formulaireValide = true;
      
      // Vérifier le pseudo (minimum 3 caractères)
      if (!champNom || !champNom.value || champNom.value.length < 3) {
        formulaireValide = false;
        afficherErreur('suName', '3 caractères minimum');
      }
      
      // Vérifier l'email
      if (!champEmail || !champEmail.value || !champEmail.checkValidity()) {
        formulaireValide = false;
        afficherErreur('suEmail', 'Email invalide');
      }
      
      // Vérifier le mot de passe (minimum 6 caractères)
      if (!champPassword || !champPassword.value || champPassword.value.length < 6) {
        formulaireValide = false;
        afficherErreur('suPass', '6 caractères minimum');
      }
      
      // Vérifier que les mots de passe correspondent
      if (!champConfirmation || champPassword.value !== champConfirmation.value) {
        formulaireValide = false;
        afficherErreur('suConfirm', 'Les mots de passe ne correspondent pas');
      }
      
      // Vérifier que la case CGU est cochée
      if (caseACocherCGU && !caseACocherCGU.checked) {
        formulaireValide = false;
        alert('Vous devez accepter les CGU.');
      }
      
      // Si le formulaire n'est pas valide, empêcher l'envoi
      if (!formulaireValide) {
        evenement.preventDefault();
        return;
      }
      
      // Bloquer le bouton pour éviter les doubles clics
      bloquerBouton(formulaireInscription);
    });
  }
});
</script>

</body>
</html>