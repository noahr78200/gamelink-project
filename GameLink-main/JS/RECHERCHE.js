// RECHERCHE.js
// Version simple :
// - charge une liste de jeux par défaut (A → Z)
// - permet de chercher par nom
// - applique des filtres (plateforme, genre, éditeur) côté client

// URL de l'API PHP
// En prod (VPS) :
const API_URL = '/API/igdb.php';
// En local, si besoin : const API_URL = '../API/igdb.php';

let tousLesJeux = [];          // tous les jeux reçus de l'API
let filtrePlateforme = '';     // nom exact de la plateforme
let filtreGenre = '';          // nom exact du genre
let filtreEditeur = '';        // nom exact de l'éditeur

// Récupération des éléments de la page
const gameList     = document.getElementById('Game-list');
const searchInput  = document.getElementById('search-input');
const searchButton = document.getElementById('search-button');
const errorBox     = document.getElementById('error-message');
const gamesCountEl = document.getElementById('games-count');
const resetButton  = document.getElementById('filters-reset');

// ===============================
// 1) Charger les jeux depuis l'API
// ===============================
function chargerJeux(texteRecherche) {
  const formData = new FormData();
  formData.append('search', texteRecherche || '');

  fetch(API_URL, {
    method: 'POST',
    body: formData
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error('Erreur HTTP : ' + response.status);
      }
      return response.json();
    })
    .then(function (data) {
      // data doit être un tableau de jeux
      if (!Array.isArray(data)) {
        tousLesJeux = [];
      } else {
        tousLesJeux = data;
      }

      // On affiche les jeux en appliquant les filtres (au début : aucun)
      appliquerFiltresEtAfficher();
    })
    .catch(function (err) {
      console.error(err);
      errorBox.textContent = 'Erreur lors du chargement des jeux.';
    });
}

// ==================================
// 2) Appliquer les filtres et afficher
// ==================================
function appliquerFiltresEtAfficher() {
  gameList.innerHTML = '';
  errorBox.textContent = '';

  // On filtre les jeux dans un nouveau tableau
  let liste = tousLesJeux.filter(function (jeu) {
    const nom = (jeu.name || '').toLowerCase();

    // Récupérer les noms des plateformes, genres, éditeurs sous forme de tableaux simples
    let plateformes = [];
    if (Array.isArray(jeu.platforms)) {
      plateformes = jeu.platforms
        .filter(function (p) { return p && p.name; })
        .map(function (p) { return p.name; });
    }

    let genres = [];
    if (Array.isArray(jeu.genres)) {
      genres = jeu.genres
        .filter(function (g) { return g && g.name; })
        .map(function (g) { return g.name; });
    }

    let editeurs = [];
    if (Array.isArray(jeu.involved_companies)) {
      editeurs = jeu.involved_companies
        .filter(function (ic) { return ic && ic.company && ic.company.name; })
        .map(function (ic) { return ic.company.name; });
    }

    // Filtre plateforme
    if (filtrePlateforme !== '') {
      if (!plateformes.includes(filtrePlateforme)) {
        return false;
      }
    }

    // Filtre genre
    if (filtreGenre !== '') {
      if (!genres.includes(filtreGenre)) {
        return false;
      }
    }

    // Filtre éditeur
    if (filtreEditeur !== '') {
      if (!editeurs.includes(filtreEditeur)) {
        return false;
      }
    }

    return true;
  });

  // Tri alphabétique A → Z sur le nom du jeu
  liste.sort(function (a, b) {
    const na = (a.name || '').toLowerCase();
    const nb = (b.name || '').toLowerCase();
    return na.localeCompare(nb, 'fr');
  });

  // Si aucun jeu, on affiche un message
  if (liste.length === 0) {
    gamesCountEl.textContent = '0 jeu trouvé';
    errorBox.textContent = 'Aucun jeu trouvé avec ces critères.';
    return;
  } else {
    gamesCountEl.textContent =
      liste.length + (liste.length > 1 ? ' jeux trouvés' : ' jeu trouvé');
  }

  // On crée une carte pour chaque jeu
  liste.forEach(function (jeu) {
    const li = document.createElement('li');
    li.className = 'game-card';

    const lien = document.createElement('a');
    lien.className = 'game-link';
    lien.href = 'game.php?id=' + jeu.id;

    // Image
    const img = document.createElement('img');
    img.className = 'game-cover';
    img.alt = jeu.name || 'Jeu';

    if (jeu.cover && jeu.cover.image_id) {
      img.src =
        'https://images.igdb.com/igdb/image/upload/t_cover_big/' +
        jeu.cover.image_id +
        '.jpg';
    } else {
      img.src =
        'https://placehold.co/264x352?text=' +
        encodeURIComponent(jeu.name || 'Jeu');
    }

    // Contenu texte
    const body = document.createElement('div');
    body.className = 'game-card-body';

    const titre = document.createElement('h2');
    titre.className = 'game-title';
    titre.textContent = jeu.name || 'Sans nom';

    const info = document.createElement('p');
    info.className = 'game-info';

    const morceaux = [];

    if (typeof jeu.rating === 'number') {
      morceaux.push('Note : ' + Math.round(jeu.rating) + '/100');
    }

    if (typeof jeu.first_release_date === 'number') {
      const d = new Date(jeu.first_release_date * 1000);
      morceaux.push('Sortie : ' + d.toLocaleDateString('fr-FR'));
    }

    info.textContent = morceaux.join(' · ');

    // Tags (plateforme + genre + éditeur)
    const tags = document.createElement('div');
    tags.className = 'game-tags';

    if (Array.isArray(jeu.platforms)) {
      jeu.platforms.slice(0, 2).forEach(function (p) {
        if (p && p.name) {
          const tag = document.createElement('span');
          tag.className = 'game-tag';
          tag.textContent = p.name;
          tags.appendChild(tag);
        }
      });
    }

    if (Array.isArray(jeu.genres)) {
      jeu.genres.slice(0, 1).forEach(function (g) {
        if (g && g.name) {
          const tag = document.createElement('span');
          tag.className = 'game-tag';
          tag.textContent = g.name;
          tags.appendChild(tag);
        }
      });
    }

    if (Array.isArray(jeu.involved_companies)) {
      const ic = jeu.involved_companies.find(function (x) {
        return x && x.company && x.company.name;
      });
      if (ic && ic.company && ic.company.name) {
        const tag = document.createElement('span');
        tag.className = 'game-tag';
        tag.textContent = ic.company.name;
        tags.appendChild(tag);
      }
    }

    body.appendChild(titre);
    body.appendChild(info);
    body.appendChild(tags);

    lien.appendChild(img);
    lien.appendChild(body);
    li.appendChild(lien);
    gameList.appendChild(li);
  });
}

