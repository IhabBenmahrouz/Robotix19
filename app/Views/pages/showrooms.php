<?php
$premier = $showrooms[0] ?? null;
$urlCarte = static fn (array $s): string => 'https://maps.google.com/maps?q=' . (float) $s['latitude'] . ',' . (float) $s['longitude'] . '&z=15&output=embed';
?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="en-tete-page">
    <div class="container">
        <h1>Nos showrooms</h1>
        <p>Venez essayer nos robots à Paris, Lyon et Marseille.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="showrooms" id="showrooms" data-api="<?= site_url('api/showrooms') ?>">
                    <button type="button" class="btn btn-robotix w-100 mb-2" id="btn-proche">📍 Showroom le plus proche</button>
                    <p class="small text-secondary" id="message-geo" aria-live="polite">Votre position n'est utilisée que dans votre navigateur.</p>
                    <ul class="showrooms__liste" id="liste-showrooms">
                        <?php foreach ($showrooms as $showroom): ?>
                            <li><?= esc($showroom['nom']) ?> — <?= esc($showroom['adresse']) ?>, <?= esc($showroom['codePostal']) ?> <?= esc($showroom['ville']) ?></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="carte" data-contenu-tiers>
                    <?php if ($premier !== null): ?>
                        <iframe id="carte-google" class="carte__iframe" title="Carte Google Maps du showroom sélectionné"
                                data-src="<?= esc($urlCarte($premier)) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                    <div class="carte__consentement">
                        <p>La carte est fournie par Google Maps, qui peut déposer des cookies.</p>
                        <button type="button" class="btn btn-robotix" data-cookies="autoriser-tiers">Autoriser Google Maps</button>
                    </div>
                    <?php endif ?>
                    <div class="carte__zoom" role="group" aria-label="Zoom de la carte">
                        <button type="button" class="btn btn-light" id="zoom-plus" aria-label="Zoomer">+</button>
                        <button type="button" class="btn btn-light" id="zoom-moins" aria-label="Dézoomer">−</button>
                    </div>
                </div>
                <div class="carte__info" id="info-showroom" aria-live="polite">
                    <?php if ($premier !== null): ?>
                        <h2 class="h5"><?= esc($premier['nom']) ?></h2>
                        <p class="mb-0"><?= esc($premier['adresse']) ?>, <?= esc($premier['codePostal']) ?> <?= esc($premier['ville']) ?></p>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/carte.js') ?>"></script>
<?= $this->endSection() ?>
