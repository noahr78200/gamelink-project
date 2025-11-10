async function LoadGame() {
  const url = '/GameLink/API/igdb.php';
  const options = {
    method: 'POST',
    headers: { 'content-type': 'text/plain' },
    body:
      'fields name, cover.image_id;' +
      ' where cover != null;' +
      ' sort popularity desc;' +
      ' limit 420;' // multiple de 3 / 4 / 5 / 6 / 7 pour une page complete
  };

  const response = await fetch(url, options);
  if (!response.ok) { ShowError('Erreur de chargement.'); return; }

  const data = await response.json();
  console.log('Jeux reçus (top):', Array.isArray(data) ? data.length : data);
  afficherGames(data);
}

function afficherGames(list) {
  const container = document.getElementById('Game-list');
  container.innerHTML = '';

  if (!Array.isArray(list) || list.length === 0) {
    container.textContent = 'Aucun jeu trouvé.';
    return;
  }

  list.forEach(function (game) {
    const card = document.createElement('a');
    card.classList.add('card');
    card.href = '/GameLink/PAGE/game.php?id=' + game.id;
    card.style.textDecoration = 'none';

    const img = document.createElement('img');
    img.alt = game.name;

    if (game.cover && game.cover.image_id) {
      img.src = 'https://images.igdb.com/igdb/image/upload/t_cover_big/' + game.cover.image_id + '.jpg';
    } else {
      img.src = 'https://placehold.co/264x352?text=' + encodeURIComponent(game.name || 'Jeu');
    }

    const title = document.createElement('h2');
    title.textContent = game.name;

    card.appendChild(img);
    card.appendChild(title);
    container.appendChild(card);
  });
}

function ShowError(message) {
  const container = document.getElementById('Game-list');
  if (container) container.textContent = message;
}


async function searchItems(query) {
  const url = '/GameLink/API/igdb.php';
  const q = query.replace(/"/g, '\\"');

  const options = {
    method: 'POST',
    headers: { 'content-type': 'text/plain' },
    body:
      'search "' + q + '";' +
      ' fields name, cover.image_id;' +
      ' where cover != null;' +
      ' limit 420;'
  };

  const response = await fetch(url, options);
  if (!response.ok) { ShowError('Erreur de chargement.'); return; }

  const data = await response.json();
  console.log('Jeux reçus (search):', q, Array.isArray(data) ? data.length : data);
  afficherGames(data);
}

function initSearch() {
  const input = document.getElementById('search-Bar');

  input.addEventListener('input', function () {
    const query = input.value.trim();
    if (query) {
      searchItems(query);
    } else {
      LoadGame();
    }
  });
  input.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      const q = input.value.trim();
      if (q) { searchItems(q); } else { LoadGame(); }
    }
  });
  input.addEventListener('input', function () {
    const q = input.value.trim();
    if (q === '') LoadGame();
  });
}


if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', function () {
    LoadGame();
    initSearch();
  });
} else {
  LoadGame();
  initSearch();
}