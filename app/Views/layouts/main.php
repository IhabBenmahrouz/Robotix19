<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= esc($description ?? 'Robotix : robots humanoïdes pour les particuliers.') ?>">
    <title><?= esc($titre ?? 'Robotix') ?> — Robotix</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&amp;family=Orbitron:wght@600;800&amp;display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= base_url('css/robotix.css') ?>">
</head>
<body>
    <a class="visually-hidden-focusable lien-evitement" href="#contenu">Aller au contenu</a>
    <?= $this->include('partials/header') ?>
    <main id="contenu">
        <?= $this->include('partials/flash') ?>
        <?= $this->renderSection('contenu') ?>
    </main>
    <?= $this->include('partials/footer') ?>
    <?= $this->include('partials/cookies') ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('js/consentement.js') ?>"></script>
    <script src="<?= base_url('js/main.js') ?>"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
