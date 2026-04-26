<?php
// admin/create.php - skapa ny post Ⓐ Style
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/../app/TodoRepository.php';
require_once __DIR__ . '/../app/UploadHandler.php';

$repo = new TodoRepository();
$todo = [
    'rubrik' => '', 'text' => '', 'kategori' => '', 'underkategori' => '',
    'status' => 'ny', 'plats' => '', 'datum' => null,
    'datum_start' => null, 'datum_slut' => null,
    'bild' => [], 'ljud' => [], 'film' => [],
    'aktiv' => true, 'arkiv' => false,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    try {
        $rubrik = trim((string)($_POST['rubrik'] ?? ''));
        if ($rubrik === '') {
            throw new RuntimeException('Rubrik krävs.');
        }
        $up = new UploadHandler();
        $bilder = $up->handleMany('bild', 'image');
        $ljud   = $up->handleMany('ljud', 'audio');
        $film   = $up->handleMany('film', 'video');

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
            'bild'          => $bilder,
            'ljud'          => $ljud,
            'film'          => $film,
            'aktiv'         => !empty($_POST['aktiv']),
            'arkiv'         => !empty($_POST['arkiv']),
        ];
        $created = $repo->create($payload);
        flash_set('ok', 'Skapade "' . $created['rubrik'] . '".');
        redirect('admin/index.php');
    } catch (Throwable $ex) {
        flash_set('error', $ex->getMessage());
        // Behåll inmatat
        $todo = array_merge($todo, $_POST);
    }
}

admin_header('Ny uppgift');
?>
<div class="page-header"><h1>Ny uppgift</h1></div>
<?php
$action = url('admin/create.php');
$submit_label = 'Skapa';
include __DIR__ . '/_form.php';
admin_footer();
