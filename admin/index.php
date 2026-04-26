<?php
// admin/index.php - lista poster (aktiva eller arkiv) Ⓐ Style
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/../app/TodoRepository.php';

$repo  = new TodoRepository();
$view  = ($_GET['view'] ?? '') === 'arkiv' ? 'arkiv' : 'aktiva';
$items = $view === 'arkiv' ? $repo->archived() : $repo->active();

admin_header($view === 'arkiv' ? 'Arkiv' : 'Aktiva uppgifter');
?>
<div class="page-header">
    <h1><?= $view === 'arkiv' ? 'Arkiv' : 'Aktiva uppgifter' ?> <span class="count"><?= count($items) ?></span></h1>
</div>

<?php if (!$items): ?>
    <p class="muted">Inga uppgifter ännu. <a href="<?= e(url('admin/create.php')) ?>">Skapa första</a>.</p>
<?php else: ?>
<div class="table-wrap">
<table class="data-table">
    <thead>
        <tr>
            <th>Rubrik</th>
            <th>Kategori</th>
            <th>Status</th>
            <th>Start</th>
            <th>Slut</th>
            <th>Tid kvar</th>
            <th>Plats</th>
            <th>Aktiv</th>
            <th>Arkiv</th>
            <th class="row-actions">Åtgärder</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $t): ?>
        <tr>
            <td><strong><?= e($t['rubrik']) ?></strong></td>
            <td><?= e(trim(($t['kategori'] ?? '') . ' / ' . ($t['underkategori'] ?? ''), ' /')) ?></td>
            <td><span class="status-pill <?= e(status_class($t['status'])) ?>"><?= e(status_label($t['status'])) ?></span></td>
            <td><?= e(format_dt($t['datum_start'] ?? null)) ?></td>
            <td><?= e(format_dt($t['datum_slut'] ?? null)) ?></td>
            <td><?= e(tid_kvar_text($t['datum_slut'] ?? null)) ?></td>
            <td><?= e($t['plats'] ?? '') ?></td>
            <td><?= !empty($t['aktiv']) ? 'Ja' : 'Nej' ?></td>
            <td><?= !empty($t['arkiv']) ? 'Ja' : 'Nej' ?></td>
            <td class="row-actions">
                <a class="btn btn-sm" href="<?= e(url('index.php#'.$t['id'])) ?>">Visa</a>
                <a class="btn btn-sm" href="<?= e(url('admin/edit.php?id='.urlencode($t['id']))) ?>">Redigera</a>
                <?php if (empty($t['arkiv'])): ?>
                    <form method="post" action="<?= e(url('admin/archive.php')) ?>" style="display:inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= e($t['id']) ?>">
                        <button class="btn btn-sm" type="submit">Arkivera</button>
                    </form>
                <?php else: ?>
                    <form method="post" action="<?= e(url('admin/archive.php?unarchive=1')) ?>" style="display:inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= e($t['id']) ?>">
                        <button class="btn btn-sm" type="submit">Återställ</button>
                    </form>
                <?php endif; ?>
                <form method="post" action="<?= e(url('admin/delete.php')) ?>" style="display:inline"
                      onsubmit="return confirm('Vill du verkligen ta bort denna post?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= e($t['id']) ?>">
                    <button class="btn btn-sm btn-danger" type="submit">Ta bort</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>

<?php admin_footer();
