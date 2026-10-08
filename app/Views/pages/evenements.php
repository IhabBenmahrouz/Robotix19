<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="en-tete-page">
    <div class="container">
        <h1>Événements</h1>
        <p>Démonstrations, lancements, ateliers et salons : venez rencontrer nos robots.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="calendrier" id="calendrier" data-api="<?= site_url('api/evenements') ?>" data-mois="<?= esc($mois) ?>">
                    <div class="calendrier__barre">
                        <button type="button" class="btn btn-contour btn-sm" id="mois-precedent" aria-label="Mois précédent">‹</button>
                        <h2 class="calendrier__titre" id="calendrier-titre" aria-live="polite">Calendrier</h2>
                        <button type="button" class="btn btn-contour btn-sm" id="mois-suivant" aria-label="Mois suivant">›</button>
                        <button type="button" class="btn btn-link btn-sm" id="mois-courant">Aujourd'hui</button>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="filtre-type">Type</label>
                            <select class="form-select form-select-sm" id="filtre-type">
                                <option value="">Tous les types</option>
                                <?php foreach ($types as $code => $libelle): ?>
                                    <option value="<?= $code ?>"><?= esc($libelle) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="filtre-showroom">Lieu</label>
                            <select class="form-select form-select-sm" id="filtre-showroom">
                                <option value="">Tous les lieux</option>
                                <?php foreach ($showrooms as $showroom): ?>
                                    <option value="<?= $showroom['idShowroom'] ?>"><?= esc($showroom['nom']) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="calendrier__table">
                            <caption class="visually-hidden">Événements du mois affiché</caption>
                            <thead>
                                <tr>
                                    <?php foreach (['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $jour): ?>
                                        <th scope="col"><?= $jour ?></th>
                                    <?php endforeach ?>
                                </tr>
                            </thead>
                            <tbody id="calendrier-corps"></tbody>
                        </table>
                    </div>
                    <p class="text-danger mt-2" id="calendrier-erreur" role="alert" hidden></p>
                    <noscript><p>Activez JavaScript pour afficher le calendrier.</p></noscript>
                    <ul class="legende">
                        <?php foreach ($types as $code => $libelle): ?>
                            <li><span class="pastille pastille--<?= $code ?>"></span><?= esc($libelle) ?></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            </div>
            <div class="col-lg-4">
                <aside class="detail-jour" id="detail-jour" aria-live="polite">
                    <h2 class="h5">Détail du jour</h2>
                    <p class="mb-0">Cliquez sur un jour pour voir ses événements.</p>
                </aside>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/calendrier.js') ?>"></script>
<?= $this->endSection() ?>
