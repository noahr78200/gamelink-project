document.addEventListener('DOMContentLoaded', () => {
  // ----- Onglets (si tu en as) -----
  const tabs = document.querySelectorAll('.auth-tab');
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

  // ----- Afficher / masquer les mots de passe -----
  document.querySelectorAll('.toggle-visibility').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = btn.previousElementSibling;
      if (!input) return;
      input.type = input.type === 'password' ? 'text' : 'password';
    });
  });

  // Helper erreurs (affiche dans .error[data-for="idDuChamp"])
  function setError(id, msg){
    const el = document.querySelector(`.error[data-for="${id}"]`);
    if (el) el.textContent = msg || '';
  }

  // Petit anti double-clic submit (UX) — ne bloque pas l’envoi
  function lockSubmit(form){
    const btn = form.querySelector('button[type="submit"], input[type="submit"]');
    if (!btn) return;
    btn.dataset._txt = btn.textContent || btn.value || '';
    if (btn.tagName === 'BUTTON') btn.textContent = 'Envoi…';
    else btn.value = 'Envoi…';
    btn.disabled = true;
  }

  // ========== CONNEXION ==========
  const loginForm = document.getElementById('login');
  if (loginForm){
    loginForm.addEventListener('submit', (e) => {
      const email = document.getElementById('loginEmail');
      const pass  = document.getElementById('loginPassword');

      // reset erreurs UI
      setError('loginEmail',''); 
      setError('loginPassword','');

      let ok = true;
      if (!email || !email.value || !email.checkValidity()){
        ok = false; setError('loginEmail', 'Email invalide');
      }
      if (!pass || !pass.value || pass.value.length < 6){
        ok = false; setError('loginPassword', '6 caractères minimum');
      }

      // Si invalide → on empêche (HTML5 + messages), sinon on laisse partir vers PHP
      if (!ok){
        e.preventDefault();
        loginForm.reportValidity?.();
        return;
      }
      lockSubmit(loginForm); // UX, puis POST envoyé à ../INCLUDES/auth_login.php
    });
  }

  // ========== INSCRIPTION ==========
  const signupForm = document.getElementById('signup');
  if (signupForm){
    signupForm.addEventListener('submit', (e) => {
      const name  = document.getElementById('suName');
      const email = document.getElementById('suEmail');
      const pass  = document.getElementById('suPass');
      const conf  = document.getElementById('suConfirm');
      const cgu   = document.getElementById('suCgu'); // optionnel

      // reset erreurs UI
      setError('suName',''); setError('suEmail',''); setError('suPass',''); setError('suConfirm','');

      let ok = true;
      if (!name || !name.value || name.value.length < 3){
        ok=false; setError('suName','3 caractères minimum');
      }
      if (!email || !email.value || !email.checkValidity()){
        ok=false; setError('suEmail','Email invalide');
      }
      if (!pass || !pass.value || pass.value.length < 6){
        ok=false; setError('suPass','6 caractères minimum');
      }
      if (!conf || pass.value !== conf.value){
        ok=false; setError('suConfirm','Les mots de passe ne correspondent pas');
      }
      if (cgu && !cgu.checked){
        ok=false; alert('Vous devez accepter les CGU.');
      }

      if (!ok){
        e.preventDefault();
        signupForm.reportValidity?.();
        return;
      }
      lockSubmit(signupForm); // UX, puis POST envoyé à ../INCLUDES/auth_register.php
    });
  }

  // Préremplir “se souvenir” (si présent)
  const remembered = localStorage.getItem('rememberEmail');
  if (remembered){
    const el = document.getElementById('loginEmail');
    const cb = document.getElementById('rememberMe');
    if (el) el.value = remembered;
    if (cb) cb.checked = true;
  }
});
