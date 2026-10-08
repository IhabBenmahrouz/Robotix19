<?php $zones = \App\Libraries\AnatomieRobot::zones(); ?>
<section class="section section--sombre anatomie" aria-labelledby="titre-anatomie">
    <div class="container">
        <h2 id="titre-anatomie" class="titre-section">Anatomie d'un robot Robotix</h2>
        <p class="text-center mb-5">Survolez une partie du robot pour un aperçu, cliquez pour tout savoir.</p>
        <div class="row align-items-center g-5">
            <div class="col-md-6">
                <div class="image-map" data-image-map>
                    <img class="img-fluid" src="<?= base_url('images/ui/robot-anatomie.svg') ?>" usemap="#carte-robot"
                         width="600" height="800" alt="Schéma d'un robot humanoïde : tête, torse, mains et jambes">
                    <map name="carte-robot">
                        <?php foreach ($zones as $cle => $zone): ?>
                            <area shape="rect" coords="<?= $zone['coords'] ?>" href="#zone-info"
                                  alt="<?= esc($zone['titre']) ?>" data-zone="<?= $cle ?>">
                        <?php endforeach ?>
                    </map>
                    <div class="image-map__surbrillance" hidden></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="image-map__info" id="zone-info" aria-live="polite">
                    <h3 class="h5">Choisissez une zone</h3>
                    <p class="mb-0">Tête, torse, mains ou jambes : chaque partie cache une technologie.</p>
                </div>
                <ul class="image-map__liste">
                    <?php foreach ($zones as $cle => $zone): ?>
                        <li><button type="button" class="btn btn-contour btn-sm" data-zone-bouton="<?= $cle ?>"><?= esc($zone['titre']) ?></button></li>
                    <?php endforeach ?>
                </ul>
            </div>
        </div>
    </div>
    <script type="application/json" id="zones-robot"><?= json_encode($zones, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?></script>
</section>

<div class="modal fade" id="modale-zone" role="dialog" tabindex="-1" aria-labelledby="modale-zone-titre" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title h5" id="modale-zone-titre">Zone du robot</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body" id="modale-zone-texte"></div>
            <div class="modal-footer">
                <a class="btn btn-robotix" href="<?= site_url('store') ?>">Voir les robots</a>
            </div>
        </div>
    </div>
</div>
