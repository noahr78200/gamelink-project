document.addEventListener('DOMContentLoaded', () => {
  // ----- Onglets Connexion/Inscription -----
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

  // Helpers affichage d’erreur
  function setError(id, msg){
    const el = document.querySelector(`.error[data-for="${id}"]`);
    if (el) el.textContent = msg || '';
  }

  // Util: désactive le bouton submit + petit état "Envoi…"
  function lockSubmit(form){
    const btn = form.querySelector('button[type="submit"], input[type="submit"]');
    if (!btn) return;
    btn.dataset._txt = btn.textContent || btn.value || '';
    if (btn.tagName === 'BUTTON') btn.textContent = 'Envoi…';
    else btn.value = 'Envoi…';
    btn.disabled = true;
  }
  function unlockSubmit(form){
    const btn = form.querySelector('button[type="submit"], input[type="submit"]');
    if (!btn) return;
    const t = btn.dataset._txt || '';
    if (btn.tagName === 'BUTTON') btn.textContent = t;
    else btn.value = t;
    btn.disabled = false;
  }

  // ----- Soumission CONNEXION (vers PHP) -----
  const loginForm = document.getElementById('login');
  if (loginForm){
    // Sécurité: si l'action est vide, on met celle attendue
    if (!loginForm.getAttribute('action')) {
      loginForm.setAttribute('action', '../INCLUDES/auth_login.php');
    }
    if (!loginForm.getAttribute('method')) {
      loginForm.setAttribute('method', 'post');
    }

    loginForm.addEventListener('submit', (e) => {
      const email = document.getElementById('loginEmail');
      const pass  = document.getElementById('loginPassword');

      let ok = true;
      setError('loginEmail',''); 
      setError('loginPassword','');

      if (!email || !email.value || !email.checkValidity()){
        ok=false; setError('loginEmail','Email invalide');
      }
      if (!pass || !pass.value || pass.value.length < 6){
        ok=false; setError('loginPassword','6 caractères minimum');
      }

      if (!ok){
        // on empêche l’envoi uniquement si invalide
        e.preventDefault();
        loginForm.reportValidity?.();
        return;
      }

      // UX anti double clic, puis on LAISSE PARTIR la requête vers PHP
      lockSubmit(loginForm);

      // "Se souvenir" sans bloquer l’envoi
      const remember = document.getElementById('rememberMe');
      if (remember && remember.checked) {
        localStorage.setItem('rememberEmail', email.value.trim());
      } else {
        localStorage.removeItem('rememberEmail');
      }
      // NE PAS faire de window.location.href ici
      // NE PAS faire de e.preventDefault() si tout est ok
    });
  }

  // ----- Soumission INSCRIPTION (vers PHP) -----
  const signupForm = document.getElementById('signup');
  if (signupForm){
    if (!signupForm.getAttribute('action')) {
      signupForm.setAttribute('action', '../INCLUDES/auth_register.php');
    }
    if (!signupForm.getAttribute('method')) {
      signupForm.setAttribute('method', 'post');
    }

    signupForm.addEventListener('submit', (e) => {
      const name = document.getElementById('suName');
      const email= document.getElementById('suEmail');
      const pass = document.getElementById('suPass');
      const conf = document.getElementById('suConfirm');
      const cgu  = document.getElementById('suCgu');

      setError('suName',''); setError('suEmail',''); setError('suPass',''); setError('suConfirm','');

      let ok = true;
      if (!name || !name.value || name.value.length < 3){ ok=false; setError('suName','3 caractères minimum'); }
      if (!email || !email.value || !email.checkValidity()){ ok=false; setError('suEmail','Email invalide'); }
      if (!pass || !pass.value || pass.value.length < 6){ ok=false; setError('suPass','6 caractères minimum'); }
      if (!conf || pass.value !== conf.value){ ok=false; setError('suConfirm','Les mots de passe ne correspondent pas'); }
      if (cgu && !cgu.checked){ ok=false; alert('Vous devez accepter les CGU.'); }

      if (!ok){
        // Empêche l’envoi seulement si invalide
        e.preventDefault();
        signupForm.reportValidity?.();
        return;
      }

      // UX anti double clic, puis on laisse partir vers PHP
      lockSubmit(signupForm);
      // NE RIEN BLOQUER ensuite : pas de redirect JS, pas de preventDefault
    });
  }

  // Prérempli "se souvenir"
  const remembered = localStorage.getItem('rememberEmail');
  if (remembered){
    const el = document.getElementById('loginEmail');
    const cb = document.getElementById('rememberMe');
    if (el) el.value = remembered;
    if (cb) cb.checked = true;
  }
});
