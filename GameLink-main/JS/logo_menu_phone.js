// Script pour le menu hamburger responsive

document.addEventListener('DOMContentLoaded', function() {
    // Créer le bouton hamburger s'il n'existe pas
    const header = document.querySelector('header');
    const menu = document.querySelector('.Menu');
    const profileLink = document.querySelector('header > a:last-child');
    
    // Vérifier si le hamburger existe déjà
    let hamburger = document.querySelector('.hamburger');
    
    if (!hamburger) {
        // Créer le bouton hamburger
        hamburger = document.createElement('button');
        hamburger.className = 'hamburger';
        hamburger.setAttribute('aria-label', 'Menu');
        hamburger.setAttribute('aria-expanded', 'false');
        hamburger.innerHTML = `
            <span></span>
            <span></span>
            <span></span>
        `;
        
        // Insérer le hamburger avant l'icône de profil
        if (profileLink) {
            header.insertBefore(hamburger, profileLink);
        } else {
            header.appendChild(hamburger);
        }
    }
    
    // Toggle du menu au clic
    hamburger.addEventListener('click', function() {
        const isActive = menu.classList.toggle('active');
        hamburger.classList.toggle('active');
        hamburger.setAttribute('aria-expanded', isActive);
        
        // Empêcher le scroll quand le menu est ouvert
        if (isActive) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    });
    
    // Fermer le menu quand on clique sur un lien
    const menuLinks = menu.querySelectorAll('a');
    menuLinks.forEach(link => {
        link.addEventListener('click', function() {
            menu.classList.remove('active');
            hamburger.classList.remove('active');
            hamburger.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        });
    });
    
    // Fermer le menu si on redimensionne la fenêtre au-delà de 1024px
    window.addEventListener('resize', function() {
        if (window.innerWidth > 1024) {
            menu.classList.remove('active');
            hamburger.classList.remove('active');
            hamburger.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }
    });
});