<div class="cookies" id="cookies" role="dialog" aria-labelledby="cookies-titre" hidden>
    <div class="cookies__onglets" role="tablist" aria-label="Informations sur les cookies">
        <button type="button" class="cookies__onglet active" role="tab" id="onglet-consentement" aria-selected="true" aria-controls="panneau-consentement" data-onglet="consentement">Consentement</button>
        <button type="button" class="cookies__onglet" role="tab" id="onglet-details" aria-selected="false" aria-controls="panneau-details" data-onglet="details">Détails</button>
        <button type="button" class="cookies__onglet" role="tab" id="onglet-apropos" aria-selected="false" aria-controls="panneau-apropos" data-onglet="apropos">À propos des cookies</button>
    </div>

    <div class="cookies__panneau" role="tabpanel" id="panneau-consentement" aria-labelledby="onglet-consentement">
        <h2 class="h6" id="cookies-titre">Ce site web utilise des cookies.</h2>
        <p>Robotix utilise des cookies nécessaires au fonctionnement du site (session, sécurité des formulaires) et, avec votre accord,
           des cookies de préférences, de mesure d'audience et de contenus tiers (carte Google Maps des showrooms).</p>
    </div>

    <div class="cookies__panneau" role="tabpanel" id="panneau-details" aria-labelledby="onglet-details" hidden>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="cookie-necessaires" checked disabled>
            <label class="form-check-label" for="cookie-necessaires"><strong>Nécessaires</strong> — session et protection CSRF, toujours actifs.</label>
        </div>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="cookie-preferences">
            <label class="form-check-label" for="cookie-preferences"><strong>Préférences</strong> — mémorisation de vos choix d'affichage.</label>
        </div>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="cookie-statistiques">
            <label class="form-check-label" for="cookie-statistiques"><strong>Statistiques</strong> — mesure d'audience anonyme.</label>
        </div>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="cookie-tiers">
            <label class="form-check-label" for="cookie-tiers"><strong>Contenus tiers</strong> — carte Google Maps (Google peut déposer ses propres cookies).</label>
        </div>
        <button type="button" class="btn btn-sm btn-robotix mt-2" data-cookies="enregistrer">Enregistrer mes choix</button>
    </div>

    <div class="cookies__panneau" role="tabpanel" id="panneau-apropos" aria-labelledby="onglet-apropos" hidden>
        <p>Un cookie est un petit fichier enregistré par votre navigateur. Conformément au RGPD et aux recommandations de la CNIL,
           votre choix est conservé 6 mois dans le cookie <code>robotix_consentement</code> ; vous pouvez le modifier à tout moment
           avec le lien « Gérer les cookies » en bas de page. Refuser est aussi simple qu'accepter.</p>
    </div>

    <div class="cookies__actions">
        <button type="button" class="btn btn-outline-light" data-cookies="refuser">Refuser</button>
        <button type="button" class="btn btn-outline-light" data-cookies="personnaliser">Personnaliser ›</button>
        <button type="button" class="btn btn-robotix" data-cookies="tout">Tout autoriser</button>
    </div>
</div>