// ===================================
// 3) Gestion de la recherche par nom
// ===================================
function lancerRecherche() {
  const texte = searchInput.value.trim();
  // On renvoie la recherche au serveur (API),
  // c’est lui qui gère recherche + tri
  chargerJeux(texte);
}

// ===================================
// 4) Gestion des filtres (boutons)
// ===================================
function initialiserFiltres() {
  const chips = document.querySelectorAll('.chip');

  chips.forEach(function (chip) {
    chip.addEventListener('click', function () {
      const type  = chip.getAttribute('data-type');
      const value = chip.getAttribute('data-value') || '';

      // On enlève la classe active pour les autres du même type
      chips.forEach(function (c) {
        if (c.getAttribute('data-type') === type) {
          c.classList.remove('chip--active');
        }
      });

      // On active ce bouton
      chip.classList.add('chip--active');

      // On met à jour les variables de filtre
      if (type === 'platform') {
        filtrePlateforme = value;
      } else if (type === 'genre') {
        filtreGenre = value;
      } else if (type === 'editor') {
        filtreEditeur = value;
      }

      // On ré-applique les filtres (pas besoin de recharger l’API)
      appliquerFiltresEtAfficher();
    });
  });

  if (resetButton) {
    resetButton.addEventListener('click', function () {
      filtrePlateforme = '';
      filtreGenre = '';
      filtreEditeur = '';

      // Réactiver tous les boutons "Tous"
      const chips = document.querySelectorAll('.chip');
      chips.forEach(function (c) {
        const type  = c.getAttribute('data-type');
        const value = c.getAttribute('data-value') || '';
        if (value === '') {
          c.classList.add('chip--active');
        } else {
          c.classList.remove('chip--active');
        }
      });

      appliquerFiltresEtAfficher();
    });
  }
}

// ===================================
// 5) Initialisation de la page
// ===================================
function initRecherche() {
  // Quand on clique sur "Rechercher"
  if (searchButton) {
    searchButton.addEventListener('click', lancerRecherche);
  }

  // Quand on appuie sur Entrée dans l’input
  if (searchInput) {
    searchInput.addEventListener('keydown', function (event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        lancerRecherche();
      }
    });
  }

  initialiserFiltres();

  // Charger des jeux par défaut (liste A → Z)
  chargerJeux('');
}

// Lancer init quand la page est prête
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initRecherche);
} else {
  initRecherche();
}
