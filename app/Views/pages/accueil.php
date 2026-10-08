<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <p class="hero__surtitre">Robots humanoïdes pour particuliers</p>
                <h1>Le robot qui vous aide <em>à la maison</em> est enfin là.</h1>
                <p>Robotix sélectionne, vend et installe chez vous les meilleurs robots humanoïdes du marché,
                   et vous tient informé de toute leur actualité.</p>
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a class="btn btn-robotix" href="<?= site_url('store') ?>">Découvrir le Store</a>
                    <a class="btn btn-contour" href="<?= site_url('tarifs') ?>">Calculer mon prix</a>
                </div>
            </div>
            <div class="col-lg-5 text-center">
                <figure class="hero__photo">
                    <img src="<?= base_url('images/robots/rbx-xpg-iron-1.jpg') ?>"
                         alt="Le robot humanoïde XPeng Iron exposé au salon de l'automobile de Guangzhou 2025" width="640" height="960">
                    <figcaption>XPeng Iron, salon de Guangzhou 2025 — Photo : Tim Wu, CC BY-SA 4.0,
                        <a href="https://commons.wikimedia.org/wiki/File:XPeng_Iron_at_Auto_Guangzhou_2025_20251123.jpg" rel="noopener" target="_blank">Wikimedia Commons</a></figcaption>
                </figure>
            </div>
        </div>
    </div>
</section>

<section class="section" aria-labelledby="titre-activite">
    <div class="container">
        <h2 id="titre-activite" class="titre-section">Notre activité</h2>
        <p class="text-center text-secondary mb-5">Deux univers complémentaires et un accompagnement de A à Z.</p>
        <div class="row g-4">
            <div class="col-md-4">
                <article class="univers">
                    <p class="univers__icone" aria-hidden="true">🤖</p>
                    <h3 class="h5">Robotix Store</h3>
                    <p>Une sélection de robots humanoïdes compagnons, domestiques, éducatifs et premium, livrés et installés chez vous.</p>
                    <a href="<?= site_url('store') ?>">Voir le catalogue →</a>
                </article>
            </div>
            <div class="col-md-4">
                <article class="univers univers--violet">
                    <p class="univers__icone" aria-hidden="true">📰</p>
                    <h3 class="h5">Robotix News</h3>
                    <p>Tests, comparatifs et nouveautés : toute l'actualité des robots vendus sur le Store.</p>
                    <a href="<?= site_url('news') ?>">Lire les articles →</a>
                </article>
            </div>
            <div class="col-md-4">
                <article class="univers univers--ambre">
                    <p class="univers__icone" aria-hidden="true">🛠️</p>
                    <h3 class="h5">Services et showrooms</h3>
                    <p>Démonstrations gratuites, ateliers, financement et maintenance dans nos showrooms de Paris, Lyon et Marseille.</p>
                    <a href="<?= site_url('evenements') ?>">Voir les événements →</a>
                </article>
            </div>
        </div>
    </div>
</section>

<section class="section bg-white" aria-labelledby="titre-phares">
    <div class="container">
        <h2 id="titre-phares" class="titre-section">Robots phares</h2>
        <p class="text-center text-secondary mb-5">Survolez un robot pour le voir en action.</p>
        <div class="row g-4">
            <?php foreach ($phares as $robot): ?>
                <div class="col-sm-6 col-lg-3"><?= view('partials/carte_robot', ['robot' => $robot]) ?></div>
            <?php endforeach ?>
        </div>
        <p class="text-center mt-5"><a class="btn btn-robotix" href="<?= site_url('store') ?>">Tout le catalogue</a></p>
    </div>
</section>

<?= $this->include('partials/anatomie') ?>

<section class="section" aria-labelledby="titre-evenements">
    <div class="container">
        <h2 id="titre-evenements" class="titre-section">Prochains événements</h2>
        <?php if ($evenements === []): ?>
            <p class="text-center">Aucun événement programmé pour le moment.</p>
        <?php else: ?>
            <div class="row g-4 mt-2">
                <?php foreach ($evenements as $evenement): ?>
                    <div class="col-md-4">
                        <article class="univers">
                            <p class="mb-2"><span class="pastille pastille--<?= esc($evenement['type']) ?>"></span><?= esc($types[$evenement['type']] ?? $evenement['type']) ?></p>
                            <h3 class="h6"><?= esc($evenement['titre']) ?></h3>
                            <p class="mb-0 text-secondary"><?= date_fr($evenement['dateDebut']) ?> — <?= esc($evenement['ville'] ?? 'Hors showroom') ?></p>
                        </article>
                    </div>
                <?php endforeach ?>
            </div>
        <?php endif ?>
        <p class="text-center mt-4"><a href="<?= site_url('evenements') ?>">Voir tout le calendrier →</a></p>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/image-map.js') ?>"></script>
<?= $this->endSection() ?>
