<?php
$selectMembres = static function (string $nom, int $choisi) use ($membres): string {
    $html = '<select class="form-select form-select-sm" id="' . $nom . '" name="' . $nom . '">';
    foreach ($membres as $membre) {
        $id    = (int) $membre['idUtilisateur'];
        $html .= '<option value="' . $id . '"' . ($id === $choisi ? ' selected' : '') . '>' . esc($membre['prenom'] . ' ' . $membre['nom']) . '</option>';
    }

    return $html . '</select>';
};
?>
<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="en-tete-page">
    <div class="container">
        <h1>Rapports du club</h1>
        <p>Résultats des procédures stockées, des vues et des déclencheurs SQL Server de l'AP3.</p>
    </div>
</section>

<section class="section">
    <div class="container rapports">
        <form class="filtres row g-3 align-items-end mb-5" method="get" action="<?= site_url('admin/club/rapports') ?>">
            <div class="col-sm-4 col-lg-2"><label class="form-label" for="annee">Année</label>
                <input class="form-control form-control-sm" type="number" id="annee" name="annee" min="2000" max="2100" value="<?= $p['annee'] ?>"></div>
            <div class="col-sm-4 col-lg-2"><label class="form-label" for="date">Date de réunion</label>
                <input class="form-control form-control-sm" type="date" id="date" name="date" value="<?= $p['date'] ?>"></div>
            <div class="col-sm-4 col-lg-2"><label class="form-label" for="idAdherent">Adhérent</label><?= $selectMembres('idAdherent', $p['idAdherent']) ?></div>
            <div class="col-sm-4 col-lg-2"><label class="form-label" for="idMembre">Joueur</label><?= $selectMembres('idMembre', $p['idMembre']) ?></div>
            <div class="col-sm-4 col-lg-1"><label class="form-label" for="debut">Du</label>
                <input class="form-control form-control-sm" type="date" id="debut" name="debut" value="<?= $p['debut'] ?>"></div>
            <div class="col-sm-4 col-lg-1"><label class="form-label" for="fin">Au</label>
                <input class="form-control form-control-sm" type="date" id="fin" name="fin" value="<?= $p['fin'] ?>"></div>
            <div class="col-sm-4 col-lg-2 d-grid"><button class="btn btn-robotix btn-sm" type="submit">Actualiser</button></div>
        </form>

        <div class="row g-4">
            <article class="col-lg-6"><div class="formulaire h-100">
                <h2 class="h5">Adhérents ayant renouvelé en <?= $p['annee'] ?></h2>
                <p class="small text-secondary"><code>EXEC ps_AdherentsRenouveles @annee = <?= $p['annee'] ?></code></p>
                <table class="table table-sm"><caption><?= count($renouveles) ?> adhérent(s)</caption>
                    <thead><tr><th scope="col">Adhérent</th><th scope="col"><?= $p['annee'] - 1 ?></th><th scope="col"><?= $p['annee'] ?></th></tr></thead>
                    <tbody><?php foreach ($renouveles as $r): ?><tr><th scope="row"><?= esc($r['nom'] . ' ' . $r['prenom']) ?></th><td><?= esc($r['formulePrecedente']) ?></td><td><?= esc($r['formuleAnnee']) ?></td></tr><?php endforeach ?></tbody>
                </table>
            </div></article>

            <article class="col-lg-6"><div class="formulaire h-100">
                <h2 class="h5">Ordre du jour du <?= date_fr($p['date'] . ' 00:00:00', false) ?></h2>
                <p class="small text-secondary"><code>EXEC ps_OrdreDuJour @date = '<?= $p['date'] ?>'</code></p>
                <?php if ($ordreDuJour === []): ?>
                    <p>Aucune réunion ce jour-là. Réunions prévues :
                        <?php foreach ($reunions as $reunion): ?><br>· <?= date_fr($reunion['dateReunion']) ?> — <?= esc($reunion['objet']) ?> (<?= (int) $reunion['nbConvoques'] ?> convoqué(s), <?= (int) $reunion['nbPoints'] ?> point(s))<?php endforeach ?>
                    </p>
                <?php else: ?>
                    <p class="mb-2"><strong><?= esc($ordreDuJour[0]['objet']) ?></strong> à <?= esc($ordreDuJour[0]['heure']) ?></p>
                    <ol class="mb-0"><?php foreach ($ordreDuJour as $point): ?><li><?= esc($point['libelle']) ?></li><?php endforeach ?></ol>
                <?php endif ?>
            </div></article>

            <article class="col-lg-6"><div class="formulaire h-100">
                <h2 class="h5">Événements suivis par un adhérent</h2>
                <p class="small text-secondary"><code>EXEC ps_EvenementsSuivis @idAdherent = <?= $p['idAdherent'] ?></code></p>
                <table class="table table-sm"><caption><?= count($suivis) ?> événement(s)</caption>
                    <thead><tr><th scope="col">Événement</th><th scope="col">Présence</th><th scope="col">Travail réalisé</th></tr></thead>
                    <tbody><?php foreach ($suivis as $s): ?><tr><th scope="row"><?= esc($s['titre']) ?><br><small class="text-secondary"><?= esc($s['prenom'] . ' ' . $s['nom']) ?> · <?= date_fr($s['dateDebut'], false) ?></small></th><td><?= esc($s['presence']) ?></td><td><?= esc($s['travailRealise'] ?? '—') ?></td></tr><?php endforeach ?></tbody>
                </table>
            </div></article>

            <article class="col-lg-6"><div class="formulaire h-100">
                <h2 class="h5">Événements suivis entre deux dates</h2>
                <p class="small text-secondary"><code>EXEC ps_NbEvenementsEntreDates @idMembre = <?= $p['idMembre'] ?>, @debut = '<?= $p['debut'] ?>', @fin = '<?= $p['fin'] ?>', @nb = @nb OUTPUT</code></p>
                <p class="stat__valeur mb-1"><strong id="nb-evenements"><?= $nbEntre ?></strong> événement(s)</p>
                <p class="small mb-4">avec présence, du <?= date_fr($p['debut'] . ' 00:00:00', false) ?> au <?= date_fr($p['fin'] . ' 00:00:00', false) ?></p>
                <h2 class="h5">Heures d'entraînement par joueur</h2>
                <p class="small text-secondary"><code>EXEC ps_HeuresEntrainement</code></p>
                <table class="table table-sm"><caption>Heures d'atelier avec présence</caption>
                    <thead><tr><th scope="col">Joueur</th><th scope="col" class="text-end">Heures</th></tr></thead>
                    <tbody><?php foreach ($heures as $h): ?><tr><th scope="row"><?= esc($h['prenom'] . ' ' . $h['nom']) ?></th><td class="text-end"><?= number_format((float) $h['heures'], 2, ',', ' ') ?> h</td></tr><?php endforeach ?></tbody>
                </table>
            </div></article>

            <article class="col-lg-6"><div class="formulaire h-100">
                <h2 class="h5">Adhérents entraîneurs et joueurs</h2>
                <p class="small text-secondary"><code>SELECT * FROM v_AdherentsRoles</code></p>
                <table class="table table-sm"><caption><?= count($roles) ?> ligne(s)</caption>
                    <thead><tr><th scope="col">Adhérent</th><th scope="col">Rôle</th><th scope="col">Détail</th></tr></thead>
                    <tbody><?php foreach ($roles as $r): ?><tr><th scope="row"><?= esc($r['prenom'] . ' ' . $r['nom']) ?></th><td><?= esc($r['role']) ?></td><td><?= esc($r['detail']) ?></td></tr><?php endforeach ?></tbody>
                </table>
            </div></article>

            <article class="col-lg-6"><div class="formulaire h-100">
                <h2 class="h5">Événements avec les personnes présentes</h2>
                <p class="small text-secondary"><code>SELECT * FROM v_EvenementsPresents</code></p>
                <?php foreach ($presents as $titreEvenement => $liste): ?>
                    <p class="mb-1"><strong><?= esc($titreEvenement) ?></strong></p>
                    <ul class="small"><?php foreach ($liste as $present): ?><li><?= esc($present['prenom'] . ' ' . $present['nom']) ?><?= $present['travailRealise'] ? ' — ' . esc($present['travailRealise']) : '' ?></li><?php endforeach ?></ul>
                <?php endforeach ?>
            </div></article>

            <article class="col-lg-6"><div class="formulaire h-100">
                <h2 class="h5">Mails générés</h2>
                <p class="small text-secondary">Table <code>MailAEnvoyer</code> : déclencheur <code>trg_Inscription_Mail</code> et liens « mot de passe oublié »</p>
                <ul class="small mb-0"><?php foreach ($mails as $mail): ?><li><strong><?= esc($mail['objet']) ?></strong> → <?= esc($mail['destinataire']) ?><br><span class="text-secondary"><?= esc($mail['corps']) ?></span></li><?php endforeach ?></ul>
            </div></article>

            <article class="col-lg-6"><div class="formulaire h-100">
                <h2 class="h5">Adhésions non renouvelées</h2>
                <p class="small text-secondary">Déclencheur <code>trg_Utilisateur_Historisation</code> → table <code>HistoriqueAdhesion</code> (se remplit quand un adhérent sans adhésion cette année est désactivé)</p>
                <?php if ($historique === []): ?>
                    <p class="mb-0">Aucun historique pour le moment. Désactivez par exemple Jules Moreau dans « Adhérents ».</p>
                <?php else: ?>
                    <ul class="small mb-0"><?php foreach ($historique as $h): ?><li><?= esc($h['prenom'] . ' ' . $h['nom']) ?> — dernière adhésion <?= esc((string) ($h['derniereAnnee'] ?? '—')) ?> — <?= date_fr($h['dateHistorisation']) ?></li><?php endforeach ?></ul>
                <?php endif ?>
            </div></article>
        </div>
    </div>
</section>

<?= $this->endSection() ?>
