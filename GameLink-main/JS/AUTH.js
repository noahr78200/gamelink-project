// AUTH.js — version qui ne bloque jamais le submit

// Tab switch (si tu as des onglets)
document.querySelectorAll('[data-tab-target]').forEach(btn => {
  btn.addEventListener('click', () => {
    const target = btn.getAttribute('data-tab-target');
    document.querySelectorAll('[data-tab]').forEach(p => p.hidden = true);
    document.querySelector(`[data-tab="${target}"]`).hidden = false;
  });
});

// Toggle visibilité mot de passe (ne touche pas au submit)
document.querySelectorAll('.toggle-visibility').forEach(btn => {
  btn.addEventListener('click', () => {
    const selector = btn.getAttribute('data-target');
    const input = document.querySelector(selector);
    if (!input) return;
    input.type = (input.type === 'password') ? 'text' : 'password';
  });
});

// IMPORTANT: AUCUN e.preventDefault() sur les 'submit' !
// Pas de window.location.href ici non plus.
