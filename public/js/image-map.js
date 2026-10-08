/* Robotix — image réactive : zones <area> redimensionnées, survol (div) et clic (popup) */
(function (racine) {
    'use strict';

    /** « x1,y1,x2,y2 » multipliées par ratio, arrondies au pixel. */
    function mettreAEchelle(coords, ratio) {
        return coords.split(',').map((valeur) => Math.round(Number(valeur) * ratio)).join(',');
    }

    const api = { mettreAEchelle };
    if (typeof module === 'object' && module.exports) {
        module.exports = api;
        return;
    }
    racine.ImageMap = api;

    function initialiser(bloc) {
        const image = bloc.querySelector('img[usemap]');
        const zonesHtml = bloc.querySelectorAll('area');
        const surbrillance = bloc.querySelector('.image-map__surbrillance');
        const info = document.getElementById('zone-info');
        const zones = JSON.parse(document.getElementById('zones-robot').textContent);
        const modale = new bootstrap.Modal(document.getElementById('modale-zone'));

        zonesHtml.forEach((zone) => { zone.dataset.coordsOrigine = zone.getAttribute('coords'); });

        // Les coordonnées <area> sont en pixels : on les recalcule selon la taille affichée
        function redimensionner() {
            const ratio = image.clientWidth / (image.naturalWidth || 600);
            zonesHtml.forEach((zone) => { zone.coords = mettreAEchelle(zone.dataset.coordsOrigine, ratio); });
        }

        function surligner(zone) {
            const [x1, y1, x2, y2] = zone.coords.split(',').map(Number);
            Object.assign(surbrillance.style, { left: x1 + 'px', top: y1 + 'px', width: (x2 - x1) + 'px', height: (y2 - y1) + 'px' });
            surbrillance.hidden = false;
        }

        function afficherApercu(cle) {
            const zone = zones[cle];
            const titre = document.createElement('h3');
            const texte = document.createElement('p');
            titre.className = 'h5';
            titre.textContent = zone.titre;
            texte.className = 'mb-0';
            texte.textContent = zone.resume + ' Cliquez pour en savoir plus.';
            info.replaceChildren(titre, texte);
        }

        function ouvrirModale(cle) {
            document.getElementById('modale-zone-titre').textContent = zones[cle].titre;
            document.getElementById('modale-zone-texte').textContent = zones[cle].detail;
            modale.show();
        }

        zonesHtml.forEach((zone) => {
            const entrer = () => { surligner(zone); afficherApercu(zone.dataset.zone); };
            zone.addEventListener('mouseenter', entrer);
            zone.addEventListener('focus', entrer);
            zone.addEventListener('mouseleave', () => { surbrillance.hidden = true; });
            zone.addEventListener('blur', () => { surbrillance.hidden = true; });
            zone.addEventListener('click', (evenement) => {
                evenement.preventDefault();
                ouvrirModale(zone.dataset.zone);
            });
        });

        // Boutons équivalents (accessibles au clavier et sur mobile)
        document.querySelectorAll('[data-zone-bouton]').forEach((bouton) => {
            bouton.addEventListener('click', () => ouvrirModale(bouton.dataset.zoneBouton));
        });

        window.addEventListener('resize', redimensionner);
        if (image.complete) {
            redimensionner();
        } else {
            image.addEventListener('load', redimensionner);
        }
    }

    document.querySelectorAll('[data-image-map]').forEach(initialiser);
})(this);
