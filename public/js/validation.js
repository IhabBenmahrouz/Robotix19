/* Robotix — contrôle des formulaires : attributs HTML5 (pattern, required, type) + expressions régulières */
(function (racine) {
    'use strict';

    const LETTRES = "A-Za-zÀ-ÖØ-öø-ÿ' -";

    const REGLES = {
        nom: new RegExp('^[' + LETTRES + ']{2,50}$'),
        ville: new RegExp('^[' + LETTRES + ']{2,80}$'),
        email: /^[^\s@]+@[^\s@]+\.[A-Za-z]{2,}$/,
        telephone: /^0[1-9](?:[ .-]?\d{2}){4}$/,
        codePostal: /^\d{5}$/,
        adresse: /^.{5,120}$/,
        motDePasse: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/,
        message: /^[\s\S]{20,2000}$/,
    };

    function valider(regle, valeur) {
        const motif = REGLES[regle];
        return motif ? motif.test(String(valeur).trim()) : true;
    }

    const api = { REGLES, valider };
    if (typeof module === 'object' && module.exports) {
        module.exports = api;
        return;
    }
    racine.Validation = api;

    function verifierChamp(champ) {
        const valeur = champ.type === 'checkbox' ? champ.checked : champ.value;
        let valide = champ.checkValidity(); // required, type, pattern, minlength… (HTML5)

        if (valide && champ.dataset.regle && champ.value !== '') {
            valide = valider(champ.dataset.regle, valeur); // expression régulière JS
        }
        if (valide && champ.dataset.identique) {
            valide = champ.value === document.getElementById(champ.dataset.identique).value;
        }

        champ.classList.toggle('is-invalid', !valide);
        champ.classList.toggle('is-valid', valide && champ.value !== '');
        champ.setAttribute('aria-invalid', String(!valide));
        return valide;
    }

    document.querySelectorAll('form[data-valider]').forEach((formulaire) => {
        const champs = Array.from(formulaire.querySelectorAll('input, select, textarea'))
            .filter((c) => c.type !== 'hidden' && c.type !== 'submit');

        champs.forEach((champ) => {
            // Contrôle en direct, une fois que l'utilisateur a quitté le champ une première fois
            champ.addEventListener('blur', () => { champ.dataset.touche = '1'; verifierChamp(champ); });
            champ.addEventListener('input', () => { if (champ.dataset.touche) verifierChamp(champ); });
            champ.addEventListener('change', () => verifierChamp(champ));
        });

        formulaire.addEventListener('submit', (evenement) => {
            const invalides = champs.filter((champ) => !verifierChamp(champ));
            if (invalides.length > 0) {
                evenement.preventDefault();
                invalides[0].focus();
            }
        });
    });
})(this);
