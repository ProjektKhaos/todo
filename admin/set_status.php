<?php
// admin/set_status.php - JSON-endpoint för att uppdatera status (Kanban drag/drop) Ⓐ Style
declare(strict_types=1);
require_once __DIR__ . '/../app/helpers.php';
require_admin();
require_once __DIR__ . '/../app/TodoRepository.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}
csrf_check();

$id     = (string)($_POST['id'] ?? '');
$status = (string)($_POST['status'] ?? '');

$repo = new TodoRepository();
$ok = $repo->setStatus($id, $status);
echo json_encode(['ok' => $ok]);
