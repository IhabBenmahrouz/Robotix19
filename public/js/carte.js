/* Robotix — gestion de la carte Google Maps des showrooms */
(function (racine) {
    'use strict';

    const ZOOM_MIN = 5;
    const ZOOM_MAX = 20;

    const urlCarte = (lat, lng, zoom) => 'https://maps.google.com/maps?q=' + lat + ',' + lng + '&z=' + zoom + '&output=embed';
    const urlItineraire = (lat, lng) => 'https://www.google.com/maps/dir/?api=1&destination=' + lat + ',' + lng;
    const bornerZoom = (z) => Math.min(ZOOM_MAX, Math.max(ZOOM_MIN, Math.round(z)));

    /** Distance à vol d'oiseau en km (formule de haversine). */
    function distanceKm(lat1, lng1, lat2, lng2) {
        const rad = (deg) => (deg * Math.PI) / 180;
        const dLat = rad(lat2 - lat1);
        const dLng = rad(lng2 - lng1);
        const a = Math.sin(dLat / 2) ** 2 + Math.cos(rad(lat1)) * Math.cos(rad(lat2)) * Math.sin(dLng / 2) ** 2;
        return 6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    function plusProche(position, showrooms) {
        return showrooms
            .map((s) => ({ showroom: s, distance: distanceKm(position.lat, position.lng, s.latitude, s.longitude) }))
            .sort((a, b) => a.distance - b.distance)[0];
    }

    const api = { urlCarte, urlItineraire, distanceKm, plusProche, bornerZoom };
    if (typeof module === 'object' && module.exports) {
        module.exports = api;
        return;
    }
    racine.Carte = api;

    const conteneur = document.getElementById('showrooms');
    const iframe = document.getElementById('carte-google');
    if (!conteneur || !iframe) return;

    const liste = document.getElementById('liste-showrooms');
    const info = document.getElementById('info-showroom');
    const messageGeo = document.getElementById('message-geo');
    const etat = { showrooms: [], courant: null, zoom: 15, distances: {} };

    function element(balise, classe, texte) {
        const el = document.createElement(balise);
        if (classe) el.className = classe;
        if (texte !== undefined) el.textContent = texte;
        return el;
    }

    function rafraichirCarte() {
        const url = urlCarte(etat.courant.latitude, etat.courant.longitude, etat.zoom);
        // Tant que les contenus tiers ne sont pas acceptés, l'adresse attend dans data-src
        if (iframe.hasAttribute('data-src')) {
            iframe.dataset.src = url;
        } else {
            iframe.src = url;
        }
    }

    function selectionner(showroom) {
        etat.courant = showroom;
        rafraichirCarte();
        liste.querySelectorAll('button').forEach((b) => b.classList.toggle('active', Number(b.dataset.id) === showroom.id));

        const lien = element('a', 'btn btn-contour btn-sm mt-2', 'Itinéraire');
        lien.href = urlItineraire(showroom.latitude, showroom.longitude);
        lien.target = '_blank';
        lien.rel = 'noopener';
        info.replaceChildren(
            element('h2', 'h5', showroom.nom),
            element('p', 'mb-1', showroom.adresse + ', ' + showroom.codePostal + ' ' + showroom.ville),
            element('p', 'mb-1', '☎ ' + (showroom.telephone || '—') + ' · ' + (showroom.horaires || '')),
            lien,
        );
    }

    function dessinerListe() {
        liste.replaceChildren();
        etat.showrooms.forEach((s) => {
            const bouton = element('button', 'showrooms__item');
            bouton.type = 'button';
            bouton.dataset.id = s.id;
            bouton.append(element('strong', '', s.nom), element('span', 'd-block small', s.adresse + ', ' + s.ville));
            if (etat.distances[s.id] !== undefined) {
                bouton.append(element('span', 'badge text-bg-info', Math.round(etat.distances[s.id]) + ' km'));
            }
            bouton.addEventListener('click', () => selectionner(s));
            const item = document.createElement('li');
            item.append(bouton);
            liste.append(item);
        });
        if (etat.courant) selectionner(etat.courant);
    }

    async function charger() {
        try {
            const reponse = await fetch(conteneur.dataset.api);
            if (!reponse.ok) throw new Error('HTTP ' + reponse.status);
            etat.showrooms = await reponse.json();
            etat.courant = etat.showrooms[0] || null;
            dessinerListe();
        } catch (e) {
            messageGeo.textContent = 'La liste interactive est indisponible ; la carte reste consultable.';
        }
    }

    document.getElementById('zoom-plus').addEventListener('click', () => { etat.zoom = bornerZoom(etat.zoom + 1); if (etat.courant) rafraichirCarte(); });
    document.getElementById('zoom-moins').addEventListener('click', () => { etat.zoom = bornerZoom(etat.zoom - 1); if (etat.courant) rafraichirCarte(); });

    document.getElementById('btn-proche').addEventListener('click', () => {
        if (!('geolocation' in navigator)) {
            messageGeo.textContent = 'Votre navigateur ne permet pas la géolocalisation.';
            return;
        }
        messageGeo.textContent = 'Recherche de votre position…';
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const ici = { lat: position.coords.latitude, lng: position.coords.longitude };
                etat.showrooms.forEach((s) => { etat.distances[s.id] = distanceKm(ici.lat, ici.lng, s.latitude, s.longitude); });
                const resultat = plusProche(ici, etat.showrooms);
                etat.courant = resultat.showroom;
                dessinerListe();
                messageGeo.textContent = 'Le plus proche : ' + resultat.showroom.nom + ' (' + Math.round(resultat.distance) + ' km).';
            },
            () => { messageGeo.textContent = 'Position refusée ou indisponible : choisissez un showroom dans la liste.'; },
            { timeout: 10000 },
        );
    });

    charger();
})(this);
