/* Robotix — calendrier mensuel des événements (données : api/evenements) */
(function (racine) {
    'use strict';

    const MOIS = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

    /** 42 cases (6 semaines) commençant un lundi, calculées en UTC pour éviter les décalages d'heure d'été. */
    function grilleMois(annee, mois) {
        const premier = new Date(Date.UTC(annee, mois - 1, 1));
        const decalage = (premier.getUTCDay() + 6) % 7; // lundi = 0
        const cases = [];
        for (let i = 0; i < 42; i++) {
            const d = new Date(Date.UTC(annee, mois - 1, 1 - decalage + i));
            cases.push({ date: d.toISOString().slice(0, 10), jour: d.getUTCDate(), dansLeMois: d.getUTCMonth() === mois - 1 });
        }
        return cases;
    }

    function decalerMois(annee, mois, delta) {
        const d = new Date(Date.UTC(annee, mois - 1 + delta, 1));
        return { annee: d.getUTCFullYear(), mois: d.getUTCMonth() + 1 };
    }

    const evenementsDuJour = (evenements, dateIso) =>
        evenements.filter((e) => e.debut.slice(0, 10) <= dateIso && e.fin.slice(0, 10) >= dateIso);

    const filtrer = (evenements, type, idShowroom) =>
        evenements.filter((e) => (!type || e.type === type) && (!idShowroom || e.idShowroom === Number(idShowroom)));

    const cleMois = (annee, mois) => annee + '-' + String(mois).padStart(2, '0');

    const titreMois = (annee, mois) => {
        const nom = MOIS[mois - 1];
        return nom.charAt(0).toUpperCase() + nom.slice(1) + ' ' + annee;
    };

    const api = { grilleMois, decalerMois, evenementsDuJour, filtrer, cleMois, titreMois };
    if (typeof module === 'object' && module.exports) {
        module.exports = api;
        return;
    }
    racine.Calendrier = api;

    const conteneur = document.getElementById('calendrier');
    if (!conteneur) return;

    const corps = document.getElementById('calendrier-corps');
    const titre = document.getElementById('calendrier-titre');
    const erreur = document.getElementById('calendrier-erreur');
    const detail = document.getElementById('detail-jour');
    const filtreType = document.getElementById('filtre-type');
    const filtreShowroom = document.getElementById('filtre-showroom');
    const aujourdhui = new Date();
    const cleAujourdhui = cleMois(aujourdhui.getFullYear(), aujourdhui.getMonth() + 1) + '-' + String(aujourdhui.getDate()).padStart(2, '0');

    const [anneeInitiale, moisInitial] = conteneur.dataset.mois.split('-').map(Number);
    const etat = { annee: anneeInitiale, mois: moisInitial, evenements: [], jourChoisi: null };

    const heure = (iso) => new Date(iso).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
    const dateLongue = (iso) => new Date(iso + 'T12:00:00').toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' });

    function element(balise, classe, texte) {
        const el = document.createElement(balise);
        if (classe) el.className = classe;
        if (texte !== undefined) el.textContent = texte;
        return el;
    }

    async function charger() {
        titre.textContent = titreMois(etat.annee, etat.mois);
        erreur.hidden = true;
        try {
            const reponse = await fetch(conteneur.dataset.api + '?mois=' + cleMois(etat.annee, etat.mois));
            if (!reponse.ok) throw new Error('HTTP ' + reponse.status);
            etat.evenements = await reponse.json();
        } catch (e) {
            etat.evenements = [];
            erreur.textContent = 'Impossible de charger les événements. Réessayez plus tard.';
            erreur.hidden = false;
        }
        dessiner();
    }

    function visibles() {
        return filtrer(etat.evenements, filtreType.value, filtreShowroom.value);
    }

    function dessiner() {
        const evenements = visibles();
        const cases = grilleMois(etat.annee, etat.mois);
        corps.replaceChildren();

        for (let semaine = 0; semaine < 6; semaine++) {
            const ligne = document.createElement('tr');
            cases.slice(semaine * 7, semaine * 7 + 7).forEach((c) => {
                const cellule = document.createElement('td');
                const bouton = element('button', 'calendrier__jour');
                const duJour = evenementsDuJour(evenements, c.date);
                bouton.type = 'button';
                bouton.dataset.date = c.date;
                bouton.classList.toggle('hors-mois', !c.dansLeMois);
                bouton.classList.toggle('aujourdhui', c.date === cleAujourdhui);
                bouton.classList.toggle('choisi', c.date === etat.jourChoisi);
                bouton.setAttribute('aria-label', dateLongue(c.date) + ', ' + duJour.length + ' événement(s)');
                bouton.append(element('span', 'calendrier__numero', String(c.jour)));
                const pastilles = element('span', 'calendrier__pastilles');
                duJour.forEach((e) => pastilles.append(element('span', 'pastille pastille--' + e.type)));
                bouton.append(pastilles);
                bouton.addEventListener('click', () => { etat.jourChoisi = c.date; dessiner(); afficherDetail(c.date); });
                cellule.append(bouton);
                ligne.append(cellule);
            });
            corps.append(ligne);
        }
    }

    function afficherDetail(dateIso) {
        const duJour = evenementsDuJour(visibles(), dateIso);
        const titreDetail = element('h2', 'h5', dateLongue(dateIso));
        detail.replaceChildren(titreDetail);

        if (duJour.length === 0) {
            detail.append(element('p', 'mb-0', 'Aucun événement ce jour-là.'));
            return;
        }

        duJour.forEach((e) => {
            const carte = element('article', 'detail-jour__evenement');
            const type = element('p', 'mb-1 small');
            type.append(element('span', 'pastille pastille--' + e.type), document.createTextNode(e.typeLibelle));
            carte.append(type, element('h3', 'h6', e.titre));
            carte.append(element('p', 'mb-1', heure(e.debut) + ' – ' + heure(e.fin) + ' · ' + (e.showroom || 'Hors showroom')));
            if (e.description) carte.append(element('p', 'mb-1 text-secondary', e.description));
            if (e.urlProduit) {
                const lien = element('a', '', 'Voir le robot ' + e.produit + ' →');
                lien.href = e.urlProduit;
                carte.append(lien);
            }
            detail.append(carte);
        });
    }

    function changerMois(delta) {
        Object.assign(etat, decalerMois(etat.annee, etat.mois, delta), { jourChoisi: null });
        charger();
    }

    document.getElementById('mois-precedent').addEventListener('click', () => changerMois(-1));
    document.getElementById('mois-suivant').addEventListener('click', () => changerMois(1));
    document.getElementById('mois-courant').addEventListener('click', () => {
        Object.assign(etat, { annee: aujourdhui.getFullYear(), mois: aujourdhui.getMonth() + 1, jourChoisi: cleAujourdhui });
        charger().then(() => afficherDetail(cleAujourdhui));
    });
    [filtreType, filtreShowroom].forEach((filtre) => filtre.addEventListener('change', () => {
        dessiner();
        if (etat.jourChoisi) afficherDetail(etat.jourChoisi);
    }));

    charger();
})(this);
