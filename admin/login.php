<?php
// admin/login.php - inloggning till admin Ⓐ Style
declare(strict_types=1);
require_once __DIR__ . '/../app/helpers.php';
start_admin_session();

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $pw = (string)($_POST['password'] ?? '');
    if (password_verify($pw, ADMIN_PASSWORD_HASH)) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        flash_set('ok', 'Inloggad.');
        redirect('admin/index.php');
    }
    $error = 'Fel lösenord.';
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
        <label>Lösenord
            <input type="password" name="password" required autofocus>
        </label>
        <button class="btn btn-primary" type="submit">Logga in</button>
    </form>
    <p class="muted"><a href="<?= e(url('index.php')) ?>">← Till tavlan</a></p>
</main>
</body>
</html>
