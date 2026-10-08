<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="en-tete-page">
    <div class="container">
        <h1>Animateurs du club</h1>
        <p>Adhérents entraîneurs, leur spécialité et leur remplaçant.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="table-responsive formulaire p-0 mb-5">
            <table class="table table-hover align-middle mb-0">
                <caption class="px-3"><?= count($animateurs) ?> animateur(s)</caption>
                <thead><tr><th scope="col">Animateur</th><th scope="col">Pseudo</th><th scope="col">Spécialité</th><th scope="col">Remplaçant</th><th scope="col" class="text-end">Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($animateurs as $animateur): ?>
                        <tr>
                            <th scope="row"><?= esc($animateur['prenom'] . ' ' . $animateur['nom']) ?></th>
                            <td><?= esc($animateur['pseudo'] ?? '—') ?></td>
                            <td><?= esc($animateur['specialite']) ?></td>
                            <td><?= esc($animateur['remplacant'] ?? 'Aucun') ?></td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-primary" href="<?= site_url('admin/club/animateurs/' . $animateur['idUtilisateur'] . '/modifier') ?>">Modifier</a>
                                <form class="d-inline" action="<?= site_url('admin/club/animateurs/' . $animateur['idUtilisateur'] . '/supprimer') ?>" method="post"
                                      data-confirm="Retirer <?= esc($animateur['prenom'] . ' ' . $animateur['nom']) ?> des animateurs ? Ses événements iront à son remplaçant.">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Retirer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <form class="formulaire formulaire--etroit" action="<?= site_url('admin/club/animateurs') ?>" method="post">
            <?= csrf_field() ?>
            <h2 class="h5 mb-3">Nommer un animateur</h2>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label" for="idUtilisateur">Adhérent</label>
                    <select class="form-select" id="idUtilisateur" name="idUtilisateur" required>
                        <option value="">Choisissez un client…</option>
                        <?php foreach ($candidats as $candidat): ?>
                            <option value="<?= $candidat['idUtilisateur'] ?>"><?= esc($candidat['nom'] . ' ' . $candidat['prenom'] . ' — ' . $candidat['email']) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="specialite">Spécialité</label>
                    <input class="form-control" type="text" id="specialite" name="specialite" required minlength="3" maxlength="80" value="<?= old('specialite') ?>">
                </div>
                <div class="col-sm-6">
                    <label class="form-label" for="idRemplacant">Remplaçant</label>
                    <select class="form-select" id="idRemplacant" name="idRemplacant">
                        <option value="">Aucun</option>
                        <?php foreach ($animateurs as $animateur): ?>
                            <option value="<?= $animateur['idUtilisateur'] ?>"><?= esc($animateur['prenom'] . ' ' . $animateur['nom']) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="col-12"><button class="btn btn-robotix" type="submit">Nommer</button></div>
            </div>
        </form>
    </div>
</section>

<?= $this->endSection() ?>
