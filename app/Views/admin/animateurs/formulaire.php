<?php
$specialite = (string) old('specialite', $animateur['specialite'], false);
$remplacant = (string) old('idRemplacant', (string) ($animateur['idRemplacant'] ?? ''), false);
?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="section">
    <div class="container">
        <form class="formulaire formulaire--etroit" action="<?= site_url('admin/club/animateurs/' . $animateur['idUtilisateur']) ?>" method="post">
            <?= csrf_field() ?>
            <h1 class="h3 mb-4">Modifier <?= esc($animateur['prenom'] . ' ' . $animateur['nom']) ?></h1>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label" for="specialite">Spécialité</label>
                    <input class="form-control" type="text" id="specialite" name="specialite" required minlength="3" maxlength="80" value="<?= esc($specialite) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label" for="idRemplacant">Remplaçant (un autre animateur)</label>
                    <select class="form-select" id="idRemplacant" name="idRemplacant">
                        <option value="">Aucun</option>
                        <?php foreach ($remplacants as $candidat): ?>
                            <option value="<?= $candidat['idUtilisateur'] ?>"<?= $remplacant === (string) $candidat['idUtilisateur'] ? ' selected' : '' ?>><?= esc($candidat['prenom'] . ' ' . $candidat['nom']) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-robotix" type="submit">Enregistrer</button>
                    <a class="btn btn-outline-secondary" href="<?= site_url('admin/club/animateurs') ?>">Annuler</a>
                </div>
            </div>
        </form>
    </div>
</section>

<?= $this->endSection() ?>
