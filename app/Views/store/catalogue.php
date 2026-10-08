<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="en-tete-page">
    <div class="container">
        <h1>Robotix Store</h1>
        <p>Robots humanoïdes livrés, installés et garantis 2 ans.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <form class="filtres row g-3 align-items-end" method="get" action="<?= site_url('store') ?>" role="search" aria-label="Filtrer les robots">
            <div class="col-sm-6 col-lg-3">
                <label class="form-label" for="q">Rechercher</label>
                <input class="form-control" type="search" id="q" name="q" value="<?= esc($filtres['q']) ?>" placeholder="Nom du robot">
            </div>
            <div class="col-sm-6 col-lg-2">
                <label class="form-label" for="categorie">Catégorie</label>
                <select class="form-select" id="categorie" name="categorie">
                    <option value="">Toutes</option>
                    <?php foreach ($categories as $categorie): ?>
                        <option value="<?= $categorie['idCategorie'] ?>"<?= (int) $categorie['idCategorie'] === $filtres['categorie'] ? ' selected' : '' ?>><?= esc($categorie['libelle']) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-sm-6 col-lg-2">
                <label class="form-label" for="marque">Marque</label>
                <select class="form-select" id="marque" name="marque">
                    <option value="">Toutes</option>
                    <?php foreach ($marques as $marque): ?>
                        <option value="<?= $marque['idMarque'] ?>"<?= (int) $marque['idMarque'] === $filtres['marque'] ? ' selected' : '' ?>><?= esc($marque['nom']) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-sm-6 col-lg-2">
                <label class="form-label" for="prixMax">Prix max (€ TTC)</label>
                <input class="form-control" type="number" id="prixMax" name="prixMax" min="0" step="100" value="<?= $filtres['prixMax'] ?: '' ?>">
            </div>
            <div class="col-sm-6 col-lg-2">
                <label class="form-label" for="tri">Trier par</label>
                <select class="form-select" id="tri" name="tri">
                    <?php foreach (['nouveautes' => 'Nouveautés', 'prix_asc' => 'Prix croissant', 'prix_desc' => 'Prix décroissant'] as $valeur => $libelle): ?>
                        <option value="<?= $valeur ?>"<?= $filtres['tri'] === $valeur ? ' selected' : '' ?>><?= $libelle ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-sm-6 col-lg-1 d-grid">
                <button class="btn btn-robotix" type="submit">OK</button>
            </div>
        </form>

        <p class="my-4" aria-live="polite">
            <strong><?= count($robots) ?> robot<?= count($robots) > 1 ? 's' : '' ?></strong>
            <?php if (array_filter($filtres) !== ['tri' => $filtres['tri']]): ?>
                · <a href="<?= site_url('store') ?>">Effacer les filtres</a>
            <?php endif ?>
        </p>

        <h2 class="visually-hidden">Résultats</h2>
        <?php if ($robots === []): ?>
            <p class="text-center py-5">Aucun robot ne correspond à votre recherche.</p>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($robots as $robot): ?>
                    <div class="col-sm-6 col-lg-4 col-xl-3"><?= view('partials/carte_robot', ['robot' => $robot]) ?></div>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </div>
</section>

<?= $this->endSection() ?>
