<?php $nombreTotal = array_sum(array_column($parCategorie, 'nombre')); ?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="en-tete-page">
    <div class="container">
        <h1>Statistiques du Club</h1>
        <p>Adhésions par catégorie d'âge et montants encaissés.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <article class="univers">
                    <h2 class="h6 text-secondary">Total des adhésions <?= date('Y') ?></h2>
                    <p class="stat__valeur"><?= euros($totalEnCours) ?></p>
                    <p class="small mb-0">Fonction sans paramètre : <code>montantTotalAdhesions()</code></p>
                </article>
            </div>
            <div class="col-md-4">
                <article class="univers univers--violet">
                    <h2 class="h6 text-secondary">Total des adhésions <?= $annee ?></h2>
                    <p class="stat__valeur"><?= euros($montantAnnee) ?></p>
                    <p class="small mb-0">Fonction avec paramètre : <code>montantAdhesions(<?= $annee ?>)</code></p>
                </article>
            </div>
            <div class="col-md-4">
                <article class="univers univers--ambre">
                    <h2 class="h6 text-secondary">Nombre d'adhésions <?= $annee ?></h2>
                    <p class="stat__valeur"><?= $nombreTotal ?></p>
                    <form method="get" action="<?= site_url('admin/statistiques') ?>">
                        <label class="form-label small" for="annee">Année</label>
                        <select class="form-select form-select-sm" id="annee" name="annee" data-envoi-auto>
                            <?php foreach ($annees as $a): ?>
                                <option value="<?= $a ?>"<?= $a === $annee ? ' selected' : '' ?>><?= $a ?></option>
                            <?php endforeach ?>
                        </select>
                        <noscript><button class="btn btn-sm btn-contour mt-2" type="submit">Afficher</button></noscript>
                    </form>
                </article>
            </div>
        </div>

        <div class="formulaire">
            <h2 class="h5">Adhésions par catégorie d'âge en <?= $annee ?></h2>
            <p class="small text-secondary">Fonction avec paramètre : <code>adhesionsParCategorie(<?= $annee ?>)</code></p>
            <table class="table align-middle">
                <caption>Nombre et taux d'adhésions par catégorie</caption>
                <thead><tr><th scope="col">Catégorie</th><th scope="col" class="text-end">Nombre</th><th scope="col" class="text-end">Taux</th><th scope="col">Répartition</th></tr></thead>
                <tbody>
                    <?php foreach ($parCategorie as $ligne): ?>
                        <tr>
                            <th scope="row"><?= esc($ligne['libelle']) ?></th>
                            <td class="text-end"><?= $ligne['nombre'] ?></td>
                            <td class="text-end"><?= number_format($ligne['taux'], 1, ',', ' ') ?> %</td>
                            <td class="w-50">
                                <div class="barre" role="img" aria-label="<?= number_format($ligne['taux'], 1, ',', ' ') ?> %">
                                    <div class="barre__remplissage" style="width: <?= number_format($ligne['taux'], 1, '.', '') ?>%"></div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
