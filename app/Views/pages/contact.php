<?php
$objetActuel = old('objet', $objetChoisi);
$robotActuel = (int) old('idProduit', (string) $robotChoisi);
$classe = static fn (string $champ): string => validation_show_error($champ) !== '' ? ' is-invalid' : '';
?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="en-tete-page">
    <div class="container">
        <h1>Contact</h1>
        <p>Une démonstration, un devis, une question ? Nous vous répondons sous 48 heures.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-8">
                <form class="formulaire" action="<?= site_url('contact') ?>" method="post" data-valider novalidate>
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="nom">Nom et prénom *</label>
                            <input class="form-control<?= $classe('nom') ?>" type="text" id="nom" name="nom" value="<?= old('nom') ?>"
                                   required minlength="2" maxlength="50" pattern="[A-Za-zÀ-ÖØ-öø-ÿ' \-]{2,50}" data-regle="nom" autocomplete="name">
                            <div class="invalid-feedback"><?= validation_show_error('nom') ?: 'Lettres, espaces, apostrophes et tirets uniquement (2 à 50 caractères).' ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="email">E-mail *</label>
                            <input class="form-control<?= $classe('email') ?>" type="email" id="email" name="email" value="<?= old('email') ?>"
                                   required maxlength="150" data-regle="email" autocomplete="email">
                            <div class="invalid-feedback"><?= validation_show_error('email') ?: 'Saisissez une adresse e-mail valide (ex. : nom@exemple.fr).' ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="telephone">Téléphone</label>
                            <input class="form-control<?= $classe('telephone') ?>" type="tel" id="telephone" name="telephone" value="<?= old('telephone') ?>"
                                   pattern="0[1-9](?:[ .\-]?\d{2}){4}" data-regle="telephone" autocomplete="tel">
                            <div class="invalid-feedback"><?= validation_show_error('telephone') ?: 'Numéro français à 10 chiffres (ex. : 06 12 34 56 78).' ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="objet">Objet *</label>
                            <select class="form-select<?= $classe('objet') ?>" id="objet" name="objet" required>
                                <option value="">Choisissez…</option>
                                <?php foreach ($objets as $code => $libelle): ?>
                                    <option value="<?= $code ?>"<?= $objetActuel === $code ? ' selected' : '' ?>><?= esc($libelle) ?></option>
                                <?php endforeach ?>
                            </select>
                            <div class="invalid-feedback"><?= validation_show_error('objet') ?: 'Choisissez l\'objet de votre demande.' ?></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="idProduit">Robot concerné</label>
                            <select class="form-select<?= $classe('idProduit') ?>" id="idProduit" name="idProduit">
                                <option value="">Aucun en particulier</option>
                                <?php foreach ($robots as $robot): ?>
                                    <option value="<?= $robot['idProduit'] ?>"<?= (int) $robot['idProduit'] === $robotActuel ? ' selected' : '' ?>><?= esc($robot['nom']) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="message">Message *</label>
                            <textarea class="form-control<?= $classe('message') ?>" id="message" name="message" rows="6"
                                      required minlength="20" maxlength="2000" data-regle="message"><?= old('message') ?></textarea>
                            <div class="invalid-feedback"><?= validation_show_error('message') ?: 'Votre message doit contenir entre 20 et 2 000 caractères.' ?></div>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input<?= $classe('rgpd') ?>" type="checkbox" id="rgpd" name="rgpd" value="1" required>
                                <label class="form-check-label" for="rgpd">J'accepte que Robotix utilise ces informations pour me répondre. *</label>
                                <div class="invalid-feedback"><?= validation_show_error('rgpd') ?: 'Merci d\'accepter le traitement de vos données.' ?></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-robotix" type="submit">Envoyer</button>
                            <p class="small text-secondary mt-2 mb-0">* Champs obligatoires</p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-lg-4">
                <aside class="univers">
                    <h2 class="h5">Nous rencontrer</h2>
                    <p>Nos conseillers vous accueillent du mardi au samedi, de 10 h à 19 h, à Paris, Lyon et Marseille.</p>
                    <a href="<?= site_url('showrooms') ?>">Trouver un showroom →</a>
                </aside>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/validation.js') ?>"></script>
<?= $this->endSection() ?>
