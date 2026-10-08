<?php foreach (['succes' => 'success', 'erreur' => 'danger'] as $cle => $classe): ?>
    <?php if ($message = session()->getFlashdata($cle)): ?>
        <div class="container mt-3">
            <div class="alert alert-<?= $classe ?> alert-dismissible fade show" role="alert">
                <?= esc($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        </div>
    <?php endif ?>
<?php endforeach ?>
