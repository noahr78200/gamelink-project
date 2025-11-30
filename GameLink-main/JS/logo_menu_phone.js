document.addEventListener('DOMContentLoaded', function() {
    const header = document.querySelector('header');
    const menu = document.querySelector('.Menu');
    const profileLink = document.querySelector('header > a:last-child');
    
    let hamburger = document.querySelector('.hamburger');
    
    if (!hamburger) {
        hamburger = document.createElement('button');
        hamburger.className = 'hamburger';
        hamburger.setAttribute('aria-label', 'Menu');
        hamburger.setAttribute('aria-expanded', 'false');
        hamburger.innerHTML = `
            <span></span>
            <span></span>
            <span></span>
        `;
        
        if (profileLink) {
            header.insertBefore(hamburger, profileLink);
        } else {
            header.appendChild(hamburger);
        }
    }
    
    hamburger.addEventListener('click', function() {
        const isActive = menu.classList.toggle('active');
        hamburger.classList.toggle('active');
        hamburger.setAttribute('aria-expanded', isActive);
        
        if (isActive) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    });
    
    const menuLinks = menu.querySelectorAll('a');
    menuLinks.forEach(link => {
        link.addEventListener('click', function() {
            menu.classList.remove('active');
            hamburger.classList.remove('active');
            hamburger.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        });
    });
    
    window.addEventListener('resize', function() {
        if (window.innerWidth > 1024) {
            menu.classList.remove('active');
            hamburger.classList.remove('active');
            hamburger.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }
    });
});