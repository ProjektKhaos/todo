<?php
// admin/logout.php - logga ut admin Ⓐ Style
declare(strict_types=1);
require_once __DIR__ . '/../app/helpers.php';
start_admin_session();
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();
redirect('admin/login.php');
