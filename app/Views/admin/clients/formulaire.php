<?php
$valeur = static fn (string $champ): string => (string) old($champ, $adherent[$champ] ?? '', false);
?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="section">
    <div class="container">
        <form class="formulaire formulaire--etroit" action="<?= site_url('admin/clients/' . $adherent['idUtilisateur']) ?>" method="post" data-valider novalidate>
            <?= csrf_field() ?>
            <h1 class="h3 mb-4">Modifier <?= esc($adherent['prenom'] . ' ' . $adherent['nom']) ?></h1>
            <div class="row g-3">
                <div class="col-sm-6">
                    <label class="form-label" for="prenom">Prénom</label>
                    <input class="form-control" type="text" id="prenom" name="prenom" required data-regle="nom" value="<?= esc($valeur('prenom')) ?>">
                    <div class="invalid-feedback">Lettres uniquement (2 à 50 caractères).</div>
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="nom">Nom</label>
                    <input class="form-control" type="text" id="nom" name="nom" required data-regle="nom" value="<?= esc($valeur('nom')) ?>">
                    <div class="invalid-feedback">Lettres uniquement (2 à 50 caractères).</div>
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="email">E-mail</label>
                    <input class="form-control" type="email" id="email" name="email" required data-regle="email" value="<?= esc($valeur('email')) ?>">
                    <div class="invalid-feedback">Adresse e-mail valide.</div>
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="telephone">Téléphone</label>
                    <input class="form-control" type="tel" id="telephone" name="telephone" required pattern="0[1-9](?:[ .\-]?\d{2}){4}" data-regle="telephone" value="<?= esc($valeur('telephone')) ?>">
                    <div class="invalid-feedback">Numéro français à 10 chiffres.</div>
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="dateNaissance">Date de naissance</label>
                    <input class="form-control" type="date" id="dateNaissance" name="dateNaissance" required value="<?= esc($valeur('dateNaissance')) ?>">
                    <div class="form-text">La catégorie d'âge est recalculée à l'enregistrement.</div>
                </div>
                <div class="col-sm-6 d-flex align-items-end">
                    <input type="hidden" name="actif" value="0">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="actif" name="actif" value="1"<?= $valeur('actif') !== '0' ? ' checked' : '' ?>>
                        <label class="form-check-label" for="actif">Compte actif</label>
                    </div>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-robotix" type="submit">Enregistrer</button>
                    <a class="btn btn-outline-secondary" href="<?= site_url('admin/clients') ?>">Annuler</a>
                </div>
            </div>
        </form>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/validation.js') ?>"></script>
<?= $this->endSection() ?>
