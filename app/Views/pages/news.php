<?= $this->extend('layouts/main') ?>
<?= $this->section('contenu') ?>

<section class="en-tete-page">
    <div class="container">
        <h1>Robotix News</h1>
        <p>L'actualité des robots humanoïdes vendus sur Robotix Store.</p>
    </div>
</section>

<section class="section">
    <div class="container text-center">
        <p class="lead">Nos rédacteurs préparent les premiers articles : tests, comparatifs et coulisses des lancements.</p>
        <p>En attendant, retrouvez les robots en démonstration lors de nos prochains événements.</p>
        <a class="btn btn-robotix" href="<?= site_url('evenements') ?>">Voir le calendrier</a>
    </div>
</section>

<?= $this->endSection() ?>
