<?php
// admin/delete.php - ta bort post Ⓐ Style
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
$todo = $repo->find($id);
if ($todo) {
    // Radera kopplade filer
    foreach (['bild','ljud','film'] as $k) {
        foreach (($todo[$k] ?? []) as $rel) {
            $abs = BASE_PATH . '/' . ltrim((string)$rel, '/');
            if (is_file($abs) && str_starts_with(realpath($abs) ?: '', UPLOAD_PATH)) {
                @unlink($abs);
            }
        }
    }
    $repo->delete($id);
    flash_set('ok', 'Borttaget.');
} else {
    flash_set('error', 'Hittade inte posten.');
}
redirect('admin/index.php');
