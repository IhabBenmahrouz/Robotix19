/* Robotix — galerie de la fiche robot : vignettes (div) et visionneuse (popup) */
(function (racine) {
    'use strict';

    const indexSuivant = (i, n) => (i + 1) % n;
    const indexPrecedent = (i, n) => (i - 1 + n) % n;

    const api = { indexSuivant, indexPrecedent };
    if (typeof module === 'object' && module.exports) {
        module.exports = api;
        return;
    }
    racine.Galerie = api;

    const galerie = document.querySelector('[data-galerie]');
    const lightbox = document.getElementById('lightbox');
    if (!galerie || !lightbox) {
        return;
    }

    const vignettes = Array.from(galerie.querySelectorAll('.galerie__vignette'));
    const principale = document.getElementById('image-principale');
    const imageGrande = document.getElementById('lightbox-image');
    const legende = document.getElementById('lightbox-legende');
    let courant = 0;
    let declencheur = null;

    function afficher(index) {
        courant = index;
        const vignette = vignettes[index];
        principale.src = vignette.dataset.src;
        principale.alt = vignette.dataset.legende;
        imageGrande.src = vignette.dataset.src;
        imageGrande.alt = vignette.dataset.legende;
        legende.textContent = vignette.dataset.legende + ' (' + (index + 1) + '/' + vignettes.length + ')';
        vignettes.forEach((v, i) => v.classList.toggle('active', i === index));
    }

    function ouvrir() {
        declencheur = document.activeElement;
        afficher(courant);
        lightbox.hidden = false;
        document.body.classList.add('sans-defilement');
        lightbox.querySelector('[data-lightbox="fermer"]').focus();
    }

    function fermer() {
        lightbox.hidden = true;
        document.body.classList.remove('sans-defilement');
        if (declencheur) {
            declencheur.focus();
        }
    }

    vignettes.forEach((vignette, index) => vignette.addEventListener('click', () => afficher(index)));
    galerie.querySelector('[data-lightbox="ouvrir"]').addEventListener('click', ouvrir);

    lightbox.addEventListener('click', (evenement) => {
        const action = evenement.target.dataset.lightbox;
        if (action === 'fermer' || evenement.target === lightbox) fermer();
        if (action === 'suivant') afficher(indexSuivant(courant, vignettes.length));
        if (action === 'precedent') afficher(indexPrecedent(courant, vignettes.length));
    });

    document.addEventListener('keydown', (evenement) => {
        if (lightbox.hidden) return;
        if (evenement.key === 'Escape') fermer();
        if (evenement.key === 'ArrowRight') afficher(indexSuivant(courant, vignettes.length));
        if (evenement.key === 'ArrowLeft') afficher(indexPrecedent(courant, vignettes.length));
    });
})(this);
