<?php
/** @var \App\Libraries\CalendrierDemo $calendrier */
$jour = static fn (string $date): string => date('d/m/Y', strtotime($calendrier->date($date)));
?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="en-tete-page">
    <div class="container">
        <h1>À propos du projet</h1>
        <p>Robotix19 est un projet de BTS SIO (option SLAM) : une boutique fictive de robots humanoïdes pour les particuliers, avec son club, construite avec CodeIgniter 4 et SQL Server.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="mb-4">Le vrai et le fictif</h2>
        <div class="row g-4">
            <div class="col-md-6">
                <article class="univers h-100">
                    <h3 class="h5">Réel et vérifié</h3>
                    <ul class="mb-0">
                        <li>Les robots, leurs caractéristiques et leur statut commercial</li>
                        <li>Les marques, leur siège social et leur site officiel</li>
                        <li>Les photos, sous licence libre, avec leurs <a href="<?= site_url('credits') ?>">crédits</a></li>
                    </ul>
                </article>
            </div>
            <div class="col-md-6">
                <article class="univers univers--ambre h-100">
                    <h3 class="h5">Fictif (pour la démonstration)</h3>
                    <ul class="mb-0">
                        <li>La boutique Robotix, ses prix et ses stocks</li>
                        <li>Les showrooms de Lyon, Paris et Marseille</li>
                        <li>Le club, ses membres, ses animateurs et ses événements</li>
                    </ul>
                </article>
            </div>
        </div>

        <h2 class="mt-5 mb-4">En chiffres</h2>
        <div class="row g-3">
            <?php foreach ($chiffres as $libelle => $nombre): ?>
                <div class="col-6 col-md-4 col-lg-2">
                    <article class="univers univers--violet h-100 text-center">
                        <p class="stat__valeur mb-0"><?= $nombre ?></p>
                        <p class="small mb-0"><?= esc($libelle) ?></p>
                    </article>
                </div>
            <?php endforeach ?>
        </div>

        <h2 class="mt-5 mb-3">Technologies</h2>
        <ul>
            <li><strong>Serveur :</strong> PHP 8.1, CodeIgniter 4.6 (MVC, filtres, Query Builder), couche PDO avec requêtes préparées</li>
            <li><strong>Base de données :</strong> SQL Server 2022 — migrations, procédures stockées, déclencheurs et vues en T-SQL</li>
            <li><strong>Interface :</strong> HTML5 validé W3C, Bootstrap 5.3 et CSS personnel, JavaScript sans bibliothèque (modules testés avec Node)</li>
            <li><strong>Qualité :</strong> tests automatisés PHPUnit et <code>node:test</code>, chaque test tourne dans une transaction annulée</li>
        </ul>

        <h2 class="mt-5 mb-3">Comptes de démonstration</h2>
        <p>Mot de passe commun : <code>Robotix2026!</code> — connexion par e-mail ou par pseudo.</p>
        <div class="table-responsive formulaire p-0">
            <table class="table align-middle mb-0">
                <thead><tr><th scope="col">Rôle</th><th scope="col">Identifiant</th><th scope="col">À montrer</th></tr></thead>
                <tbody>
                    <tr><th scope="row">Administrateur</th><td><code>admin@robotix.test</code></td><td>Planning, adhérents, statistiques, animateurs, rapports SQL Server, journal des actions, jeu de démonstration</td></tr>
                    <tr><th scope="row">Animateur</th><td><code>karim.h</code></td><td>Événements animés, pointage des présences et du travail réalisé</td></tr>
                    <tr><th scope="row">Membre du club</th><td><code>camille</code></td><td>Inscription aux événements, événements suivis, réservations</td></tr>
                    <tr><th scope="row">Client hors club</th><td><code>jules.moreau@exemple.fr</code></td><td>Profil, choix d'une formule d'adhésion</td></tr>
                </tbody>
            </table>
        </div>

        <h2 class="mt-5 mb-3">Parcours conseillé</h2>
        <ol>
            <li><a href="<?= site_url('store') ?>">Store</a> : catalogue filtrable, fiche d'un robot avec anatomie interactive et galerie.</li>
            <li><a href="<?= site_url('tarifs') ?>">Tarifs</a> : calculateur JavaScript (options, quantité, financement).</li>
            <li><a href="<?= site_url('evenements') ?>">Événements</a> : planning par mois — prochaine démonstration le <?= $jour('2026-10-08') ?>.</li>
            <li><a href="<?= site_url('showrooms') ?>">Showrooms</a> : carte Google Maps, chargée seulement après accord sur les cookies.</li>
            <li>Connecté en <code>camille</code> : <a href="<?= site_url('club/membres') ?>">espace membre</a>, inscription à un événement (un déclencheur crée le mail de confirmation).</li>
            <li>Connecté en <code>admin@robotix.test</code> : <a href="<?= site_url('admin/club/rapports') ?>">rapports SQL Server</a> — ordre du jour de la réunion du <?= $jour('2026-09-30') ?>, événements suivis entre le <?= $jour('2026-09-01') ?> et le <?= $jour('2026-09-30') ?>.</li>
        </ol>
        <p class="small text-secondary">Les dates du jeu d'essai avancent avec le calendrier (décalage actuel : <?= $calendrier->semaines() ?> semaine(s)) : les événements restent toujours à venir.</p>
    </div>
</section>

<?= $this->endSection() ?>
