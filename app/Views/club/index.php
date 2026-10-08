<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="hero">
    <div class="container">
        <p class="hero__surtitre">Ateliers, démonstrations, entraide</p>
        <h1>Club Robotix</h1>
        <p>Le club des passionnés de robots humanoïdes : des ateliers encadrés par nos animateurs pour apprendre à programmer,
           utiliser et faire progresser votre robot.</p>
        <div class="d-flex flex-wrap gap-3 mt-4">
            <a class="btn btn-robotix" href="<?= site_url('compte/inscription') ?>">Adhérer au club</a>
            <a class="btn btn-contour" href="<?= site_url('club/membres') ?>">Espace membre</a>
        </div>
    </div>
</section>

<section class="section" aria-labelledby="titre-chiffres">
    <div class="container">
        <h2 id="titre-chiffres" class="titre-section">Le club en chiffres</h2>
        <div class="row g-4 mt-2 text-center">
            <div class="col-md-4"><article class="univers"><p class="stat__valeur"><?= $nbMembres ?> membres</p><p class="mb-0">inscrits aux ateliers</p></article></div>
            <div class="col-md-4"><article class="univers univers--violet"><p class="stat__valeur"><?= number_format($heures, 0, ',', ' ') ?> h</p><p class="mb-0">d'ateliers suivis</p></article></div>
            <div class="col-md-4"><article class="univers univers--ambre"><p class="stat__valeur"><?= $presences ?> présences</p><p class="mb-0">aux événements du club</p></article></div>
        </div>
    </div>
</section>

<section class="section section--sombre" aria-labelledby="titre-animateurs">
    <div class="container">
        <h2 id="titre-animateurs" class="titre-section">Nos animateurs</h2>
        <div class="row g-4 mt-2">
            <?php foreach ($animateurs as $animateur): ?>
                <div class="col-md-4">
                    <article class="animateur">
                        <img src="<?= base_url('images/ui/avatar.svg') ?>" alt="" width="64" height="64">
                        <h3 class="h5"><?= esc($animateur['prenom'] . ' ' . $animateur['nom']) ?></h3>
                        <p class="mb-1"><?= esc($animateur['specialite']) ?></p>
                        <p class="small mb-0">Remplaçant : <?= esc($animateur['remplacant'] ?? '—') ?></p>
                    </article>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</section>

<section class="section" aria-labelledby="titre-ateliers">
    <div class="container">
        <h2 id="titre-ateliers" class="titre-section">Prochains ateliers</h2>
        <div class="row g-4 mt-2">
            <?php foreach ($ateliers as $atelier): ?>
                <div class="col-md-4">
                    <article class="univers">
                        <p class="mb-2"><span class="pastille pastille--atelier"></span>Atelier</p>
                        <h3 class="h6"><?= esc($atelier['titre']) ?></h3>
                        <p class="mb-1 text-secondary"><?= date_fr($atelier['dateDebut']) ?> — <?= esc($atelier['lieu'] ?? 'Hors showroom') ?></p>
                        <p class="mb-0 small">Animé par <?= esc($atelier['animateur'] ?? '—') ?> · <?= max(0, (int) $atelier['placesRestantes']) ?> place(s)</p>
                    </article>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
