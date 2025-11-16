// Adresse de notre fichier PHP qui parle avec IGDB
const API_URL = '../API/igdb.php';

// On récupère les éléments de la page
const gameList = document.getElementById('Game-list');
const searchInput = document.getElementById('search-input');
const searchBtn = document.getElementById('search-button');
const showAllBtn = document.getElementById('show-all-button');
const errorBox = document.getElementById('error-message');
const gamesCount = document.getElementById('games-count');
const loadingText = document.getElementById('loading-text');
const resetBtn = document.getElementById('filters-reset');
const chips = document.querySelectorAll('.chip');

// On stocke tous les jeux ici
let allGames = [];

// Les filtres qu'on a choisis
let activePlatform = ''; // ex: "pc"
let activeGenre = '';    // ex: "role-playing"
let activeEditor = '';   // ex: "nintendo"
let activeSort = 'name-asc'; // tri par défaut A→Z

// ===== CRÉER UNE CARTE DE JEU =====
function createGameCard(game) {
    // On crée un élément de liste
    const li = document.createElement('li');
    li.className = 'game-card';

    // On crée un lien
    const link = document.createElement('a');
    link.href = 'game.php?id=' + game.id;
    link.className = 'game-link';

    // Image du jeu
    const img = document.createElement('img');
    img.className = 'game-cover';
    img.alt = game.name || 'Jeu';

    // Si le jeu a une image, on l'utilise
    if (game.cover && game.cover.image_id) {
        img.src = 'https://images.igdb.com/igdb/image/upload/t_cover_big/' + game.cover.image_id + '.jpg';
    } else {
        // Sinon on met une image par défaut
        img.src = 'https://placehold.co/264x352?text=' + encodeURIComponent(game.name || 'Jeu');
    }

    // Titre du jeu
    const title = document.createElement('h2');
    title.className = 'game-title';
    title.textContent = game.name || 'Sans nom';

    // Informations (note et date)
    const info = document.createElement('p');
    info.className = 'game-info';

    let infoParts = [];

    // Si le jeu a une note
    if (typeof game.rating === 'number') {
        infoParts.push('Note : ' + game.rating.toFixed(0) + '/100');
    }

    // Si le jeu a une date de sortie
    if (typeof game.first_release_date === 'number') {
        const date = new Date(game.first_release_date * 1000);
        infoParts.push('Sortie : ' + date.toLocaleDateString('fr-FR'));
    }

    info.textContent = infoParts.join(' · ');

    // On assemble tout
    link.appendChild(img);
    link.appendChild(title);
    link.appendChild(info);
    li.appendChild(link);

    return li;
}

// ===== TRIER LES JEUX =====
function sortGames(games) {
    // On fait une copie pour ne pas modifier l'original
    let sorted = games.slice();

    if (activeSort === 'name-asc') {
        // A → Z
        sorted.sort(function(a, b) {
            const nameA = (a.name || '').toLowerCase();
            const nameB = (b.name || '').toLowerCase();
            return nameA.localeCompare(nameB, 'fr');
        });
    } else if (activeSort === 'name-desc') {
        // Z → A
        sorted.sort(function(a, b) {
            const nameA = (a.name || '').toLowerCase();
            const nameB = (b.name || '').toLowerCase();
            return nameB.localeCompare(nameA, 'fr');
        });
    } else if (activeSort === 'date-desc') {
        // Plus récent d'abord
        sorted.sort(function(a, b) {
            const dateA = a.first_release_date || 0;
            const dateB = b.first_release_date || 0;
            return dateB - dateA;
        });
    } else if (activeSort === 'date-asc') {
        // Plus ancien d'abord
        sorted.sort(function(a, b) {
            const dateA = a.first_release_date || 0;
            const dateB = b.first_release_date || 0;
            return dateA - dateB;
        });
    } else if (activeSort === 'rating-desc') {
        // Meilleure note d'abord
        sorted.sort(function(a, b) {
            const ratingA = a.rating || 0;
            const ratingB = b.rating || 0;
            return ratingB - ratingA;
        });
    }

    return sorted;
}

// ===== AFFICHER LES JEUX =====
function showGames(games) {
    // On vide la liste
    gameList.innerHTML = '';
    errorBox.textContent = '';

    // On cache le texte de chargement
    if (loadingText) {
        loadingText.style.display = 'none';
    }

    // Si aucun jeu
    if (!Array.isArray(games) || games.length === 0) {
        if (gamesCount) {
            gamesCount.textContent = '0 jeu trouvé';
        }
        errorBox.textContent = 'Aucun jeu trouvé avec ces critères.';
        return;
    }

    // On trie les jeux
    const sortedGames = sortGames(games);

    // On met à jour le compteur
    if (gamesCount) {
        gamesCount.textContent = sortedGames.length + (sortedGames.length > 1 ? ' jeux trouvés' : ' jeu trouvé');
    }

    // On crée une carte pour chaque jeu
    sortedGames.forEach(function(game) {
        const card = createGameCard(game);
        gameList.appendChild(card);
    });
}

