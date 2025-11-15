// JS/RECHERCHE.js
// Charge les jeux depuis igdb.php et les affiche sur la page Recherche.

// URL de ton fichier PHP qui appelle IGDB
const API_URL = '../API/igdb.php';

// Sélecteurs utiles
const gameList   = document.getElementById('Game-list');
const searchInput = document.getElementById('search-input');
const searchBtn   = document.getElementById('search-button');
const errorBox    = document.getElementById('error-message');

// ----------- FONCTION UTILITAIRE POUR CRÉER UNE CARTE JEU ----------
function createGameCard(game) {
    const li = document.createElement('li');
    li.className = 'game-card';

    const link = document.createElement('a');
    link.href = 'game.php?id=' + game.id;
    link.className = 'game-link';

    // Image de couverture
    const img = document.createElement('img');
    img.className = 'game-cover';
    img.alt = game.name || 'Jeu';

    if (game.cover && game.cover.image_id) {
        img.src = 'https://images.igdb.com/igdb/image/upload/t_cover_big/' +
                  game.cover.image_id + '.jpg';
    } else {
        img.src = 'https://placehold.co/264x352?text=' +
                  encodeURIComponent(game.name || 'Jeu');
    }

    // Titre
    const title = document.createElement('h2');
    title.className = 'game-title';
    title.textContent = game.name || 'Sans nom';

    // Infos simples (note + date)
    const info = document.createElement('p');
    info.className = 'game-info';

    let parts = [];

    if (typeof game.rating === 'number') {
        parts.push('Note : ' + game.rating.toFixed(0) + '/100');
    }

    if (typeof game.first_release_date === 'number') {
        const date = new Date(game.first_release_date * 1000);
        parts.push('Sortie : ' + date.toLocaleDateString('fr-FR'));
    }

    info.textContent = parts.join(' · ');

    // On assemble la carte
    link.appendChild(img);
    link.appendChild(title);
    link.appendChild(info);

    li.appendChild(link);

    return li;
}

// ------------- AFFICHER LA LISTE DES JEUX ----------------
function showGames(games) {
    gameList.innerHTML = '';
    errorBox.textContent = '';

    if (!Array.isArray(games) || games.length === 0) {
        errorBox.textContent = 'Aucun jeu trouvé.';
        return;
    }

    games.forEach(function (game) {
        const card = createGameCard(game);
        gameList.appendChild(card);
    });
}

// ------------- CHARGER LES JEUX POPULAIRES ----------------
function loadPopularGames() {
    const formData = new FormData();
    formData.append('mode', 'popular');

    fetch(API_URL, {
        method: 'POST',
        body: formData
    })
    .then(function (response) {
        if (!response.ok) {
            throw new Error('Erreur serveur : ' + response.status);
        }
        return response.json();
    })
    .then(function (data) {
        showGames(data);
    })
    .catch(function (error) {
        errorBox.textContent = 'Erreur de chargement des jeux.';
        console.error(error);
    });
}

// ------------- RECHERCHER DES JEUX ----------------
function searchGames() {
    const query = searchInput.value.trim();

    // Si la barre est vide → on recharge la liste populaire
    if (query === '') {
        loadPopularGames();
        return;
    }

    const formData = new FormData();
    formData.append('mode', 'search');
    formData.append('search', query);

    fetch(API_URL, {
        method: 'POST',
        body: formData
    })
    .then(function (response) {
        if (!response.ok) {
            throw new Error('Erreur serveur : ' + response.status);
        }
        return response.json();
    })
    .then(function (data) {
        showGames(data);
    })
    .catch(function (error) {
        errorBox.textContent = 'Erreur lors de la recherche.';
        console.error(error);
    });
}

// ------------- INITIALISATION -------------
function initSearch() {
    if (searchBtn) {
        searchBtn.addEventListener('click', function () {
            searchGames();
        });
    }

    if (searchInput) {
        searchInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                searchGames();
            }
        });
    }

    // On charge les jeux populaires au début
    loadPopularGames();
}

// Quand la page est prête on lance tout
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSearch);
} else {
    initSearch();
}
