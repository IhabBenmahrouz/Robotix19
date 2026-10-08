/* Robotix — prix de l'adhésion au Club selon l'âge (catégorie et réduction) */
(function (racine) {
    'use strict';

    /** Âge en années révolues ; dates au format AAAA-MM-JJ. */
    function age(naissanceIso, aujourdhuiIso) {
        const [an, mois, jour] = naissanceIso.split('-').map(Number);
        const [an2, mois2, jour2] = aujourdhuiIso.split('-').map(Number);
        let resultat = an2 - an;
        if (mois2 < mois || (mois2 === mois && jour2 < jour)) resultat--;
        return resultat;
    }

    const categorie = (valeurAge, categories) =>
        categories.find((c) => valeurAge >= c.ageMin && valeurAge <= c.ageMax) || null;

    const montant = (valeur, taux) => Math.round(valeur * (100 - taux)) / 100;

    const api = { age, categorie, montant };
    if (typeof module === 'object' && module.exports) {
        module.exports = api;
        return;
    }
    racine.Adhesion = api;

    const formulaire = document.querySelector('[data-adhesion]');
    if (!formulaire) return;

    const donnees = JSON.parse(document.getElementById('donnees-adhesion').textContent);
    const champNaissance = document.getElementById('dateNaissance');
    const resume = document.getElementById('resume-adhesion');
    const euros = (x) => x.toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' });

    function mettreAJour() {
        const aujourdhui = new Date().toISOString().slice(0, 10);
        const cat = champNaissance.value ? categorie(age(champNaissance.value, aujourdhui), donnees.categories) : null;

        formulaire.querySelectorAll('[data-prix-formule]').forEach((prix) => {
            const valeur = Number(prix.dataset.prixFormule);
            prix.textContent = euros(cat ? montant(valeur, cat.txReduction) : valeur);
        });

        if (!champNaissance.value) {
            resume.textContent = 'Indiquez votre date de naissance pour connaître votre catégorie.';
        } else if (!cat) {
            resume.textContent = 'L\'adhésion au Club Robotix est réservée aux personnes majeures.';
        } else {
            resume.textContent = 'Catégorie ' + cat.libelle + (cat.txReduction > 0 ? ' : ' + cat.txReduction + ' % de réduction appliquée.' : ' : tarif plein.');
        }
    }

    champNaissance.addEventListener('change', mettreAJour);
    champNaissance.addEventListener('input', mettreAJour);
    mettreAJour();
})(this);
