<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="en-tete-page">
    <div class="container">
        <h1>Espace membre</h1>
        <p>Inscrivez-vous aux ateliers et suivez vos progrès au sein du Club Robotix.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="titre-section">Événements à venir</h2>
        <div class="table-responsive formulaire p-0 mb-5">
            <table class="table align-middle mb-0">
                <caption class="px-3">Ateliers, démonstrations, lancements et salons</caption>
                <thead><tr><th scope="col">Événement</th><th scope="col">Date</th><th scope="col">Lieu</th><th scope="col">Animateur</th><th scope="col" class="text-end">Places</th><th scope="col">Inscription</th></tr></thead>
                <tbody>
                    <?php foreach ($evenements as $evenement): ?>
                        <tr>
                            <th scope="row"><span class="pastille pastille--<?= esc($evenement['type']) ?>"></span><?= esc($evenement['titre']) ?></th>
                            <td><?= date_fr($evenement['dateDebut']) ?></td>
                            <td><?= esc($evenement['lieu'] ?? 'Hors showroom') ?></td>
                            <td><?= esc($evenement['animateur'] ?? '—') ?></td>
                            <td class="text-end"><?= max(0, (int) $evenement['placesRestantes']) ?></td>
                            <td>
                                <?php if (in_array((int) $evenement['idEvenement'], $inscrit, true)): ?>
                                    <span class="badge text-bg-success">Inscrit</span>
                                <?php elseif ((int) $evenement['placesRestantes'] <= 0): ?>
                                    <span class="badge text-bg-secondary">Complet</span>
                                <?php else: ?>
                                    <form action="<?= site_url('club/inscription') ?>" method="post">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="idEvenement" value="<?= (int) $evenement['idEvenement'] ?>">
                                        <button class="btn btn-sm btn-robotix" type="submit">S'inscrire</button>
                                    </form>
                                <?php endif ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <div class="row g-5">
            <div class="col-lg-6">
                <h2 class="h4">Mes événements suivis</h2>
                <?php if ($suivis === []): ?>
                    <p>Vous n'êtes inscrit à aucun événement pour le moment.</p>
                <?php else: ?>
                    <ul class="liste-suivis">
                        <?php foreach ($suivis as $suivi): ?>
                            <?php $classe = ['Présent' => 'text-bg-success', 'Absent' => 'text-bg-danger'][$suivi['presence']] ?? 'text-bg-secondary'; ?>
                            <li>
                                <strong><?= esc($suivi['titre']) ?></strong> — <?= date_fr($suivi['dateDebut'], false) ?>
                                <span class="badge <?= $classe ?>"><?= esc($suivi['presence']) ?></span>
                                <?php if ($suivi['travailRealise']): ?><br><small class="text-secondary"><?= esc($suivi['travailRealise']) ?></small><?php endif ?>
                            </li>
                        <?php endforeach ?>
                    </ul>
                <?php endif ?>
            </div>
            <div class="col-lg-6">
                <h2 class="h4">Les joueurs du club</h2>
                <table class="table table-sm align-middle">
                    <caption>Membres, niveau et nombre d'événements</caption>
                    <thead><tr><th scope="col">Membre</th><th scope="col">Niveau</th><th scope="col" class="text-end">Événements</th></tr></thead>
                    <tbody>
                        <?php foreach ($joueurs as $joueur): ?>
                            <tr>
                                <th scope="row"><?= esc($joueur['prenom'] . ' ' . $joueur['nom']) ?></th>
                                <td><?= esc($joueur['niveau']) ?></td>
                                <td class="text-end"><?= (int) $joueur['nbEvenements'] ?></td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
