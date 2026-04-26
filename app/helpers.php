<?php
// app/helpers.php - generella hjälpfunktioner Ⓐ Style
// Uppdaterad: 2026-04-26 | av: KlⒶssⓔ & Ⓐberg

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function e(?string $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    $base = BASE_URL === '' ? '' : BASE_URL;
    return $base . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function start_admin_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_start([
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
            'use_strict_mode' => true,
        ]);
    }
}

function is_admin(): bool
{
    start_admin_session();
    return !empty($_SESSION['admin_logged_in']) && !empty($_SESSION['user']);
}

function require_admin(): void
{
    if (!is_admin()) {
        redirect('admin/login.php');
    }
}

function current_user(): ?array
{
    start_admin_session();
    return $_SESSION['user'] ?? null;
}

function is_owner(): bool
{
    $u = current_user();
    return $u !== null && ($u['role'] ?? '') === 'owner';
}

function require_owner(): void
{
    require_admin();
    if (!is_owner()) {
        http_response_code(403);
        exit('Endast ägaren har tillgång till denna sida.');
    }
}

function csrf_token(): string
{
    start_admin_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    start_admin_session();
    $sent = $_POST['_csrf'] ?? '';
    if (!is_string($sent) || !hash_equals($_SESSION['csrf'] ?? '', $sent)) {
        http_response_code(419);
        exit('CSRF-token saknas eller är ogiltig.');
    }
}

function flash_set(string $key, string $msg): void
{
    start_admin_session();
    $_SESSION['_flash'][$key] = $msg;
}

function flash_get(string $key): ?string
{
    start_admin_session();
    if (isset($_SESSION['_flash'][$key])) {
        $msg = $_SESSION['_flash'][$key];
        unset($_SESSION['_flash'][$key]);
        return $msg;
    }
    return null;
}

/**
 * Räkna ut tid kvar utifrån slutdatum.
 * Returnerar text som "2 dagar 3 timmar kvar", "försenad med ..." eller "ingen sluttid satt".
 */
function tid_kvar_text(?string $datum_slut): string
{
    if (!$datum_slut) {
        return 'ingen sluttid satt';
    }
    $end = strtotime($datum_slut);
    if ($end === false) {
        return 'okänt slutdatum';
    }
    $now = time();
    $diff = $end - $now;
    $abs = abs($diff);
    $days  = (int) floor($abs / 86400);
    $hours = (int) floor(($abs % 86400) / 3600);
    $mins  = (int) floor(($abs % 3600) / 60);

    $parts = [];
    if ($days > 0)  $parts[] = $days . ' ' . ($days === 1 ? 'dag' : 'dagar');
    if ($hours > 0) $parts[] = $hours . ' ' . ($hours === 1 ? 'timme' : 'timmar');
    if (!$parts && $mins > 0) $parts[] = $mins . ' ' . ($mins === 1 ? 'minut' : 'minuter');
    if (!$parts) $parts[] = 'mindre än en minut';

    $text = implode(' ', $parts);
    return $diff >= 0 ? ($text . ' kvar') : ('försenad med ' . $text);
}

function status_label(string $status): string
{
    return match ($status) {
        'ny'      => 'Ny',
        'pågår'   => 'Pågår',
        'väntar'  => 'Väntar',
        'klar'    => 'Klar',
        'pausad'  => 'Pausad',
        default   => ucfirst($status),
    };
}

function status_class(string $status): string
{
    return 'status-' . preg_replace('/[^a-z0-9]/', '', strtolower(
        strtr($status, ['å' => 'a', 'ä' => 'a', 'ö' => 'o'])
    ));
}

function format_dt(?string $dt): string
{
    if (!$dt) return '';
    $t = strtotime($dt);
    if ($t === false) return $dt;
    return date('Y-m-d H:i', $t);
}

function brand_mark(int $size = 32): string
{
    $s = (int)$size;
    return <<<SVG
<svg class="brand-mark" width="$s" height="$s" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
    <defs>
        <linearGradient id="brandBg" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="#5a8bff"/>
            <stop offset="100%" stop-color="#1a4ed1"/>
        </linearGradient>
    </defs>
    <rect width="32" height="32" rx="9" fill="url(#brandBg)"/>
    <path d="M8.5 12 L11 14.5 L14.5 10.5" fill="none" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
    <rect x="16.5" y="11.2" width="8.5" height="1.8" rx="0.9" fill="#ffffff" opacity="0.9"/>
    <path d="M8.5 21 L11 23.5 L14.5 19.5" fill="none" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" opacity="0.55"/>
    <rect x="16.5" y="20.2" width="8.5" height="1.8" rx="0.9" fill="#ffffff" opacity="0.55"/>
</svg>
SVG;
}

function format_local(?string $dt): string
{
    if (!$dt) return '';
    $t = strtotime($dt);
    if ($t === false) return '';
    return date('Y-m-d\TH:i', $t);
}
