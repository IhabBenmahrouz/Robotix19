<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="section">
    <div class="container">
        <form class="formulaire formulaire--etroit" action="<?= site_url('compte/connexion') ?>" method="post" data-valider novalidate>
            <?= csrf_field() ?>
            <h1 class="h3 mb-4">Connexion</h1>
            <div class="mb-3">
                <label class="form-label" for="identifiant">E-mail ou pseudo</label>
                <input class="form-control" type="text" id="identifiant" name="identifiant" value="<?= old('identifiant') ?>" required minlength="3" maxlength="150" autocomplete="username">
                <div class="invalid-feedback">Saisissez votre e-mail ou votre pseudo.</div>
            </div>
            <div class="mb-4">
                <label class="form-label" for="motDePasse">Mot de passe</label>
                <input class="form-control" type="password" id="motDePasse" name="motDePasse" required autocomplete="current-password">
                <div class="invalid-feedback">Saisissez votre mot de passe.</div>
                <p class="small text-end mt-1 mb-0"><a href="<?= site_url('compte/mot-de-passe-oublie') ?>">Mot de passe oublié ?</a></p>
            </div>
            <button class="btn btn-robotix w-100" type="submit">Se connecter</button>
            <p class="text-center mt-3 mb-0">Pas encore de compte ? <a href="<?= site_url('compte/inscription') ?>">Créer un compte</a></p>
        </form>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/validation.js') ?>"></script>
<?= $this->endSection() ?>
