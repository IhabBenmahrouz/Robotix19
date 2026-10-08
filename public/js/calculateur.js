/* Robotix — calculateur de prix : robot + options + quantité + financement */
(function (racine) {
    'use strict';

    const QUANTITE_MIN = 1;
    const QUANTITE_MAX = 5;

    const arrondir = (x) => Math.round(x * 100) / 100;

    /** Mensualité d'un prêt amortissable ; 1 mois = paiement comptant. */
    function mensualiteCredit(capital, tauxAnnuel, mois) {
        if (mois <= 1) return arrondir(capital);
        const t = tauxAnnuel / 100 / 12;
        if (t === 0) return arrondir(capital / mois);
        return arrondir((capital * t) / (1 - Math.pow(1 + t, -mois)));
    }

    function calculer(entree) {
        const optionsUnitaires = entree.options.reduce(
            (somme, option) => somme + (option.type === 'pourcentage' ? (entree.prixHt * option.valeur) / 100 : option.valeur),
            0,
        );
        const totalHt = arrondir((entree.prixHt + optionsUnitaires) * entree.quantite);
        const tva = arrondir((totalHt * entree.tauxTva) / 100);
        const totalTtc = arrondir(totalHt + tva);
        const mensualite = mensualiteCredit(totalTtc, entree.tauxAnnuel, entree.mois);
        const coutCredit = Math.max(0, arrondir(mensualite * Math.max(1, entree.mois) - totalTtc));

        return { optionsHt: arrondir(optionsUnitaires * entree.quantite), totalHt, tva, totalTtc, mensualite, coutCredit };
    }

    function quantiteValide(valeur) {
        return /^\d+$/.test(String(valeur).trim()) && Number(valeur) >= QUANTITE_MIN && Number(valeur) <= QUANTITE_MAX;
    }

    function normaliserQuantite(valeur) {
        const n = parseInt(valeur, 10);
        if (Number.isNaN(n) || n < QUANTITE_MIN) return QUANTITE_MIN;
        return Math.min(n, QUANTITE_MAX);
    }

    const api = { calculer, mensualiteCredit, quantiteValide, normaliserQuantite };
    if (typeof module === 'object' && module.exports) {
        module.exports = api;
        return;
    }
    racine.Calculateur = api;

    const formulaire = document.getElementById('calculateur');
    if (!formulaire) return;

    const donnees = JSON.parse(document.getElementById('donnees-tarifs').textContent);
    const champQuantite = document.getElementById('quantite');
    const lienDevis = document.getElementById('lien-devis');
    const euros = (x) => x.toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' });
    const ecrire = (id, texte) => { document.getElementById(id).textContent = texte; };

    function mettreAJour() {
        const robot = donnees.robots.find((r) => r.idProduit === Number(formulaire.robot.value));
        const valide = quantiteValide(champQuantite.value);
        champQuantite.classList.toggle('is-invalid', !valide);
        const quantite = normaliserQuantite(champQuantite.value);

        const codes = Array.from(formulaire.querySelectorAll('input[name="options"]:checked')).map((c) => c.value);
        const options = donnees.options.filter((o) => codes.includes(o.code));
        const financement = donnees.financements.find((f) => f.mois === Number(formulaire.financement.value));

        const r = calculer({
            prixHt: robot.prixHt, tauxTva: robot.tauxTva, quantite, options,
            mois: financement.mois, tauxAnnuel: financement.taux,
        });

        ecrire('resultat-robot', euros(robot.prixHt * quantite));
        ecrire('resultat-options', euros(r.optionsHt));
        ecrire('resultat-ht', euros(r.totalHt));
        ecrire('resultat-tva', euros(r.tva));
        ecrire('resultat-ttc', euros(r.totalTtc));
        ecrire('resultat-mensualite', financement.mois > 1 ? euros(r.mensualite) + ' × ' + financement.mois + ' mois' : 'Paiement comptant');
        ecrire('resultat-cout', euros(r.coutCredit));
        lienDevis.href = lienDevis.href.split('?')[0] + '?objet=devis&robot=' + robot.idProduit;
    }

    formulaire.addEventListener('input', mettreAJour);
    formulaire.addEventListener('change', mettreAJour);
    champQuantite.addEventListener('blur', () => { champQuantite.value = normaliserQuantite(champQuantite.value); mettreAJour(); });
    formulaire.addEventListener('submit', (evenement) => evenement.preventDefault());
    mettreAJour();
})(this);
