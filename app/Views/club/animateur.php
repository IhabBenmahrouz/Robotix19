<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="en-tete-page">
    <div class="container">
        <h1>Espace animateur</h1>
        <p><?= esc($animateur['prenom'] . ' ' . $animateur['nom']) ?> — <?= esc($animateur['specialite']) ?> · remplaçant : <?= esc($animateur['remplacant'] ?? 'aucun') ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if ($evenements === []): ?>
            <p class="text-center">Aucun événement à animer pour le moment.</p>
        <?php endif ?>
        <?php foreach ($evenements as $evenement): ?>
            <article class="formulaire mb-4" id="evenement-<?= (int) $evenement['idEvenement'] ?>">
                <h2 class="h5"><span class="pastille pastille--<?= esc($evenement['type']) ?>"></span><?= esc($evenement['titre']) ?></h2>
                <p class="text-secondary">
                    <?= date_fr($evenement['dateDebut']) ?> — <?= esc($evenement['lieu'] ?? 'Hors showroom') ?> ·
                    <?= $evenement['role'] === 'titulaire' ? 'Vous animez cet événement' : 'Vous remplacez ' . esc($evenement['titulaire']) ?>
                </p>
                <?php if ($evenement['inscrits'] === []): ?>
                    <p class="mb-0">Aucun inscrit.</p>
                <?php else: ?>
                    <form action="<?= site_url('club/animateur/evenements/' . (int) $evenement['idEvenement'] . '/presences') ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <caption>Inscrits : présence et travail réalisé</caption>
                                <thead><tr><th scope="col">Membre</th><th scope="col">Niveau</th><th scope="col">Présence</th><th scope="col">Travail réalisé</th></tr></thead>
                                <tbody>
                                    <?php foreach ($evenement['inscrits'] as $inscrit): ?>
                                        <?php
                                        $id      = (int) $inscrit['idMembre'];
                                        $cle     = (int) $evenement['idEvenement'] . '-' . $id;
                                        $present = $inscrit['present'] === null ? '' : (string) (int) $inscrit['present'];
                                        ?>
                                        <tr>
                                            <th scope="row"><?= esc($inscrit['prenom'] . ' ' . $inscrit['nom']) ?></th>
                                            <td><?= esc($inscrit['niveau']) ?></td>
                                            <td>
                                                <label class="visually-hidden" for="present-<?= $cle ?>">Présence de <?= esc($inscrit['prenom']) ?></label>
                                                <select class="form-select form-select-sm" id="present-<?= $cle ?>" name="present[<?= $id ?>]">
                                                    <?php foreach (['' => 'Non pointé', '1' => 'Présent', '0' => 'Absent'] as $valeur => $libelle): ?>
                                                        <option value="<?= $valeur ?>"<?= $present === (string) $valeur ? ' selected' : '' ?>><?= $libelle ?></option>
                                                    <?php endforeach ?>
                                                </select>
                                            </td>
                                            <td>
                                                <label class="visually-hidden" for="travail-<?= $cle ?>">Travail réalisé par <?= esc($inscrit['prenom']) ?></label>
                                                <input class="form-control form-control-sm" type="text" id="travail-<?= $cle ?>" name="travail[<?= $id ?>]" maxlength="300" value="<?= esc($inscrit['travailRealise'] ?? '') ?>">
                                            </td>
                                        </tr>
                                    <?php endforeach ?>
                                </tbody>
                            </table>
                        </div>
                        <button class="btn btn-robotix" type="submit">Enregistrer les présences</button>
                    </form>
                <?php endif ?>
            </article>
        <?php endforeach ?>
    </div>
</section>

<?= $this->endSection() ?>