// ===== APPLIQUER LES FILTRES =====
function applyFilters() {
    // On part de tous les jeux
    let filtered = allGames.slice();

    // Filtre par plateforme
    if (activePlatform !== '') {
        filtered = filtered.filter(function(game) {
            // On récupère les plateformes du jeu
            if (!Array.isArray(game.platforms)) {
                return false;
            }

            // On cherche si une plateforme correspond
            const key = activePlatform.toLowerCase();
            return game.platforms.some(function(platform) {
                if (!platform || !platform.name) {
                    return false;
                }
                const platformName = platform.name.toLowerCase();
                return platformName.includes(key);
            });
        });
    }

    // Filtre par genre
    if (activeGenre !== '') {
        filtered = filtered.filter(function(game) {
            // On récupère les genres du jeu
            if (!Array.isArray(game.genres)) {
                return false;
            }

            // On cherche si un genre correspond
            const key = activeGenre.toLowerCase();
            return game.genres.some(function(genre) {
                if (!genre || !genre.name) {
                    return false;
                }
                const genreName = genre.name.toLowerCase();
                return genreName.includes(key);
            });
        });
    }

    // Filtre par éditeur
    if (activeEditor !== '') {
        filtered = filtered.filter(function(game) {
            // On récupère les éditeurs du jeu
            if (!Array.isArray(game.involved_companies)) {
                return false;
            }

            // On cherche si un éditeur correspond
            const key = activeEditor.toLowerCase();
            return game.involved_companies.some(function(company) {
                if (!company || !company.company || !company.company.name) {
                    return false;
                }
                const companyName = company.company.name.toLowerCase();
                return companyName.includes(key);
            });
        });
    }

    // On affiche les jeux filtrés
    showGames(filtered);
}

// ===== CHARGER TOUS LES JEUX =====
function loadAllGames() {
    // On affiche le chargement
    if (loadingText) {
        loadingText.style.display = 'block';
    }
    errorBox.textContent = '';

    // On prépare les données à envoyer
    const formData = new FormData();
    formData.append('mode', 'all');

    // On fait la requête
    fetch(API_URL, {
        method: 'POST',
        body: formData
    })
    .then(function(response) {
        if (!response.ok) {
            throw new Error('Erreur serveur : ' + response.status);
        }
        return response.json();
    })
    .then(function(data) {
        // On stocke tous les jeux
        allGames = Array.isArray(data) ? data : [];
        // On applique les filtres
        applyFilters();
    })
    .catch(function(error) {
        if (loadingText) {
            loadingText.style.display = 'none';
        }
        errorBox.textContent = 'Erreur de chargement des jeux.';
        console.error(error);
    });
}

// ===== RECHERCHER DES JEUX =====
function searchGames() {
    // On récupère le texte de recherche
    const query = searchInput.value.trim();

    // Si vide, on ne fait rien
    if (query === '') {
        errorBox.textContent = 'Tape un nom de jeu pour rechercher.';
        return;
    }

    // On affiche le chargement
    if (loadingText) {
        loadingText.style.display = 'block';
    }
    errorBox.textContent = '';

    // On prépare les données
    const formData = new FormData();
    formData.append('mode', 'search');
    formData.append('search', query);

    // On fait la requête
    fetch(API_URL, {
        method: 'POST',
        body: formData
    })
    .then(function(response) {
        if (!response.ok) {
            throw new Error('Erreur serveur : ' + response.status);
        }
        return response.json();
    })
    .then(function(data) {
        // On stocke les jeux trouvés
        allGames = Array.isArray(data) ? data : [];
        // On applique les filtres
        applyFilters();
    })
    .catch(function(error) {
        if (loadingText) {
            loadingText.style.display = 'none';
        }
        errorBox.textContent = 'Erreur lors de la recherche.';
        console.error(error);
    });
}

// ===== GÉRER LES FILTRES (CHIPS) =====
function initFilters() {
    // Pour chaque bouton de filtre
    chips.forEach(function(chip) {
        chip.addEventListener('click', function() {
            const type = chip.getAttribute('data-type');
            const value = chip.getAttribute('data-value') || '';

            // On enlève chip--active sur les autres du même type
            chips.forEach(function(c) {
                if (c.getAttribute('data-type') === type) {
                    c.classList.remove('chip--active');
                }
            });

            // On active celui qu'on vient de cliquer
            chip.classList.add('chip--active');

            // On met à jour le filtre correspondant
            if (type === 'platform') {
                activePlatform = value;
            } else if (type === 'genre') {
                activeGenre = value;
            } else if (type === 'editor') {
                activeEditor = value;
            } else if (type === 'sort') {
                activeSort = value;
            }

            // On applique les filtres
            applyFilters();
        });
    });

    // Bouton de réinitialisation
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            // On réinitialise tous les filtres
            activePlatform = '';
            activeGenre = '';
            activeEditor = '';
            activeSort = 'name-asc';

            // On réactive tous les "Tous"
            chips.forEach(function(c) {
                const type = c.getAttribute('data-type');
                const value = c.getAttribute('data-value') || '';

                if (value === '' || (type === 'sort' && value === 'name-asc')) {
                    c.classList.add('chip--active');
                } else {
                    c.classList.remove('chip--active');
                }
            });

            // On vide la barre de recherche
            if (searchInput) {
                searchInput.value = '';
            }

            // On applique les filtres
            applyFilters();
        });
    }
}

// ===== INITIALISATION =====
function initSearch() {
    // Bouton de recherche
    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            searchGames();
        });
    }

    // Touche Entrée dans la barre de recherche
    if (searchInput) {
        searchInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                searchGames();
            }
        });
    }

    // Bouton "Afficher tous les jeux"
    if (showAllBtn) {
        showAllBtn.addEventListener('click', function() {
            // On vide la barre de recherche
            if (searchInput) {
                searchInput.value = '';
            }
            // On charge tous les jeux
            loadAllGames();
        });
    }

    // On initialise les filtres
    initFilters();

    // Au départ, on n'affiche rien (l'utilisateur doit cliquer sur "Afficher tous")
    if (loadingText) {
        loadingText.style.display = 'none';
    }
}

// On démarre quand la page est prête
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSearch);
} else {
    initSearch();
}