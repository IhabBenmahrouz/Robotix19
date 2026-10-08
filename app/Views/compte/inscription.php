<?php
$classe = static fn (string $champ): string => validation_show_error($champ) !== '' ? ' is-invalid' : '';
$champ = static function (string $nom, string $libelle, string $attributs, string $aide) use ($classe): string {
    $erreur = validation_show_error($nom);

    return '<label class="form-label" for="' . $nom . '">' . $libelle . '</label>'
        . '<input class="form-control' . $classe($nom) . '" id="' . $nom . '" name="' . $nom . '" ' . $attributs . '>'
        . '<div class="invalid-feedback">' . ($erreur !== '' ? $erreur : esc($aide)) . '</div>';
};
?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="section">
    <div class="container">
        <form class="formulaire formulaire--etroit" action="<?= site_url('compte/inscription') ?>" method="post"
              enctype="multipart/form-data" data-valider data-adhesion novalidate>
            <?= csrf_field() ?>
            <h1 class="h3 mb-1">Adhérer au Club Robotix</h1>
            <p class="text-secondary mb-4">Ateliers, démonstrations et réservations de places : votre compte client et votre adhésion en une étape.</p>
            <div class="row g-3">
                <div class="col-sm-6"><?= $champ('prenom', 'Prénom *', 'type="text" required maxlength="50" pattern="[A-Za-zÀ-ÖØ-öø-ÿ\' \-]{2,50}" data-regle="nom" autocomplete="given-name" value="' . old('prenom') . '"', 'Lettres uniquement (2 à 50 caractères).') ?></div>
                <div class="col-sm-6"><?= $champ('nom', 'Nom *', 'type="text" required maxlength="50" pattern="[A-Za-zÀ-ÖØ-öø-ÿ\' \-]{2,50}" data-regle="nom" autocomplete="family-name" value="' . old('nom') . '"', 'Lettres uniquement (2 à 50 caractères).') ?></div>
                <div class="col-sm-6"><?= $champ('email', 'E-mail *', 'type="email" required maxlength="150" data-regle="email" autocomplete="email" value="' . old('email') . '"', 'Adresse e-mail valide (ex. : nom@exemple.fr).') ?></div>
                <div class="col-sm-6"><?= $champ('telephone', 'Téléphone *', 'type="tel" required pattern="0[1-9](?:[ .\-]?\d{2}){4}" data-regle="telephone" autocomplete="tel" value="' . old('telephone') . '"', 'Numéro français à 10 chiffres.') ?></div>
                <div class="col-12"><?= $champ('adresse', 'Adresse *', 'type="text" required minlength="5" maxlength="120" data-regle="adresse" autocomplete="address-line1" value="' . old('adresse') . '"', 'Numéro et nom de rue.') ?></div>
                <div class="col-sm-4"><?= $champ('codePostal', 'Code postal *', 'type="text" required inputmode="numeric" pattern="\d{5}" data-regle="codePostal" autocomplete="postal-code" value="' . old('codePostal') . '"', '5 chiffres.') ?></div>
                <div class="col-sm-8"><?= $champ('ville', 'Ville *', 'type="text" required maxlength="80" data-regle="ville" autocomplete="address-level2" value="' . old('ville') . '"', 'Nom de la ville.') ?></div>
                <div class="col-sm-6"><?= $champ('dateNaissance', 'Date de naissance *', 'type="date" required max="' . date('Y-m-d') . '" autocomplete="bday" value="' . old('dateNaissance') . '"', 'Vous devez avoir au moins 18 ans.') ?></div>
                <div class="col-sm-6">
                    <label class="form-label" for="photo">Photo (facultative)</label>
                    <input class="form-control<?= $classe('photo') ?>" type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp">
                    <div class="invalid-feedback"><?= validation_show_error('photo') ?: 'Image JPG, PNG ou WebP de 2 Mo maximum.' ?></div>
                </div>
                <div class="col-12">
                    <fieldset>
                        <legend class="form-label">Formule d'adhésion *</legend>
                        <p class="small text-secondary mb-2" id="resume-adhesion" aria-live="polite">Indiquez votre date de naissance pour connaître votre catégorie.</p>
                        <div class="formules">
                            <?php foreach ($formules as $index => $formule): ?>
                                <?php $coche = old('idTarif') !== null ? (int) old('idTarif') === $formule['idTarif'] : $index === 1; ?>
                                <label class="formule" for="formule-<?= $formule['code'] ?>">
                                    <input class="form-check-input" type="radio" name="idTarif" id="formule-<?= $formule['code'] ?>" value="<?= $formule['idTarif'] ?>"<?= $coche ? ' checked' : '' ?> required>
                                    <span class="formule__nom"><?= esc($formule['libelle']) ?></span>
                                    <span class="formule__prix" data-prix-formule="<?= $formule['valeur'] ?>"><?= euros($formule['valeur']) ?></span>
                                    <span class="formule__detail"><?= esc($formule['description']) ?></span>
                                </label>
                            <?php endforeach ?>
                        </div>
                        <div class="text-danger small"><?= validation_show_error('idTarif') ?></div>
                    </fieldset>
                </div>
                <div class="col-12">
                    <fieldset>
                        <legend class="form-label">Centres d'intérêt</legend>
                        <?php $choisis = array_map('intval', (array) old('interets')); ?>
                        <?php foreach ($interets as $interet): ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="interets[]" id="interet-<?= $interet['idCategorie'] ?>"
                                       value="<?= $interet['idCategorie'] ?>"<?= in_array((int) $interet['idCategorie'], $choisis, true) ? ' checked' : '' ?>>
                                <label class="form-check-label" for="interet-<?= $interet['idCategorie'] ?>"><?= esc($interet['libelle']) ?></label>
                            </div>
                        <?php endforeach ?>
                    </fieldset>
                </div>
                <div class="col-sm-6"><?= $champ('motDePasse', 'Mot de passe *', 'type="password" required minlength="8" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}" data-regle="motDePasse" autocomplete="new-password"', '8 caractères minimum, avec une majuscule, une minuscule et un chiffre.') ?></div>
                <div class="col-sm-6"><?= $champ('confirmation', 'Confirmation *', 'type="password" required data-identique="motDePasse" autocomplete="new-password"', 'Les deux mots de passe doivent être identiques.') ?></div>
                <div class="col-12">
                    <button class="btn btn-robotix w-100" type="submit">Adhérer au club</button>
                    <p class="text-center mt-3 mb-0">Déjà client ? <a href="<?= site_url('compte/connexion') ?>">Se connecter</a></p>
                </div>
            </div>
            <script type="application/json" id="donnees-adhesion"><?= json_encode(['categories' => $categoriesAge], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?></script>
        </form>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/validation.js') ?>"></script>
<script src="<?= base_url('js/adhesion.js') ?>"></script>
<?= $this->endSection() ?>
