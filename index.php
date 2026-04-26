<?php
// index.php - publik startsida med Kanban-tavla över aktiva uppgifter Ⓐ Style
// Uppdaterad: 2026-04-26 | av: KlⒶssⓔ & Ⓐberg
declare(strict_types=1);
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/TodoRepository.php';

$repo = new TodoRepository();

$view = ($_GET['view'] ?? 'aktiva');
$f_kategori = trim((string)($_GET['kategori'] ?? ''));
$f_status   = trim((string)($_GET['status'] ?? ''));
$f_overdue  = !empty($_GET['forsenade']);

$items = $view === 'arkiv' ? $repo->archived() : $repo->active();

// Filter
$items = array_values(array_filter($items, function ($t) use ($f_kategori, $f_status, $f_overdue) {
    if ($f_kategori !== '' && stripos((string)($t['kategori'] ?? ''), $f_kategori) === false) return false;
    if ($f_status !== ''   && ($t['status'] ?? '') !== $f_status) return false;
    if ($f_overdue) {
        if (empty($t['datum_slut'])) return false;
        $end = strtotime((string)$t['datum_slut']);
        if ($end === false || $end >= time()) return false;
    }
    return true;
}));

// Kategorier för filter-dropdown
$kategorier = array_values(array_unique(array_filter(array_map(fn($t) => (string)($t['kategori'] ?? ''), $repo->all()))));
sort($kategorier);

// Kanban-kolumner
$kolumner = [
    'ny'      => ['Nya',         'col-new'],
    'väntar'  => ['Schemalagda', 'col-scheduled'],
    'pågår'   => ['Pågår',       'col-progress'],
    'klar'    => ['Klart',       'col-done'],
];

$grupper = [];
foreach (array_keys($kolumner) as $s) $grupper[$s] = [];
foreach ($items as $t) {
    $st = (string)($t['status'] ?? 'ny');
    if (!isset($grupper[$st])) $st = 'ny';
    $grupper[$st][] = $t;
}

$is_admin = is_admin();
$csrf = $is_admin ? csrf_token() : '';
?>
<!DOCTYPE html>
<html lang="sv">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e(APP_NAME) ?> · Tavla</title>
<link rel="stylesheet" href="<?= e(url('assets/style.css')) ?>">
</head>
<body class="page-board">

<aside class="sidebar">
    <div class="brand">
        <span class="brand-mark">✓</span>
        <div>
            <div class="brand-name"><?= e(APP_NAME) ?></div>
            <div class="brand-sub">JSON App</div>
        </div>
    </div>
    <div class="search">
        <input type="search" placeholder="Sök i tavlan…" oninput="filterCards(this.value)">
    </div>
    <nav class="side-nav">
        <a class="<?= $view==='aktiva' ? 'active' : '' ?>" href="<?= e(url('index.php')) ?>">Min tavla</a>
        <a class="<?= $view==='arkiv'  ? 'active' : '' ?>" href="<?= e(url('index.php?view=arkiv')) ?>">Arkiv</a>
        <a href="<?= e(url('index.php?forsenade=1')) ?>">Försenade</a>
    </nav>
    <div class="side-section">
        <h3>Kategorier</h3>
        <ul class="side-list">
            <li><a href="<?= e(url('index.php')) ?>">Alla</a></li>
            <?php foreach ($kategorier as $k): ?>
                <li><a href="<?= e(url('index.php?kategori=' . urlencode($k))) ?>"><?= e($k) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="side-foot">
        <?php if ($is_admin): ?>
            <a class="btn btn-primary block" href="<?= e(url('admin/create.php')) ?>">+ Ny uppgift</a>
            <a class="btn block" href="<?= e(url('admin/index.php')) ?>">Admin</a>
            <a class="btn block" href="<?= e(url('admin/logout.php')) ?>">Logga ut</a>
        <?php else: ?>
            <a class="btn block" href="<?= e(url('admin/login.php')) ?>">Logga in</a>
        <?php endif; ?>
    </div>
</aside>

