<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="section">
    <div class="container">
        <form class="formulaire formulaire--etroit" action="<?= site_url('compte/mot-de-passe/' . esc($jeton, 'url')) ?>" method="post" data-valider novalidate>
            <?= csrf_field() ?>
            <h1 class="h3 mb-4">Nouveau mot de passe</h1>
            <div class="mb-3">
                <label class="form-label" for="motDePasse">Nouveau mot de passe</label>
                <input class="form-control" type="password" id="motDePasse" name="motDePasse" required minlength="8"
                       pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}" data-regle="motDePasse" autocomplete="new-password" aria-describedby="aide-mdp">
                <div class="form-text" id="aide-mdp">8 caractères minimum, avec une majuscule, une minuscule et un chiffre.</div>
                <div class="invalid-feedback">8 caractères minimum, avec une majuscule, une minuscule et un chiffre.</div>
            </div>
            <div class="mb-4">
                <label class="form-label" for="confirmation">Confirmation</label>
                <input class="form-control" type="password" id="confirmation" name="confirmation" required data-identique="motDePasse" autocomplete="new-password">
                <div class="invalid-feedback">Les deux mots de passe doivent être identiques.</div>
            </div>
            <button class="btn btn-robotix w-100" type="submit">Enregistrer</button>
        </form>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/validation.js') ?>"></script>
<?= $this->endSection() ?>
