<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="en-tete-page">
    <div class="container">
        <h1>Jeu de démonstration</h1>
        <p>Les dates de démonstration avancent avec le calendrier : réinitialisez avant une présentation pour repartir de données propres.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-6">
                <article class="univers h-100">
                    <h2 class="h5">État actuel</h2>
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Dernière réinitialisation</dt>
                        <dd class="col-sm-6"><?= $derniere ? esc(date_fr($derniere)) : 'inconnue' ?></dd>
                        <dt class="col-sm-6">Décalage des dates</dt>
                        <dd class="col-sm-6"><?= $calendrier->semaines() ?> semaine(s) après le <?= date('d/m/Y', strtotime(\App\Libraries\CalendrierDemo::REFERENCE)) ?></dd>
                        <?php foreach ($compteurs as $libelle => $nombre): ?>
                            <dt class="col-sm-6"><?= esc($libelle) ?></dt>
                            <dd class="col-sm-6"><?= $nombre ?></dd>
                        <?php endforeach ?>
                    </dl>
                    <p class="mt-3 mb-0">
                        <?php if ($aJour): ?>
                            <span class="badge text-bg-success">Dates à jour</span>
                        <?php else: ?>
                            <span class="badge text-bg-warning">Dates à rafraîchir : certains événements sont passés</span>
                        <?php endif ?>
                    </p>
                </article>
            </div>
            <div class="col-lg-6">
                <form class="formulaire h-100" method="post" action="<?= site_url('admin/demo/reinitialiser') ?>">
                    <?= csrf_field() ?>
                    <h2 class="h5">Réinitialiser la démo</h2>
                    <p>Supprime <strong>toutes</strong> les données (comptes créés, réservations, messages…) et recrée le jeu d'essai avec des dates décalées sur la semaine en cours. Votre session repasse sur le compte <code>admin@robotix.test</code>.</p>
                    <label class="form-label" for="confirmation">Tapez <strong>REINITIALISER</strong> pour confirmer</label>
                    <input class="form-control mb-3" id="confirmation" name="confirmation" autocomplete="off" required pattern="REINITIALISER">
                    <button class="btn btn-danger" type="submit">Réinitialiser la démo</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