<main class="board">
    <header class="board-top">
        <div class="board-title">
            <h1><?= $view === 'arkiv' ? 'Arkiv' : 'Min tavla' ?></h1>
            <span class="muted"><?= count($items) ?> uppgifter</span>
        </div>
        <form method="get" class="board-filter">
            <?php if ($view==='arkiv'): ?><input type="hidden" name="view" value="arkiv"><?php endif; ?>
            <input type="text" name="kategori" placeholder="Kategori" value="<?= e($f_kategori) ?>">
            <select name="status">
                <option value="">Status (alla)</option>
                <?php foreach (STATUSAR as $s): ?>
                    <option value="<?= e($s) ?>" <?= $f_status === $s ? 'selected' : '' ?>><?= e(status_label($s)) ?></option>
                <?php endforeach; ?>
            </select>
            <label class="inline-check"><input type="checkbox" name="forsenade" value="1" <?= $f_overdue ? 'checked' : '' ?>> Försenade</label>
            <button class="btn btn-sm" type="submit">Filtrera</button>
        </form>
    </header>

    <section class="kanban" data-admin="<?= $is_admin ? '1' : '0' ?>" data-csrf="<?= e($csrf) ?>">
        <?php foreach ($kolumner as $statusKey => [$titel, $colClass]): ?>
            <div class="kanban-col <?= e($colClass) ?>" data-status="<?= e($statusKey) ?>">
                <div class="col-head">
                    <span class="dot"></span>
                    <span class="col-title"><?= e($titel) ?></span>
                    <span class="col-count"><?= count($grupper[$statusKey]) ?></span>
                </div>
                <div class="col-cards">
                    <?php foreach ($grupper[$statusKey] as $t): ?>
                        <article id="<?= e($t['id']) ?>"
                                 class="card <?= e(status_class($t['status'])) ?>"
                                 draggable="<?= $is_admin ? 'true' : 'false' ?>"
                                 data-id="<?= e($t['id']) ?>">
                            <?php if (!empty($t['bild'][0])): ?>
                                <div class="card-img"><img src="<?= e(url($t['bild'][0])) ?>" alt=""></div>
                            <?php endif; ?>

                            <div class="card-body">
                                <h3 class="card-title"><?= e($t['rubrik']) ?></h3>
                                <?php if (!empty($t['text'])): ?>
                                    <p class="card-text"><?= nl2br(e(mb_strimwidth($t['text'], 0, 220, '…'))) ?></p>
                                <?php endif; ?>

                                <?php if (!empty($t['kategori']) || !empty($t['underkategori'])): ?>
                                    <div class="chip chip-cat"><?= e(trim(($t['kategori'] ?? '') . ' / ' . ($t['underkategori'] ?? ''), ' /')) ?></div>
                                <?php endif; ?>

                                <?php if (!empty($t['plats'])): ?>
                                    <div class="chip chip-plats">📍 <?= e($t['plats']) ?></div>
                                <?php endif; ?>

                                <?php if (count($t['bild']) > 1): ?>
                                    <div class="thumbs-mini">
                                        <?php foreach (array_slice($t['bild'], 1, 4) as $img): ?>
                                            <img src="<?= e(url($img)) ?>" alt="">
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php foreach (($t['ljud'] ?? []) as $a): ?>
                                    <audio controls preload="none" src="<?= e(url($a)) ?>"></audio>
                                <?php endforeach; ?>
                                <?php foreach (($t['film'] ?? []) as $v): ?>
                                    <video controls preload="none" src="<?= e(url($v)) ?>"></video>
                                <?php endforeach; ?>
                            </div>

                            <footer class="card-foot">
                                <span class="time-left"><?= e(tid_kvar_text($t['datum_slut'] ?? null)) ?></span>
                                <?php if (!empty($t['datum_slut'])): ?>
                                    <span class="muted">slut: <?= e(format_dt($t['datum_slut'])) ?></span>
                                <?php endif; ?>
                            </footer>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
</main>

<script src="<?= e(url('assets/app.js')) ?>"></script>
</body>
</html>
