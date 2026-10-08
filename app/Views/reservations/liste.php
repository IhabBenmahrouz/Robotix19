<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="en-tete-page">
    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1>Réserver des places</h1>
            <p>Démonstrations, ateliers, lancements et salons à venir.</p>
        </div>
        <a class="btn btn-robotix" href="<?= site_url('reservations/panier') ?>">Mon panier (<?= $nbPanier ?> place(s))</a>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if (! $estClient): ?>
            <p class="alert alert-info">La réservation est réservée aux adhérents du Club Robotix : vous pouvez consulter la liste.</p>
        <?php endif ?>
        <div class="table-responsive formulaire p-0">
            <table class="table align-middle mb-0">
                <caption class="px-3">Événements à venir et places disponibles</caption>
                <thead>
                    <tr><th scope="col">Événement</th><th scope="col">Date</th><th scope="col" class="text-end">Places dispo</th><th scope="col">Réserver</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($possibles as $reservation): ?>
                        <tr>
                            <th scope="row"><?= esc($reservation->getNomEvenement()) ?></th>
                            <td><?= date_fr($reservation->getDateResa()) ?></td>
                            <td class="text-end"><?= $reservation->getNbPlaceDispo() ?></td>
                            <td>
                                <?php if ($reservation->getNbPlaceDispo() === 0): ?>
                                    <span class="badge text-bg-secondary">Complet</span>
                                <?php else: ?>
                                    <form class="reservation__form" action="<?= site_url('reservations/ajouter') ?>" method="post">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="idEvenement" value="<?= $reservation->getIdEvenement() ?>">
                                        <label class="visually-hidden" for="places-<?= $reservation->getIdEvenement() ?>">Nombre de places</label>
                                        <input class="form-control form-control-sm" type="number" id="places-<?= $reservation->getIdEvenement() ?>" name="nbPlace"
                                               min="1" max="<?= $reservation->getNbPlaceDispo() ?>" value="1" required>
                                        <button class="btn btn-sm btn-robotix" type="submit"<?= $estClient ? '' : ' disabled' ?>>Ajouter au panier</button>
                                    </form>
                                <?php endif ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
