<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="en-tete-page">
    <div class="container">
        <h1>Mon panier</h1>
        <p><?= $nbPlaces ?> place(s) en attente d'enregistrement.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if ($reservations === []): ?>
            <p class="text-center">Votre panier est vide.</p>
        <?php else: ?>
            <div class="table-responsive formulaire p-0 mb-4">
                <table class="table align-middle mb-0">
                    <caption class="px-3">Réservations sélectionnées</caption>
                    <thead><tr><th scope="col">Événement</th><th scope="col">Date</th><th scope="col" class="text-end">Places</th></tr></thead>
                    <tbody>
                        <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <th scope="row"><?= esc($reservation->getNomEvenement()) ?></th>
                                <td><?= date_fr($reservation->getDateResa()) ?></td>
                                <td class="text-end"><?= $reservation->getNbPlace() ?> place(s)</td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        <?php endif ?>

        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <a class="btn btn-contour" href="<?= site_url('reservations') ?>">Continuer mes réservations</a>
            <?php if ($reservations !== []): ?>
                <form action="<?= site_url('reservations/enregistrer') ?>" method="post">
                    <?= csrf_field() ?>
                    <button class="btn btn-robotix" type="submit">Enregistrer le panier</button>
                </form>
                <form action="<?= site_url('reservations/vider') ?>" method="post" data-confirm="Vider le panier ?">
                    <?= csrf_field() ?>
                    <button class="btn btn-outline-danger" type="submit">Vider le panier</button>
                </form>
            <?php endif ?>
            <form action="<?= site_url('compte/deconnexion') ?>" method="post" data-confirm="Se déconnecter ? Le panier sera annulé.">
                <?= csrf_field() ?>
                <button class="btn btn-outline-secondary" type="submit">Se déconnecter</button>
            </form>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
