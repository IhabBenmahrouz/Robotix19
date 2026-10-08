<?php
/** Pagination au style Bootstrap. @var \CodeIgniter\Pager\PagerRenderer $pager */
$pager->setSurroundCount(2);
?>
<nav aria-label="Pagination">
    <ul class="pagination justify-content-center mb-0">
        <?php if ($pager->hasPrevious()): ?>
            <li class="page-item"><a class="page-link" href="<?= $pager->getFirst() ?>">« Début</a></li>
        <?php endif ?>
        <?php foreach ($pager->links() as $lien): ?>
            <li class="page-item<?= $lien['active'] ? ' active' : '' ?>">
                <a class="page-link" href="<?= $lien['uri'] ?>"<?= $lien['active'] ? ' aria-current="page"' : '' ?>><?= $lien['title'] ?></a>
            </li>
        <?php endforeach ?>
        <?php if ($pager->hasNext()): ?>
            <li class="page-item"><a class="page-link" href="<?= $pager->getLast() ?>">Fin »</a></li>
        <?php endif ?>
    </ul>
</nav>
