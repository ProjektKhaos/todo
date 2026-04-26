<?php
// admin/edit.php - redigera post Ⓐ Style
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/../app/TodoRepository.php';
require_once __DIR__ . '/../app/UploadHandler.php';

$repo = new TodoRepository();
$id   = (string)($_GET['id'] ?? $_POST['id'] ?? '');
$todo = $repo->find($id);

if (!$todo) {
    http_response_code(404);
    admin_header('Hittas inte');
    echo '<p class="alert alert-error">Hittar ingen post med id ' . e($id) . '.</p>';
    admin_footer();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    try {
        $rubrik = trim((string)($_POST['rubrik'] ?? ''));
        if ($rubrik === '') {
            throw new RuntimeException('Rubrik krävs.');
        }

        // Hantera borttag av media (kryssrutor)
        $bild = $todo['bild'];
        $ljud = $todo['ljud'];
        $film = $todo['film'];
        foreach (($_POST['remove_bild'] ?? []) as $rm) {
            $bild = remove_and_unlink($bild, (string)$rm);
        }
        foreach (($_POST['remove_ljud'] ?? []) as $rm) {
            $ljud = remove_and_unlink($ljud, (string)$rm);
        }
        foreach (($_POST['remove_film'] ?? []) as $rm) {
            $film = remove_and_unlink($film, (string)$rm);
        }

        // Lägg till nya
        $up = new UploadHandler();
        $bild = array_merge($bild, $up->handleMany('bild', 'image'));
        $ljud = array_merge($ljud, $up->handleMany('ljud', 'audio'));
        $film = array_merge($film, $up->handleMany('film', 'video'));

        $payload = [
            'rubrik'        => $rubrik,
            'text'          => (string)($_POST['text'] ?? ''),
            'kategori'      => (string)($_POST['kategori'] ?? ''),
            'underkategori' => (string)($_POST['underkategori'] ?? ''),
            'status'        => (string)($_POST['status'] ?? 'ny'),
            'plats'         => (string)($_POST['plats'] ?? ''),
            'datum'         => (string)($_POST['datum'] ?? '') ?: null,
            'datum_start'   => (string)($_POST['datum_start'] ?? '') ?: null,
            'datum_slut'    => (string)($_POST['datum_slut'] ?? '') ?: null,
            'bild'          => $bild,
            'ljud'          => $ljud,
            'film'          => $film,
            'aktiv'         => !empty($_POST['aktiv']),
            'arkiv'         => !empty($_POST['arkiv']),
        ];
        $repo->update($id, $payload);
        flash_set('ok', 'Sparat.');
        redirect('admin/edit.php?id=' . urlencode($id));
    } catch (Throwable $ex) {
        flash_set('error', $ex->getMessage());
    }
    $todo = $repo->find($id) ?? $todo;
}

function remove_and_unlink(array $list, string $rel): array
{
    $out = [];
    foreach ($list as $p) {
        if ($p === $rel) {
            $abs = BASE_PATH . '/' . ltrim($rel, '/');
            if (is_file($abs) && str_starts_with(realpath($abs) ?: '', UPLOAD_PATH)) {
                @unlink($abs);
            }
            continue;
        }
        $out[] = $p;
    }
    return $out;
}

admin_header('Redigera: ' . $todo['rubrik']);
?>
<div class="page-header">
    <h1>Redigera</h1>
    <small class="muted">ID: <?= e($todo['id']) ?> · Skapad: <?= e($todo['created_at'] ?? '') ?> · Uppdaterad: <?= e($todo['updated_at'] ?? '') ?></small>
</div>
<?php
$action = url('admin/edit.php?id=' . urlencode($id));
$submit_label = 'Spara ändringar';
include __DIR__ . '/_form.php';
admin_footer();
