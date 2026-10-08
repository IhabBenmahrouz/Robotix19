<?php
$e = $evenement ?? [];
$valeur = static fn (string $champ, string $defaut = ''): string => (string) old($champ, $e[$champ] ?? $defaut, false);
$action = $evenement === null ? site_url('admin/evenements') : site_url('admin/evenements/' . $evenement['idEvenement']);
?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="section">
    <div class="container">
        <form class="formulaire formulaire--etroit" action="<?= $action ?>" method="post">
            <?= csrf_field() ?>
            <h1 class="h3 mb-4"><?= esc($titre) ?></h1>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label" for="titre">Titre *</label>
                    <input class="form-control" type="text" id="titre" name="titre" required maxlength="150" value="<?= esc($valeur('titre')) ?>">
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="type">Type *</label>
                    <select class="form-select" id="type" name="type" required>
                        <?php foreach ($types as $code => $libelle): ?>
                            <option value="<?= $code ?>"<?= $valeur('type') === $code ? ' selected' : '' ?>><?= esc($libelle) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="idShowroom">Lieu</label>
                    <select class="form-select" id="idShowroom" name="idShowroom">
                        <option value="">Hors showroom</option>
                        <?php foreach ($showrooms as $showroom): ?>
                            <option value="<?= $showroom['idShowroom'] ?>"<?= $valeur('idShowroom') === (string) $showroom['idShowroom'] ? ' selected' : '' ?>><?= esc($showroom['nom']) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="dateDebut">Début *</label>
                    <input class="form-control" type="datetime-local" id="dateDebut" name="dateDebut" required
                           value="<?= esc(old('dateDebut', date_saisie($e['dateDebut'] ?? null), false)) ?>">
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="dateFin">Fin *</label>
                    <input class="form-control" type="datetime-local" id="dateFin" name="dateFin" required
                           value="<?= esc(old('dateFin', date_saisie($e['dateFin'] ?? null), false)) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label" for="idAnimateur">Animateur</label>
                    <select class="form-select" id="idAnimateur" name="idAnimateur">
                        <option value="">Aucun</option>
                        <?php foreach ($animateurs as $animateur): ?>
                            <option value="<?= $animateur['idUtilisateur'] ?>"<?= $valeur('idAnimateur') === (string) $animateur['idUtilisateur'] ? ' selected' : '' ?>><?= esc($animateur['prenom'] . ' ' . $animateur['nom']) ?> — <?= esc($animateur['specialite']) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label" for="idProduit">Robot présenté</label>
                    <select class="form-select" id="idProduit" name="idProduit">
                        <option value="">Aucun</option>
                        <?php foreach ($robots as $robot): ?>
                            <option value="<?= $robot['idProduit'] ?>"<?= $valeur('idProduit') === (string) $robot['idProduit'] ? ' selected' : '' ?>><?= esc($robot['nom']) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4" maxlength="1000"><?= esc($valeur('description')) ?></textarea>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-robotix" type="submit">Enregistrer</button>
                    <a class="btn btn-outline-secondary" href="<?= site_url('admin/evenements') ?>">Annuler</a>
                </div>
            </div>
        </form>
    </div>
</section>

<?= $this->endSection() ?>
