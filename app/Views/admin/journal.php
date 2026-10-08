<?php
$libelles = ['ajout' => 'success', 'modification' => 'primary', 'suppression' => 'danger', 'remplacement' => 'warning', 'reinitialisation' => 'dark'];
?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="en-tete-page">
    <div class="container">
        <h1>Journal des actions</h1>
        <p>Toutes les modifications faites depuis l'administration : qui, quoi, quand.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <form class="filtres row g-3 align-items-end mb-4" method="get" action="<?= site_url('admin/journal') ?>">
            <div class="col-sm-5 col-lg-3">
                <label class="form-label" for="type">Action</label>
                <select class="form-select" id="type" name="type" data-envoi-auto>
                    <option value="">Toutes les actions</option>
                    <?php foreach ($types as $valeur): ?>
                        <option value="<?= esc($valeur, 'attr') ?>"<?= $valeur === $type ? ' selected' : '' ?>><?= esc(ucfirst($valeur)) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-sm-5 col-lg-3">
                <label class="form-label" for="table">Table</label>
                <select class="form-select" id="table" name="table" data-envoi-auto>
                    <option value="">Toutes les tables</option>
                    <?php foreach ($tables as $valeur): ?>
                        <option value="<?= esc($valeur, 'attr') ?>"<?= $valeur === $table ? ' selected' : '' ?>><?= esc($valeur) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-sm-2 col-lg-2 d-grid">
                <button class="btn btn-robotix" type="submit">Filtrer</button>
            </div>
        </form>

        <div class="table-responsive formulaire p-0">
            <table class="table table-hover align-middle mb-0">
                <caption class="px-3"><?= $pager->getTotal() ?> action(s)</caption>
                <thead><tr><th scope="col">Date</th><th scope="col">Action</th><th scope="col">Table</th><th scope="col">Description</th><th scope="col">Auteur</th></tr></thead>
                <tbody>
                    <?php foreach ($lignes as $ligne): ?>
                        <tr>
                            <td class="text-nowrap"><?= esc(date_fr($ligne['dateAction'])) ?></td>
                            <td><span class="badge text-bg-<?= $libelles[$ligne['typeAction']] ?? 'secondary' ?>"><?= esc($ligne['typeAction']) ?></span></td>
                            <td><code><?= esc($ligne['tableCible']) ?></code> n° <?= (int) $ligne['idCible'] ?></td>
                            <td><?= esc($ligne['description']) ?></td>
                            <td><?= $ligne['prenom'] !== null ? esc($ligne['prenom'] . ' ' . $ligne['nom']) : '<em>compte supprimé</em>' ?></td>
                        </tr>
                    <?php endforeach ?>
                    <?php if ($lignes === []): ?>
                        <tr><td colspan="5" class="text-center text-secondary py-4">Aucune action enregistrée pour ces critères.</td></tr>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
        <div class="mt-4"><?= $pager->links('default', 'bootstrap') ?></div>
    </div>
</section>

<?= $this->endSection() ?>
