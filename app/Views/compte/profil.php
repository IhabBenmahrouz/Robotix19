<?php
$photo = $profil['photo'] ? base_url('uploads/clients/' . $profil['photo']) : base_url('images/ui/avatar.svg');
?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="en-tete-page">
    <div class="container d-flex align-items-center gap-4 flex-wrap">
        <img class="profil__photo" src="<?= $photo ?>" alt="Photo de <?= esc($profil['prenom']) ?>" width="120" height="120">
        <div>
            <h1><?= esc($profil['prenom'] . ' ' . $profil['nom']) ?></h1>
            <p>Membre du Club Robotix depuis le <?= date_fr($profil['dateInscription'], false) ?></p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="univers">
                    <h2 class="h5">Mes informations</h2>
                    <dl class="profil__infos">
                        <dt>Nom</dt><dd><?= esc($profil['nom']) ?></dd>
                        <dt>Prénom</dt><dd><?= esc($profil['prenom']) ?></dd>
                        <dt>E-mail</dt><dd><?= esc($profil['email']) ?></dd>
                        <dt>Téléphone</dt><dd><?= esc($profil['telephone']) ?></dd>
                        <dt>Catégorie</dt><dd><?= esc($profil['categorie'] ?? '—') ?><?= (float) $profil['txReduction'] > 0 ? ' (−' . (float) $profil['txReduction'] . ' %)' : '' ?></dd>
                        <dt>Date d'adhésion</dt><dd><?= date_fr($profil['dateAdhesion'], false) ?: '—' ?></dd>
                        <dt>Formule</dt><dd id="formule-actuelle"><?= esc($profil['formule'] ?? 'Aucune') ?></dd>
                        <dt>Montant payé</dt><dd id="montant-adhesion"><?= $profil['montant'] !== null ? euros($profil['montant']) : '—' ?></dd>
                        <dt>Centres d'intérêt</dt><dd><?= $interets === [] ? '—' : esc(implode(', ', $interets)) ?></dd>
                    </dl>
                </div>
            </div>
            <div class="col-lg-6">
                <form class="univers univers--violet" id="changer-formule" data-url="<?= site_url('compte/formule') ?>" data-csrf="<?= csrf_hash() ?>">
                    <h2 class="h5">Changer de formule pour <?= date('Y') ?></h2>
                    <p class="small text-secondary">Le changement est enregistré immédiatement, sans recharger la page.</p>
                    <?php foreach ($formules as $formule): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="idTarif" id="profil-formule-<?= $formule['code'] ?>"
                                   value="<?= $formule['idTarif'] ?>"<?= (int) $profil['idTarif'] === $formule['idTarif'] && (int) $profil['annee'] === (int) date('Y') ? ' checked' : '' ?>>
                            <label class="form-check-label" for="profil-formule-<?= $formule['code'] ?>"><?= esc($formule['libelle']) ?> — <?= euros($formule['valeur']) ?> avant réduction</label>
                        </div>
                    <?php endforeach ?>
                    <p class="mt-3 mb-0 small" id="message-formule" role="status" aria-live="polite"></p>
                </form>
            </div>
        </div>

        <h2 class="titre-section mt-5">Mes réservations</h2>
        <?php if ($reservations === []): ?>
            <p class="text-center">Aucune réservation pour le moment.</p>
        <?php else: ?>
            <div class="table-responsive formulaire p-0">
                <table class="table align-middle mb-0">
                    <caption class="px-3">Places réservées pour les événements Robotix</caption>
                    <thead><tr><th scope="col">Événement</th><th scope="col">Date</th><th scope="col" class="text-end">Places</th></tr></thead>
                    <tbody>
                        <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <th scope="row"><?= esc($reservation['nomEvenement']) ?></th>
                                <td><?= date_fr($reservation['dateDebut']) ?></td>
                                <td class="text-end"><?= (int) $reservation['nbPlace'] ?></td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        <?php endif ?>
        <p class="text-center mt-4"><a class="btn btn-robotix" href="<?= site_url('reservations') ?>">Réserver des places</a></p>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/profil.js') ?>"></script>
<?= $this->endSection() ?>
