<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="en-tete-page">
    <div class="container">
        <h1>Crédits photos</h1>
        <p>Robotix est une boutique fictive, mais les robots, les marques et les photos sont réels.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <p>Toutes les photos de robots proviennent de <a href="https://commons.wikimedia.org" rel="noopener" target="_blank">Wikimedia Commons</a>
           et sont publiées sous licence libre (CC0, CC BY ou CC BY-SA). Elles sont reproduites ici avec la mention de leur auteur
           et de leur licence, conformément à ces licences. Les prix affichés dans la boutique sont fictifs.</p>
        <div class="table-responsive formulaire p-0 mt-4">
            <table class="table align-middle mb-0">
                <caption class="px-3"><?= count($photos) ?> photos</caption>
                <thead><tr><th scope="col">Photo</th><th scope="col">Robot</th><th scope="col">Description</th><th scope="col">Auteur</th><th scope="col">Licence</th><th scope="col">Source</th></tr></thead>
                <tbody>
                    <?php foreach ($photos as $photo): ?>
                        <tr>
                            <td><img class="credits__vignette" src="<?= base_url('images/robots/' . $photo['fichier']) ?>" alt="" width="64" height="80" loading="lazy"></td>
                            <th scope="row"><?= esc($photo['robot']) ?></th>
                            <td><?= esc($photo['legende']) ?></td>
                            <td><?= esc($photo['credit']) ?></td>
                            <td><?= esc($photo['licence']) ?></td>
                            <td><a href="<?= esc($photo['source']) ?>" rel="noopener" target="_blank">Page Commons</a></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
