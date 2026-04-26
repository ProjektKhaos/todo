<?php
// admin/users.php - hantera användare (endast ägare) Ⓐ Style
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/../app/UserRepository.php';
require_owner();

$users = new UserRepository();
$me = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = (string)($_POST['action'] ?? '');
    try {
        switch ($action) {
            case 'create':
                $u = $users->create(
                    (string)($_POST['username'] ?? ''),
                    (string)($_POST['name'] ?? ''),
                    (string)($_POST['password'] ?? '')
                );
                flash_set('ok', 'Skapade användaren ' . $u['username'] . '.');
                break;

            case 'delete':
                $id = (string)($_POST['id'] ?? '');
                if ($id === ($me['id'] ?? '')) {
                    throw new RuntimeException('Du kan inte ta bort dig själv.');
                }
                $users->delete($id);
                flash_set('ok', 'Användaren togs bort.');
                break;

            case 'set_password':
                $id = (string)($_POST['id'] ?? '');
                $users->setPassword($id, (string)($_POST['password'] ?? ''));
                flash_set('ok', 'Lösenord uppdaterat.');
                break;

            default:
                throw new RuntimeException('Okänd åtgärd.');
        }
    } catch (Throwable $ex) {
        flash_set('error', $ex->getMessage());
    }
    redirect('admin/users.php');
}

$list = $users->all();
admin_header('Användare');
?>
<div class="page-header"><h1>Användare <span class="count"><?= count($list) ?></span></h1></div>

<section class="form-card">
    <h2 style="margin-top:0">Lägg till användare</h2>
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <div class="grid">
            <label>Användarnamn
                <input type="text" name="username" required minlength="3" maxlength="32" pattern="[a-z0-9._\-]{3,32}">
            </label>
            <label>Namn
                <input type="text" name="name" required maxlength="80">
            </label>
            <label class="span-2">Lösenord (minst 8 tecken)
                <input type="password" name="password" required minlength="8" autocomplete="new-password">
            </label>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Skapa användare</button>
        </div>
    </form>
</section>

<div class="table-wrap" style="margin-top:18px">
<table class="data-table">
    <thead>
        <tr><th>Användarnamn</th><th>Namn</th><th>Roll</th><th>Skapad</th><th class="row-actions">Åtgärder</th></tr>
    </thead>
    <tbody>
    <?php foreach ($list as $u): $isOwner = ($u['role'] ?? '') === 'owner'; $isMe = ($u['id'] ?? '') === ($me['id'] ?? ''); ?>
        <tr>
            <td><strong><?= e($u['username']) ?></strong></td>
            <td><?= e($u['name'] ?? '') ?></td>
            <td><span class="status-pill <?= $isOwner ? 'status-klar' : '' ?>"><?= $isOwner ? 'Ägare' : 'Admin' ?></span></td>
            <td><?= e($u['created_at'] ?? '') ?></td>
            <td class="row-actions">
                <details style="display:inline-block">
                    <summary class="btn btn-sm">Byt lösenord</summary>
                    <form method="post" style="margin-top:6px; display:flex; gap:6px;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="set_password">
                        <input type="hidden" name="id" value="<?= e($u['id']) ?>">
                        <input type="password" name="password" placeholder="Nytt lösen ≥8" minlength="8" required>
                        <button class="btn btn-sm btn-primary" type="submit">Spara</button>
                    </form>
                </details>
                <?php if (!$isOwner && !$isMe): ?>
                    <form method="post" style="display:inline" onsubmit="return confirm('Ta bort användaren <?= e($u['username']) ?>?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= e($u['id']) ?>">
                        <button class="btn btn-sm btn-danger" type="submit">Ta bort</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php admin_footer();
