/* Robotix — consentement aux cookies (RGPD) : bandeau, choix mémorisé 6 mois, contenus tiers bloqués */
(function (racine) {
    'use strict';

    const NOM = 'robotix_consentement';
    const VERSION = 1;
    const DUREE = 60 * 60 * 24 * 182; // environ 6 mois, en secondes

    function lire(chaineCookie) {
        const morceau = (chaineCookie || '').split(';').map((s) => s.trim()).find((s) => s.startsWith(NOM + '='));
        if (!morceau) return null;
        try {
            const valeur = JSON.parse(decodeURIComponent(morceau.slice(NOM.length + 1)));
            const valide = valeur && valeur.v === VERSION
                && ['preferences', 'statistiques', 'tiers'].every((c) => typeof valeur[c] === 'boolean');
            return valide ? valeur : null;
        } catch (e) {
            return null;
        }
    }

    const choix = (preferences, statistiques, tiers, date) =>
        ({ v: VERSION, preferences: !!preferences, statistiques: !!statistiques, tiers: !!tiers, date });
    const toutAccepter = (date) => choix(true, true, true, date);
    const toutRefuser = (date) => choix(false, false, false, date);
    const serialiser = (valeur) =>
        NOM + '=' + encodeURIComponent(JSON.stringify(valeur)) + '; Max-Age=' + DUREE + '; Path=/; SameSite=Lax';

    const api = { NOM, lire, choix, toutAccepter, toutRefuser, serialiser };
    if (typeof module === 'object' && module.exports) {
        module.exports = api;
        return;
    }

    const bandeau = document.getElementById('cookies');
    const aujourdhui = () => new Date().toISOString().slice(0, 10);
    const actuel = () => lire(document.cookie);
    const autorise = (categorie) => { const c = actuel(); return !!(c && c[categorie]); };

    // Charge les contenus tiers (Google Maps) seulement si l'internaute les a acceptés
    function appliquer() {
        if (!autorise('tiers')) return;
        document.querySelectorAll('iframe[data-src]').forEach((cadre) => {
            cadre.src = cadre.dataset.src;
            cadre.removeAttribute('data-src');
        });
        document.querySelectorAll('[data-contenu-tiers]').forEach((bloc) => bloc.classList.add('autorise'));
    }

    function afficherOnglet(nom) {
        bandeau.querySelectorAll('[role="tab"]').forEach((onglet) => {
            const actif = onglet.dataset.onglet === nom;
            onglet.setAttribute('aria-selected', String(actif));
            onglet.classList.toggle('active', actif);
            document.getElementById(onglet.getAttribute('aria-controls')).hidden = !actif;
        });
    }

    function ouvrir() {
        const c = actuel();
        ['preferences', 'statistiques', 'tiers'].forEach((categorie) => {
            document.getElementById('cookie-' + categorie).checked = !!(c && c[categorie]);
        });
        afficherOnglet('consentement');
        bandeau.hidden = false;
    }

    function enregistrer(valeur) {
        document.cookie = serialiser(valeur);
        bandeau.hidden = true;
        appliquer();
    }

    document.addEventListener('click', (evenement) => {
        const bouton = evenement.target.closest('[data-cookies], [data-onglet]');
        if (!bouton) return;
        if (bouton.dataset.onglet) return afficherOnglet(bouton.dataset.onglet);

        switch (bouton.dataset.cookies) {
            case 'tout': return enregistrer(toutAccepter(aujourdhui()));
            case 'refuser': return enregistrer(toutRefuser(aujourdhui()));
            case 'personnaliser': return afficherOnglet('details');
            case 'ouvrir': return ouvrir();
            case 'enregistrer':
                return enregistrer(choix(
                    document.getElementById('cookie-preferences').checked,
                    document.getElementById('cookie-statistiques').checked,
                    document.getElementById('cookie-tiers').checked,
                    aujourdhui(),
                ));
            case 'autoriser-tiers': {
                const c = actuel() || toutRefuser(aujourdhui());
                return enregistrer(choix(c.preferences, c.statistiques, true, aujourdhui()));
            }
        }
    });

    racine.RobotixConsentement = { autorise, ouvrir };
    if (bandeau && !actuel()) ouvrir();
    appliquer();
})(this);
