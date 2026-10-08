/* Robotix — changement de formule d'adhésion sans rechargement (fetch + JSON) */
(function () {
    'use strict';

    const formulaire = document.getElementById('changer-formule');
    if (!formulaire) return;

    const message = document.getElementById('message-formule');
    let precedent = formulaire.querySelector('input[name="idTarif"]:checked');

    formulaire.addEventListener('change', async (evenement) => {
        const choix = evenement.target;
        message.textContent = 'Enregistrement…';

        try {
            const reponse = await fetch(formulaire.dataset.url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': formulaire.dataset.csrf,
                },
                body: JSON.stringify({ idTarif: Number(choix.value) }),
            });
            const json = await reponse.json();
            if (json.csrf) formulaire.dataset.csrf = json.csrf;
            if (!reponse.ok || !json.succes) throw new Error(json.message || 'Erreur');

            document.getElementById('formule-actuelle').textContent = json.formule;
            document.getElementById('montant-adhesion').textContent = json.montantTexte;
            message.textContent = 'Formule ' + json.formule + ' enregistrée : ' + json.montantTexte + '.';
            precedent = choix;
        } catch (erreur) {
            message.textContent = 'Le changement n\'a pas pu être enregistré. ' + erreur.message;
            if (precedent) precedent.checked = true;
        }
    });

    formulaire.addEventListener('submit', (evenement) => evenement.preventDefault());
})();
