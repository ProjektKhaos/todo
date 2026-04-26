<?php
// admin/_layout.php - delad layout för admin Ⓐ Style
declare(strict_types=1);
require_once __DIR__ . '/../app/helpers.php';
require_admin();

if (!function_exists('admin_header')) {
function admin_header(string $title): void {
    $ok  = flash_get('ok');
    $err = flash_get('error');
    ?>
<!DOCTYPE html>
<html lang="sv">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?> · Admin · <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= e(url('assets/style.css')) ?>">
</head>
<body class="page-admin">
<header class="topbar">
    <div class="brand">
        <span class="brand-mark">✓</span>
        <span class="brand-name"><?= e(APP_NAME) ?></span>
        <span class="brand-sub">Admin</span>
    </div>
    <nav class="topnav">
        <a href="<?= e(url('admin/index.php')) ?>">Aktiva</a>
        <a href="<?= e(url('admin/index.php?view=arkiv')) ?>">Arkiv</a>
        <a class="btn btn-primary" href="<?= e(url('admin/create.php')) ?>">+ Ny uppgift</a>
        <a href="<?= e(url('index.php')) ?>">Tavla</a>
        <a href="<?= e(url('admin/logout.php')) ?>">Logga ut</a>
    </nav>
</header>
<main class="container">
    <?php if ($ok):  ?><div class="alert alert-ok"><?= e($ok) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endif; ?>
    <?php
}}

if (!function_exists('admin_footer')) {
function admin_footer(): void { ?>
</main>
<footer class="footer">
    <small>Ⓐ <?= e(APP_NAME) ?> v<?= e(APP_VERSION) ?> · <?= date('Y-m-d') ?></small>
</footer>
</body>
</html>
<?php }}
