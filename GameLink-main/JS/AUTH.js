// AUTH.js — version safe : NE BLOQUE PAS le submit, laisse PHP traiter

document.addEventListener('DOMContentLoaded', () => {
  // ====== 1) Références aux formulaires (si présents) ======
  const loginForm  = document.getElementById('loginForm');
  const signupForm = document.getElementById('signupForm');

  // ====== 2) Bascule des onglets (optionnel) ======
  // Attends des boutons avec data-tab-target=".nomOnglet" et des panneaux avec data-tab="nomOnglet"
  const tabButtons = document.querySelectorAll('[data-tab-target]');
  const tabPanels  = document.querySelectorAll('[data-tab]');
  if (tabButtons.length && tabPanels.length) {
    tabButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const target = btn.getAttribute('data-tab-target');
        // cacher tous les panneaux
        tabPanels.forEach(p => p.hidden = true);
        // montrer le panneau ciblé
        const panel = document.querySelector(`[data-tab="${CSS.escape(target.replace('.', ''))}"]`) 
                   || document.querySelector(target);
        if (panel) panel.hidden = false;
        // état actif visuel (optionnel)
        tabButtons.forEach(b => b.classList.remove('is-active'));
        btn.classList.add('is-active');
      });
    });
  }

  // ====== 3) Afficher/Masquer les mots de passe ======
  // On suppose un HTML du type:
  // <div class="password-wrap">
  //   <input type="password" ...>
  //   <button type="button" class="toggle-visibility">👁️</button>
  // </div>
  document.querySelectorAll('.toggle-visibility').forEach(btn => {
    btn.addEventListener('click', () => {
      // Si un data-target est donné, on l'utilise, sinon on prend l'input juste avant dans le wrap
      const selector = btn.getAttribute('data-target');
      let input;
      if (selector) {
        input = document.querySelector(selector);
      } else {
        const wrap = btn.closest('.password-wrap');
        if (wrap) input = wrap.querySelector('input[type="password"], input[type="text"]');
      }
      if (!input) return;
      input.type = input.type === 'password' ? 'text' : 'password';
      // Optionnel : feedback visuel
      btn.setAttribute('aria-pressed', String(input.type === 'text'));
    });
  });

  // ====== 4) Filtrage rapide des espaces pour les emails (UX) ======
  const emailInputs = document.querySelectorAll('input[type="email"][name="email"]');
  emailInputs.forEach(inp => {
    inp.addEventListener('blur', () => {
      inp.value = inp.value.trim();
    });
  });

  // ====== 5) Fonction utilitaire : prépare le submit sans le bloquer ======
  function wireSubmit(form) {
    if (!form) return;

    form.addEventListener('submit', (e) => {
      // Ne JAMAIS appeler e.preventDefault() ici → on laisse partir vers PHP

      // a) Validation HTML5 : si invalide, le navigateur bloque tout seul
      if (!form.checkValidity()) {
        // Déclenche les messages natifs si invalide
        e.preventDefault();
        form.reportValidity();
        return;
      }

      // b) Anti double-clic + petit feedback
      const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
      if (submitBtn && !submitBtn.disabled) {
        submitBtn.dataset.originalText = submitBtn.textContent || submitBtn.value || '';
        if (submitBtn.tagName === 'BUTTON') submitBtn.textContent = 'Envoi…';
        if (submitBtn.tagName === 'INPUT')  submitBtn.value       = 'Envoi…';
        submitBtn.disabled = true;
      }

      // c) Optionnel : verrouiller les champs pendant l'envoi
      Array.from(form.elements).forEach(el => {
        if (el.tagName === 'BUTTON' || el.type === 'submit') return;
        if ('readOnly' in el) el.readOnly = true;
      });

      // d) IMPORTANT : on ne touche PAS à l'action → PHP reçoit la requête (POST)
      //    Les redirections (captcha → ACCUEIL.php) sont gérées côté PHP.
    });
  }

  // Brancher les formulaires (si présents)
  wireSubmit(loginForm);
  wireSubmit(signupForm);

  // ====== 6) Messages d'erreur côté client (optionnel) ======
  // Si tu veux afficher un message en dessous d'un champ quand il devient valide/invalide :
  function liveFieldValidation(form) {
    if (!form) return;
    form.querySelectorAll('input[required], input[minlength]').forEach(inp => {
      inp.addEventListener('input', () => {
        const errEl = form.querySelector(`.error[data-for="${inp.id}"]`);
        if (!errEl) return;
        // Nettoie le message en saisie ; les erreurs serveur (flash) restent affichées côté PHP au rechargement
        if (inp.validity.valid) errEl.textContent = '';
      });
    });
  }
  liveFieldValidation(loginForm);
  liveFieldValidation(signupForm);
});
