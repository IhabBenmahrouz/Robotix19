<footer class="site-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <p class="logo mb-2">ROBOTIX<span>.</span></p>
                <p>Robots humanoïdes pour les particuliers : vente, conseils et démonstrations dans nos showrooms.</p>
            </div>
            <div class="col-6 col-md-4">
                <h2 class="h6 text-white">Navigation</h2>
                <ul class="list-unstyled">
                    <li><a href="<?= site_url('store') ?>">Robotix Store</a></li>
                    <li><a href="<?= site_url('news') ?>">Robotix News</a></li>
                    <li><a href="<?= site_url('tarifs') ?>">Tarifs</a></li>
                    <li><a href="<?= site_url('evenements') ?>">Événements</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-4">
                <h2 class="h6 text-white">Nous trouver</h2>
                <ul class="list-unstyled">
                    <li><a href="<?= site_url('showrooms') ?>">Nos showrooms</a></li>
                    <li><a href="<?= site_url('contact') ?>">Contact</a></li>
                    <li><a href="<?= site_url('credits') ?>">Crédits photos</a></li>
                    <li><a href="<?= site_url('a-propos') ?>">À propos du projet</a></li>
                    <li><button class="lien-bouton" type="button" data-cookies="ouvrir">Gérer les cookies</button></li>
                </ul>
            </div>
        </div>
        <p class="site-footer__mentions">
            © <?= date('Y') ?> Robotix — site fictif réalisé dans le cadre du BTS SIO (AP1) par Ihab Benmahrouz.
            Les prix sont donnés à titre d'exemple.
        </p>
    </div>
</footer>
