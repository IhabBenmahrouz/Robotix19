<?php
$rubriques = [
    ''           => 'Accueil',
    'store'      => 'Store',
    'news'       => 'News',
    'club'       => 'Club',
    'tarifs'     => 'Tarifs',
    'evenements' => 'Événements',
    'showrooms'  => 'Showrooms',
    'contact'    => 'Contact',
];
$connecte = (bool) session('idUtilisateur');
?>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-nuit fixed-top" aria-label="Navigation principale">
        <div class="container">
            <a class="navbar-brand logo" href="<?= site_url('/') ?>">ROBOTIX<span>.</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu"
                    aria-controls="menu" aria-expanded="false" aria-label="Ouvrir le menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menu">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <?php foreach ($rubriques as $segment => $libelle): ?>
                        <?php $actif = menu_actif($segment); ?>
                        <li class="nav-item">
                            <a class="nav-link<?= $actif ? ' active' : '' ?>"<?= $actif ? ' aria-current="page"' : '' ?> href="<?= site_url($segment) ?>"><?= $libelle ?></a>
                        </li>
                    <?php endforeach ?>
                    <li class="nav-item dropdown">
                        <button class="nav-link dropdown-toggle btn btn-link<?= menu_actif('compte') || menu_actif('admin') ? ' active' : '' ?>"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?= $connecte ? esc(session('prenom')) : 'Compte' ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if ($connecte): ?>
                                <?php if (in_array('membre', (array) session('profilClub'), true)): ?>
                                    <li><a class="dropdown-item" href="<?= site_url('club/membres') ?>">Espace membre</a></li>
                                <?php endif ?>
                                <?php if (in_array('animateur', (array) session('profilClub'), true)): ?>
                                    <li><a class="dropdown-item" href="<?= site_url('club/animateur') ?>">Espace animateur</a></li>
                                <?php endif ?>
                                <?php if (session('role') === 'client'): ?>
                                    <li><a class="dropdown-item" href="<?= site_url('compte/profil') ?>">Mon profil</a></li>
                                    <li><a class="dropdown-item" href="<?= site_url('reservations') ?>">Réserver des places</a></li>
                                <?php endif ?>
                                <?php if (session('role') === 'admin'): ?>
                                    <li><a class="dropdown-item" href="<?= site_url('admin/evenements') ?>">Gérer le planning</a></li>
                                    <li><a class="dropdown-item" href="<?= site_url('admin/clients') ?>">Adhérents</a></li>
                                    <li><a class="dropdown-item" href="<?= site_url('admin/statistiques') ?>">Statistiques</a></li>
                                    <li><a class="dropdown-item" href="<?= site_url('admin/club/animateurs') ?>">Animateurs du club</a></li>
                                    <li><a class="dropdown-item" href="<?= site_url('admin/club/rapports') ?>">Rapports SQL Server</a></li>
                                    <li><a class="dropdown-item" href="<?= site_url('admin/journal') ?>">Journal des actions</a></li>
                                    <li><a class="dropdown-item" href="<?= site_url('admin/demo') ?>">Jeu de démonstration</a></li>
                                <?php endif ?>
                                <li>
                                    <form action="<?= site_url('compte/deconnexion') ?>" method="post">
                                        <?= csrf_field() ?>
                                        <button class="dropdown-item" type="submit">Déconnexion</button>
                                    </form>
                                </li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="<?= site_url('compte/connexion') ?>">Connexion</a></li>
                                <li><a class="dropdown-item" href="<?= site_url('compte/inscription') ?>">Créer un compte</a></li>
                            <?php endif ?>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>
