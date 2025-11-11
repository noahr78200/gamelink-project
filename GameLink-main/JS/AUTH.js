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

  // ----- Soumission CONNEXION (MOCK) -----
  const loginForm = document.getElementById('login');
  if (loginForm){
    loginForm.addEventListener('submit', (e) => {
      //e.preventDefault(); // empêcher le submit classique
      const email = document.getElementById('loginEmail');
      const pass  = document.getElementById('loginPassword');

      let ok = true;
      setError('loginEmail', ''); setError('loginPassword','');

      if (!email.value || !email.checkValidity()){ ok=false; setError('loginEmail','Email invalide'); }
      if (!pass.value || pass.value.length < 6){ ok=false; setError('loginPassword','6 caractères minimum'); }

      if (!ok) return;

      // MOCK AUTH (front uniquement) : flag de session + redirection
      sessionStorage.setItem('isAuth', '1');
      if (document.getElementById('rememberMe').checked){
        localStorage.setItem('rememberEmail', email.value);
      } else {
        localStorage.removeItem('rememberEmail');
      }
      window.location.href = 'ADMIN.php';
    });
  }

  // ----- Soumission INSCRIPTION (MOCK) -----
  const signupForm = document.getElementById('signup');
  if (signupForm){
    signupForm.addEventListener('submit', (e) => {
    //  e.preventDefault();
      const name = document.getElementById('suName');
      const email= document.getElementById('suEmail');
      const pass = document.getElementById('suPass');
      const conf = document.getElementById('suConfirm');
      const cgu  = document.getElementById('suCgu');

      setError('suName',''); setError('suEmail',''); setError('suPass',''); setError('suConfirm','');

      let ok = true;
      if (!name.value || name.value.length < 3){ ok=false; setError('suName','3 caractères minimum'); }
      if (!email.value || !email.checkValidity()){ ok=false; setError('suEmail','Email invalide'); }
      if (!pass.value || pass.value.length < 6){ ok=false; setError('suPass','6 caractères minimum'); }
      if (pass.value !== conf.value){ ok=false; setError('suConfirm','Les mots de passe ne correspondent pas'); }
      if (!cgu.checked){ ok=false; alert('Vous devez accepter les CGU.'); }

      if (!ok) return;

      // MOCK REGISTER : stock local (démo), puis flag de session + redirection
      localStorage.setItem('mockUser', JSON.stringify({ name: name.value, email: email.value }));
      sessionStorage.setItem('isAuth', '1');
      window.location.href = 'ADMIN.php';
    });
  }

  // Prérempli "se souvenir"
  const remembered = localStorage.getItem('rememberEmail');
  if (remembered){
    const el = document.getElementById('loginEmail');
    const cb = document.getElementById('rememberMe');
    if (el) el.value = remembered;
    if (cb) cb.checked = true;
    //quechose
  }
});
