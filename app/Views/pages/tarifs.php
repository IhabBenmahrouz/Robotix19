<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="en-tete-page">
    <div class="container">
        <h1>Tarifs</h1>
        <p>Des robots pour tous les budgets, des services pour en profiter sereinement.</p>
    </div>
</section>

<section class="section" aria-labelledby="titre-gammes">
    <div class="container">
        <h2 id="titre-gammes" class="titre-section">Nos gammes</h2>
        <div class="row g-4 mt-2">
            <?php foreach ($gammes as $gamme): ?>
                <div class="col-sm-6 col-lg-3">
                    <article class="gamme">
                        <h3 class="h5"><?= esc($gamme['libelle']) ?></h3>
                        <p class="gamme__prix"><small>à partir de</small><br><?= euros(prix_ttc($gamme['prixMin'], 20)) ?> <small>TTC</small></p>
                        <p><?= esc($gamme['description']) ?></p>
                        <p class="text-secondary"><?= (int) $gamme['nbRobots'] ?> robot<?= (int) $gamme['nbRobots'] > 1 ? 's' : '' ?></p>
                        <a class="btn btn-contour btn-sm" href="<?= site_url('store') . '?categorie=' . $gamme['idCategorie'] ?>">Voir la gamme</a>
                    </article>
                </div>
            <?php endforeach ?>
        </div>
    </div>
</section>

<section class="section bg-white" aria-labelledby="titre-services">
    <div class="container">
        <h2 id="titre-services" class="titre-section">Services</h2>
        <div class="table-responsive mt-4">
            <table class="table table-hover align-middle tableau-tarifs">
                <caption>Prix des services, par robot</caption>
                <thead><tr><th scope="col">Service</th><th scope="col">Détail</th><th scope="col" class="text-end">Prix HT</th><th scope="col" class="text-end">Prix TTC</th></tr></thead>
                <tbody>
                    <?php foreach ($options as $option): ?>
                        <tr>
                            <th scope="row"><?= esc($option['libelle']) ?></th>
                            <td><?= esc($option['description']) ?></td>
                            <?php if ($option['type'] === 'pourcentage'): ?>
                                <td class="text-end" colspan="2"><?= (float) $option['valeur'] ?> % du prix HT du robot</td>
                            <?php else: ?>
                                <td class="text-end"><?= euros($option['valeur']) ?></td>
                                <td class="text-end"><?= euros(prix_ttc($option['valeur'], 20)) ?></td>
                            <?php endif ?>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="section section--sombre" aria-labelledby="titre-calculateur">
    <div class="container">
        <h2 id="titre-calculateur" class="titre-section">Calculez votre prix</h2>
        <form id="calculateur" class="calculateur row g-4 mt-2" novalidate>
            <div class="col-lg-7">
                <div class="calculateur__panneau">
                    <div class="row g-3">
                        <div class="col-sm-8">
                            <label class="form-label" for="robot">Robot</label>
                            <select class="form-select" id="robot" name="robot">
                                <?php foreach ($robots as $robot): ?>
                                    <option value="<?= $robot['idProduit'] ?>"<?= $robot['idProduit'] === $robotChoisi ? ' selected' : '' ?>><?= esc($robot['nom']) ?> — <?= euros($robot['prixHt']) ?> HT</option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label" for="quantite">Quantité (1 à 5)</label>
                            <input class="form-control" type="number" id="quantite" name="quantite" min="1" max="5" step="1" value="1" required>
                            <div class="invalid-feedback">Saisissez un nombre entier entre 1 et 5.</div>
                        </div>
                    </div>
                    <fieldset class="mt-4">
                        <legend class="form-label">Options</legend>
                        <?php foreach ($options as $option): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="options" id="option-<?= $option['code'] ?>" value="<?= $option['code'] ?>">
                                <label class="form-check-label" for="option-<?= $option['code'] ?>">
                                    <?= esc($option['libelle']) ?>
                                    <span class="text-secondary-emphasis">(<?= $option['type'] === 'pourcentage' ? (float) $option['valeur'] . ' % du prix HT' : euros($option['valeur']) . ' HT' ?>)</span>
                                </label>
                            </div>
                        <?php endforeach ?>
                    </fieldset>
                    <fieldset class="mt-4">
                        <legend class="form-label">Financement</legend>
                        <?php foreach ($financements as $index => $financement): ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="financement" id="financement-<?= $financement['mois'] ?>"
                                       value="<?= $financement['mois'] ?>"<?= $index === 0 ? ' checked' : '' ?>>
                                <label class="form-check-label" for="financement-<?= $financement['mois'] ?>"><?= esc($financement['libelle']) ?></label>
                            </div>
                        <?php endforeach ?>
                    </fieldset>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="calculateur__resultat" aria-live="polite">
                    <dl class="calculateur__lignes">
                        <dt>Robot(s) HT</dt><dd><output id="resultat-robot">—</output></dd>
                        <dt>Options HT</dt><dd><output id="resultat-options">—</output></dd>
                        <dt>Total HT</dt><dd><output id="resultat-ht">—</output></dd>
                        <dt>TVA 20 %</dt><dd><output id="resultat-tva">—</output></dd>
                        <dt class="calculateur__total">Total TTC</dt><dd class="calculateur__total"><output id="resultat-ttc">—</output></dd>
                        <dt>Mensualité</dt><dd><output id="resultat-mensualite">—</output></dd>
                        <dt>Coût du crédit</dt><dd><output id="resultat-cout">—</output></dd>
                    </dl>
                    <a class="btn btn-robotix w-100" id="lien-devis" href="<?= site_url('contact') ?>?objet=devis">Demander un devis</a>
                </div>
            </div>
        </form>
        <script type="application/json" id="donnees-tarifs"><?= json_encode(
            ['robots' => $robots, 'options' => $options, 'financements' => $financements],
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE
        ) ?></script>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/calculateur.js') ?>"></script>
<?= $this->endSection() ?>
