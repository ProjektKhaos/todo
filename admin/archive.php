<?php
// admin/archive.php - arkivera/återställ post Ⓐ Style
declare(strict_types=1);
require_once __DIR__ . '/../app/helpers.php';
require_admin();
require_once __DIR__ . '/../app/TodoRepository.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}
csrf_check();

$id = (string)($_POST['id'] ?? '');
$repo = new TodoRepository();
$unarchive = isset($_GET['unarchive']);

if ($unarchive) {
    $ok = $repo->unarchive($id);
    flash_set($ok ? 'ok' : 'error', $ok ? 'Återställd.' : 'Kunde inte återställa.');
    redirect('admin/index.php?view=arkiv');
} else {
    $ok = $repo->archive($id);
    flash_set($ok ? 'ok' : 'error', $ok ? 'Arkiverad.' : 'Kunde inte arkivera.');
    redirect('admin/index.php');
}
