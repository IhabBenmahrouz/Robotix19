<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="en-tete-page">
    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1>Gestion du planning</h1>
            <p>Ajoutez, modifiez ou supprimez les événements affichés dans le calendrier.</p>
        </div>
        <a class="btn btn-robotix" href="<?= site_url('admin/evenements/nouveau') ?>">+ Nouvel événement</a>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="table-responsive formulaire p-0">
            <table class="table table-hover align-middle mb-0">
                <caption class="px-3"><?= count($evenements) ?> événement(s), du plus récent au plus ancien</caption>
                <thead>
                    <tr><th scope="col">Date</th><th scope="col">Titre</th><th scope="col">Type</th><th scope="col">Lieu</th><th scope="col">Animateur</th><th scope="col">Robot</th><th scope="col" class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($evenements as $evenement): ?>
                        <tr>
                            <td><?= date_fr($evenement['dateDebut']) ?><br><small class="text-secondary">→ <?= date_fr($evenement['dateFin']) ?></small></td>
                            <th scope="row"><?= esc($evenement['titre']) ?></th>
                            <td><span class="pastille pastille--<?= esc($evenement['type']) ?>"></span><?= esc($types[$evenement['type']] ?? $evenement['type']) ?></td>
                            <td><?= esc($evenement['showroom'] ?? 'Hors showroom') ?></td>
                            <td><?= esc($evenement['animateur'] ?? '—') ?></td>
                            <td><?= esc($evenement['produit'] ?? '—') ?></td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/evenements/' . $evenement['idEvenement'] . '/modifier') ?>">Modifier</a>
                                <?php if ($evenement['idAnimateur'] !== null): ?>
                                    <form class="d-inline" action="<?= site_url('admin/evenements/' . $evenement['idEvenement'] . '/remplacer') ?>" method="post"
                                          data-confirm="Confier « <?= esc($evenement['titre']) ?> » au remplaçant de son animateur ?">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-outline-secondary" type="submit">Faire remplacer</button>
                                    </form>
                                <?php endif ?>
                                <form class="d-inline" action="<?= site_url('admin/evenements/' . $evenement['idEvenement'] . '/supprimer') ?>" method="post"
                                      data-confirm="Supprimer « <?= esc($evenement['titre']) ?> » ?">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
