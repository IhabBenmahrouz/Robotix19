<?php
/** @var array $robot ligne issue de ProduitModel */
$lien = site_url('store/robot/' . $robot['idProduit']);
?>
<article class="carte-robot">
    <a class="carte-robot__visuel" href="<?= $lien ?>" tabindex="-1" aria-hidden="true">
        <img class="img-fluid carte-robot__img" src="<?= base_url('images/robots/' . ($robot['image'] ?? 'defaut.svg')) ?>"
             alt="" width="400" height="500" loading="lazy">
        <?php if (! empty($robot['image2'])): ?>
            <img class="img-fluid carte-robot__img carte-robot__img--survol" src="<?= base_url('images/robots/' . $robot['image2']) ?>"
                 alt="" width="400" height="500" loading="lazy">
        <?php endif ?>
    </a>
    <div class="carte-robot__corps">
        <p class="carte-robot__marque"><?= esc($robot['marque']) ?> · <?= esc($robot['categorie']) ?></p>
        <h3 class="carte-robot__nom"><a href="<?= $lien ?>"><?= esc($robot['nom']) ?></a></h3>
        <p class="carte-robot__prix"><?= euros(prix_ttc($robot['prixHt'], $robot['tauxTva'])) ?> <small>TTC</small></p>
    </div>
</article>
