/* Robotix — comportements communs à toutes les pages */
(function () {
    'use strict';

    // Ombre sous la barre de navigation dès que l'on fait défiler la page
    const barre = document.querySelector('.navbar');
    const majOmbre = () => barre && barre.classList.toggle('defile', window.scrollY > 20);
    window.addEventListener('scroll', majOmbre, { passive: true });
    majOmbre();

    // Sur mobile, refermer le menu après le choix d'une rubrique
    document.querySelectorAll('#menu .nav-link[href]').forEach((lien) => {
        lien.addEventListener('click', () => {
            const menu = document.getElementById('menu');
            if (menu.classList.contains('show')) {
                bootstrap.Collapse.getOrCreateInstance(menu).hide();
            }
        });
    });

    // Confirmation des actions sensibles : <form data-confirm="Message ?">
    document.addEventListener('submit', (evenement) => {
        const message = evenement.target.dataset.confirm;
        if (message && !window.confirm(message)) {
            evenement.preventDefault();
        }
    });

    // Listes déroulantes de filtre : la page se recharge dès que le choix change
    document.querySelectorAll('select[data-envoi-auto]').forEach((liste) => {
        liste.addEventListener('change', () => liste.form.submit());
    });
})();
