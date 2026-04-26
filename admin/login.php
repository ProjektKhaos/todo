<?php
// admin/login.php - inloggning till admin Ⓐ Style
declare(strict_types=1);
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/UserRepository.php';
start_admin_session();

$error = null;
$username_in = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username_in = trim((string)($_POST['username'] ?? ''));
    $pw  = (string)($_POST['password'] ?? '');
    $users = new UserRepository();
    $u = $users->verify($username_in, $pw);
    if ($u) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['user'] = [
            'id'       => $u['id'],
            'username' => $u['username'],
            'name'     => $u['name'] ?? $u['username'],
            'role'     => $u['role'] ?? 'admin',
        ];
        flash_set('ok', 'Välkommen, ' . ($u['name'] ?? $u['username']) . '.');
        redirect('admin/index.php');
    }
    $error = 'Fel användarnamn eller lösenord.';
    usleep(500_000);
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Logga in · <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= e(url('assets/style.css')) ?>">
</head>
<body class="page-login">
<main class="login-card">
    <h1><?= e(APP_NAME) ?> · Admin</h1>
    <?php if ($error): ?><p class="alert alert-error"><?= e($error) ?></p><?php endif; ?>
    <form method="post" autocomplete="off">
        <?= csrf_field() ?>
        <label>Användarnamn
            <input type="text" name="username" required autofocus value="<?= e($username_in) ?>">
        </label>
        <label>Lösenord
            <input type="password" name="password" required>
        </label>
        <button class="btn btn-primary" type="submit">Logga in</button>
    </form>
    <p class="muted"><a href="<?= e(url('index.php')) ?>">← Till tavlan</a></p>
</main>
</body>
</html>
