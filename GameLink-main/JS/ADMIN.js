// ==========================================
// FICHIER : ADMIN.js
// BUT : Gérer la page d'administration
// ==========================================

// Attendre que la page soit complètement chargée
document.addEventListener('DOMContentLoaded', () => {
  
  // ========================
  // GESTION DE LA MODALE
  // ========================
  
  const modal = document.getElementById('reportModal');
  if (!modal) return;
  
  const btnCloseAll = modal.querySelectorAll('.modal-close');
  const badge = document.getElementById('m-badge');

  // Formater une date
  function fmtDate(iso) {
    try {
      const d = new Date(iso);
      return d.toLocaleString('fr-FR', { dateStyle: 'medium', timeStyle: 'short' });
    } catch (e) {
      return iso;
    }
  }

  // Ouvrir la modale
  function openModal() {
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    const closeBtn = modal.querySelector('.modal-close');
    if (closeBtn) closeBtn.focus();
    document.addEventListener('keydown', onEsc);
  }

  // Fermer la modale
  function closeModal() {
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    document.removeEventListener('keydown', onEsc);
  }

  // Fermer avec la touche Échap
  function onEsc(e) {
    if (e.key === 'Escape') closeModal();
  }

  // Fermer via les boutons
  btnCloseAll.forEach(b => b.addEventListener('click', closeModal));

  // Fermer en cliquant sur l'overlay
  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
  });

  // Gérer les clics sur les lignes du tableau
  const rows = document.querySelectorAll('.report-table tbody tr.report-row');
  rows.forEach(row => {
    row.addEventListener('click', () => {
      rows.forEach(r => r.classList.remove('is-selected'));
      row.classList.add('is-selected');

      const d = row.dataset;
      
      // Fonction pour changer le texte d'un élément
      const setText = (id, value) => {
        const el = document.getElementById(id);
        if (el) el.textContent = value ?? '—';
      };

      setText('m-offender', d.offender);
      setText('m-message', d.message);
      setText('m-reason', d.reason);
      setText('m-date', d.date ? fmtDate(d.date) : '—');
      setText('m-game', d.game);
      setText('m-chat', d.chat);
      setText('m-reporter', d.reporter);

      const strikes = Number(d.strikes2025 || 0);
      setText('m-strikes', `${strikes} signalement${strikes > 1 ? 's' : ''} en 2025`);

      if (badge) {
        badge.hidden = false;
        badge.className = 'badge';
        if (strikes >= 5) {
          badge.textContent = 'Risque élevé';
          badge.classList.add('badge-bad');
        } else if (strikes >= 3) {
          badge.textContent = 'Récidiviste';
          badge.classList.add('badge-warn');
        } else {
          badge.textContent = 'Historique propre';
          badge.classList.add('badge-ok');
        }
      }

      openModal();
    });
  });
});

