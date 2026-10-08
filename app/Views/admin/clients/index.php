<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="en-tete-page">
    <div class="container">
        <h1>Adhérents du Club</h1>
        <p>Liste des adhérents, filtrable par catégorie d'âge. Données lues avec PDO.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <form class="filtres row g-3 align-items-end mb-4" method="get" action="<?= site_url('admin/clients') ?>">
            <div class="col-sm-6 col-lg-4">
                <label class="form-label" for="categorie">Catégorie</label>
                <select class="form-select" id="categorie" name="categorie" data-envoi-auto>
                    <option value="">Toutes les catégories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['idCategorieAge'] ?>"<?= (int) $cat['idCategorieAge'] === $categorie ? ' selected' : '' ?>><?= esc($cat['libelle']) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-sm-3 col-lg-2 d-grid">
                <button class="btn btn-robotix" type="submit">Afficher</button>
            </div>
        </form>

        <div class="table-responsive formulaire p-0">
            <table class="table table-hover align-middle mb-0">
                <caption class="px-3"><?= count($adherents) ?> adhérent(s)</caption>
                <thead>
                    <tr>
                        <th scope="col">Photo</th><th scope="col">Nom</th><th scope="col">Contact</th><th scope="col">Catégorie</th>
                        <th scope="col">Dernière adhésion</th><th scope="col">Inscrit le</th><th scope="col">État</th><th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($adherents as $a): ?>
                        <tr>
                            <td><img class="vignette-adherent" src="<?= $a['photo'] ? base_url('uploads/clients/' . $a['photo']) : base_url('images/ui/avatar.svg') ?>" alt="" width="40" height="40"></td>
                            <th scope="row"><?= esc($a['nom']) ?> <?= esc($a['prenom']) ?></th>
                            <td><?= esc($a['email']) ?><br><small class="text-secondary"><?= esc($a['telephone']) ?></small></td>
                            <td><?= esc($a['categorie'] ?? '—') ?></td>
                            <td><?= $a['annee'] === null ? '—' : esc($a['formule']) . ' ' . (int) $a['annee'] . '<br><small class="text-secondary">' . euros($a['montant']) . '</small>' ?></td>
                            <td><?= date_fr($a['dateInscription'], false) ?></td>
                            <td><?= (int) $a['actif'] === 1 ? '<span class="badge text-bg-success">Actif</span>' : '<span class="badge text-bg-secondary">Désactivé</span>' ?></td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/clients/' . $a['idUtilisateur'] . '/modifier') ?>">Modifier</a>
                                <form class="d-inline" action="<?= site_url('admin/clients/' . $a['idUtilisateur'] . '/supprimer') ?>" method="post"
                                      data-confirm="Supprimer l'adhérent <?= esc($a['prenom'] . ' ' . $a['nom']) ?> et toutes ses données ?">
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
