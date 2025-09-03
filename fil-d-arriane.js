// Fil d'Ariane dynamique en JS
// À inclure dans le header/footer de ton thème

(function() {

  debug.log('Fil d\'Ariane script loaded');

  // Utilise le localStorage pour stocker l'historique
  const STORAGE_KEY = 'fil_ariane_pages';

  // Récupère l'historique ou initialise
  function getAriane() {
    try {
      return JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
    } catch(e) {
      return [];
    }
  }

  // Ajoute la page courante à l'historique
  function addCurrentPage() {
    const title = document.title;
    const url = window.location.href;
    let ariane = getAriane();
    // Si la page est déjà dans l'historique, on coupe à cette position
    const idx = ariane.findIndex(p => p.url === url);
    if (idx !== -1) {
      ariane = ariane.slice(0, idx);
    }
    ariane.push({title, url});
    localStorage.setItem(STORAGE_KEY, JSON.stringify(ariane));
  }

  // Réinitialise sur la page d'accueil
  function resetOnHome() {
    if (window.location.pathname === '/' || window.location.pathname === '/index.php') {
      localStorage.removeItem(STORAGE_KEY);
    }
  }

  // Affiche le fil d'Ariane
  function renderAriane() {
    const ariane = getAriane();
    if (!ariane.length) return;
    const container = document.createElement('nav');
    container.className = 'fil-ariane-js';
    const ul = document.createElement('ul');
    ul.style.display = 'flex';
    ul.style.flexWrap = 'wrap';
    ul.style.gap = '8px';
    // Flèche retour
    if (ariane.length > 1) {
      const prev = ariane[ariane.length-2];
      const li = document.createElement('li');
      li.innerHTML = `<a href="${prev.url}" style="display:flex;align-items:center;color:#FF3F22;font-weight:700;text-decoration:none;"><span style="font-size:18px;margin-right:6px;">&#8592;</span>Retour</a>`;
      ul.appendChild(li);
    }
    ariane.forEach((p, i) => {
      const li = document.createElement('li');
      li.innerHTML = `<a href="${p.url}" style="color:#FF3F22;font-weight:700;text-decoration:none;">${p.title}</a>`;
      if (i < ariane.length-1) {
        li.innerHTML += ' <span style="color:#FF3F22;">&rsaquo;</span> ';
      }
      ul.appendChild(li);
    });
    container.appendChild(ul);
    // Ajoute dans le DOM (par exemple avant le contenu principal)
    const main = document.querySelector('main') || document.body;
    main.insertAdjacentElement('afterbegin', container);
  }

  // Initialisation
  resetOnHome();
  addCurrentPage();
  document.addEventListener('DOMContentLoaded', renderAriane);
})();