// =====================
// GRAPHIQUES (Chart.js)
// =====================
(function initCharts(){
  // Vérifier si Chart.js est chargé
  if (typeof Chart === 'undefined') return;

  // Récupérer les couleurs depuis le CSS
  const css = getComputedStyle(document.documentElement);
  const cText = (css.getPropertyValue('--text') || '#e6e9f2').trim();
  const cMuted = (css.getPropertyValue('--muted') || '#aab2c0').trim();
  const cBorder = (css.getPropertyValue('--border') || 'rgba(255,255,255,.2)').trim();
  const cAccent = (css.getPropertyValue('--accent') || '#8fb6ff').trim();
  const cAccent2 = (css.getPropertyValue('--accent-2') || '#c2ccff').trim();
  const cUp = (css.getPropertyValue('--up') || '#34d399').trim();

  // Convertir une couleur hexa en rgba
  function hexToRgba(hex, alpha){
    let c = hex.replace('#','');
    if (c.length === 3){
      c = c.split('').map(x => x + x).join('');
    }
    const r = parseInt(c.slice(0,2),16);
    const g = parseInt(c.slice(2,4),16);
    const b = parseInt(c.slice(4,6),16);
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
  }
  
  // Créer un dégradé pour les graphiques
  function makeAreaGradient(ctx, colorHex){
    const g = ctx.createLinearGradient(0, 0, 0, ctx.canvas.height);
    g.addColorStop(0, hexToRgba(colorHex, 0.30));
    g.addColorStop(1, hexToRgba(colorHex, 0.00));
    return g;
  }

  // Options communes pour tous les graphiques
  const commonOpts = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        mode: 'index',
        intersect: false,
        backgroundColor: 'rgba(0,0,0,.7)',
        borderColor: cBorder,
        borderWidth: 1,
        titleColor: '#fff',
        bodyColor: '#fff',
      }
    },
    scales: {
      x: {
        grid: { color: cBorder, drawBorder: false },
        ticks: { color: cMuted, maxRotation: 0 }
      },
      y: {
        grid: { color: cBorder, drawBorder: false },
        ticks: { color: cMuted, precision: 0 }
      }
    }
  };

  // ====================================
  // GRAPHIQUE 1 : Signalements journaliers
  // ====================================
  const elReports = document.getElementById('chartReports');
  if (elReports){
    const ctxR = elReports.getContext('2d');
    
    // Données : jours de la semaine
    const labelsR = ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'];
    
    // Données : nombre de signalements par jour
    const dataR = [120, 96, 142, 131, 171, 138, 127];

    // Créer le graphique
    new Chart(ctxR, {
      type: 'line',
      data: {
        labels: labelsR,
        datasets: [{
          label: 'Signalements',
          data: dataR,
          borderColor: cAccent,
          borderWidth: 2,
          tension: 0.35,
          pointRadius: 2,
          pointHoverRadius: 4,
          fill: true,
          backgroundColor: makeAreaGradient(ctxR, cAccent2)
        }]
      },
      options: { ...commonOpts }
    });
  }

  // ====================================
  // GRAPHIQUE 2 : Utilisateurs actifs en temps réel
  // ====================================
  const elActive = document.getElementById('chartActive');
  if (elActive){
    const ctxA = elActive.getContext('2d');

    const MAX_POINTS = 30;
    const labelsA = Array.from({length: MAX_POINTS}, (_,i) => `t-${MAX_POINTS - i}s`);
    const dataA = [];

    // Générer des données aléatoires
    let last = 2700;
    for (let i=0; i<MAX_POINTS; i++){
      last += Math.round((Math.random() - 0.5) * 40);
      dataA.push(Math.max(0, last));
    }

    // Créer le graphique
    const chartActive = new Chart(ctxA, {
      type: 'line',
      data: {
        labels: labelsA,
        datasets: [{
          label: 'Utilisateurs actifs',
          data: dataA,
          borderColor: cUp,
          borderWidth: 2,
          tension: 0.3,
          pointRadius: 0,
          fill: true,
          backgroundColor: makeAreaGradient(ctxA, cUp)
        }]
      },
      options: {
        ...commonOpts,
        animation: false,
        scales: {
          ...commonOpts.scales,
          y: { ...commonOpts.scales.y, suggestedMin: 0 }
        }
      }
    });

    // Mettre à jour le graphique toutes les secondes
    setInterval(() => {
      // Ajouter une nouvelle valeur
      last += Math.round((Math.random() - 0.5) * 60);
      last = Math.max(0, last);
      
      // Créer un label avec l'heure actuelle
      const nowLabel = new Date().toLocaleTimeString('fr-FR', { 
        minute:'2-digit', 
        second:'2-digit' 
      });

      // Ajouter le nouveau point
      chartActive.data.labels.push(nowLabel);
      chartActive.data.datasets[0].data.push(last);

      // Supprimer le point le plus ancien si on dépasse MAX_POINTS
      if (chartActive.data.labels.length > MAX_POINTS) {
        chartActive.data.labels.shift();
        chartActive.data.datasets[0].data.shift();
      }
      
      // Mettre à jour le graphique
      chartActive.update('none');
    }, 1000);
  }
})();