<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="section">
    <div class="container">
        <nav aria-label="Fil d'Ariane">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= site_url('store') ?>">Store</a></li>
                <li class="breadcrumb-item"><a href="<?= site_url('store') . '?categorie=' . $robot['idCategorie'] ?>"><?= esc($robot['categorie']) ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= esc($robot['nom']) ?></li>
            </ol>
        </nav>

        <div class="row g-5">
            <div class="col-lg-6">
                <div class="galerie" data-galerie>
                    <button type="button" class="galerie__principale" data-lightbox="ouvrir" aria-label="Agrandir l'image">
                        <img class="img-fluid" id="image-principale" src="<?= base_url('images/robots/' . ($images[0]['fichier'] ?? 'defaut.svg')) ?>"
                             alt="<?= esc($images[0]['legende'] ?? $robot['nom']) ?>" width="400" height="500">
                    </button>
                    <div class="galerie__vignettes">
                        <?php foreach ($images as $index => $image): ?>
                            <button type="button" class="galerie__vignette<?= $index === 0 ? ' active' : '' ?>"
                                    data-index="<?= $index ?>" data-src="<?= base_url('images/robots/' . $image['fichier']) ?>"
                                    data-legende="<?= esc(($image['legende'] ?? $robot['nom']) . ($image['credit'] ? ' — Photo : ' . $image['credit'] . ', ' . $image['licence'] : '')) ?>" aria-label="Voir l'image <?= $index + 1 ?>">
                                <img src="<?= base_url('images/robots/' . $image['fichier']) ?>" alt="" width="80" height="100">
                            </button>
                        <?php endforeach ?>
                    </div>
                    <ul class="galerie__credits">
                        <?php foreach ($images as $image): ?>
                            <?php if ($image['credit']): ?>
                                <li><?= esc($image['legende']) ?> — Photo : <?= esc($image['credit']) ?>, <?= esc($image['licence']) ?>,
                                    <a href="<?= esc($image['source']) ?>" rel="noopener" target="_blank">Wikimedia Commons</a></li>
                            <?php endif ?>
                        <?php endforeach ?>
                    </ul>
                </div>
            </div>

            <div class="col-lg-6">
                <p class="carte-robot__marque"><?= esc($robot['marque']) ?> · <?= esc($robot['categorie']) ?> · Réf. <?= esc($robot['reference']) ?></p>
                <h1><?= esc($robot['nom']) ?></h1>
                <p class="fiche__prix"><?= euros(prix_ttc($robot['prixHt'], $robot['tauxTva'])) ?> <small>TTC</small></p>
                <p class="text-secondary"><?= euros($robot['prixHt']) ?> HT · TVA <?= (float) $robot['tauxTva'] ?> % · <small>prix de démonstration (boutique fictive)</small></p>
                <p>
                    <?php if ($robot['statutCommercial']): ?>
                        <span class="badge text-bg-info">Statut réel : <?= esc($robot['statutCommercial']) ?></span>
                    <?php endif ?>
                    <?php if ((int) $robot['stock'] > 0): ?>
                        <span class="badge text-bg-success">En stock (<?= (int) $robot['stock'] ?>)</span>
                    <?php else: ?>
                        <span class="badge text-bg-secondary">Sur commande</span>
                    <?php endif ?>
                </p>
                <p class="lead"><?= nl2br(esc($robot['description'])) ?></p>
                <dl class="fiche__marque">
                    <dt>Fabricant</dt><dd><?= esc($robot['marque']) ?></dd>
                    <dt>Siège social</dt><dd><?= esc($robot['marqueSiege'] ?? $robot['marquePays']) ?></dd>
                    <dt>Site officiel</dt><dd><a href="<?= esc($robot['marqueSite']) ?>" rel="noopener" target="_blank"><?= esc($robot['marqueSite']) ?></a></dd>
                </dl>
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a class="btn btn-robotix" href="<?= site_url('tarifs') . '?robot=' . $robot['idProduit'] ?>#calculateur">Configurer et calculer mon prix</a>
                    <a class="btn btn-contour" href="<?= site_url('contact') . '?objet=demo&amp;robot=' . $robot['idProduit'] ?>">Demander une démo</a>
                </div>
            </div>
        </div>

        <?php if ($compatibles !== []): ?>
            <h2 class="titre-section mt-5 pt-4">Robots compatibles</h2>
            <div class="row g-4 justify-content-center">
                <?php foreach ($compatibles as $compatible): ?>
                    <div class="col-sm-6 col-lg-3"><?= view('partials/carte_robot', ['robot' => $compatible]) ?></div>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </div>
</section>

<?= $this->include('partials/anatomie') ?>

<div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Galerie d'images" hidden>
    <button type="button" class="lightbox__fermer" data-lightbox="fermer" aria-label="Fermer">×</button>
    <button type="button" class="lightbox__nav lightbox__nav--precedent" data-lightbox="precedent" aria-label="Image précédente">‹</button>
    <figure class="lightbox__figure">
        <img id="lightbox-image" src="<?= base_url('images/robots/' . ($images[0]['fichier'] ?? 'defaut.svg')) ?>" alt="" width="400" height="500">
        <figcaption id="lightbox-legende"></figcaption>
    </figure>
    <button type="button" class="lightbox__nav lightbox__nav--suivant" data-lightbox="suivant" aria-label="Image suivante">›</button>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/galerie.js') ?>"></script>
<script src="<?= base_url('js/image-map.js') ?>"></script>
<?= $this->endSection() ?>
