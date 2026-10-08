<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="section">
    <div class="container">
        <form class="formulaire formulaire--etroit" action="<?= site_url('compte/mot-de-passe-oublie') ?>" method="post" data-valider novalidate>
            <?= csrf_field() ?>
            <h1 class="h3 mb-3">Mot de passe oublié</h1>
            <p>Saisissez l'adresse e-mail de votre compte : nous vous enverrons un lien pour choisir un nouveau mot de passe.</p>
            <div class="mb-4">
                <label class="form-label" for="email">E-mail</label>
                <input class="form-control" type="email" id="email" name="email" value="<?= old('email') ?>" required maxlength="150" autocomplete="email">
                <div class="invalid-feedback">Saisissez une adresse e-mail valide.</div>
            </div>
            <button class="btn btn-robotix w-100" type="submit">Recevoir un lien</button>
            <p class="text-center mt-3 mb-0"><a href="<?= site_url('compte/connexion') ?>">Retour à la connexion</a></p>
        </form>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/validation.js') ?>"></script>
<?= $this->endSection() ?>
