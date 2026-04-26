<?php
// app/config.php - global konfiguration för To Do JSON App Ⓐ Style
// Uppdaterad: 2026-04-26 | av: KlⒶssⓔ & Ⓐberg

declare(strict_types=1);

const APP_NAME = 'To Do';
const APP_VERSION = '1.0.0';

// Sökvägar (absoluta)
define('BASE_PATH', dirname(__DIR__));
define('DATA_PATH', BASE_PATH . '/data');
define('DATA_FILE', DATA_PATH . '/todos.json');
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('UPLOAD_IMAGES', UPLOAD_PATH . '/images');
define('UPLOAD_AUDIO',  UPLOAD_PATH . '/audio');
define('UPLOAD_VIDEO',  UPLOAD_PATH . '/video');

// Webbsökväg (relativ rot för länkar i HTML).
// Räknas ut från app-katalogens läge i förhållande till DOCUMENT_ROOT.
(static function (): void {
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim(str_replace('\\', '/', (string)$_SERVER['DOCUMENT_ROOT']), '/') : '';
    $base    = str_replace('\\', '/', BASE_PATH);
    $url     = '';
    if ($docRoot !== '' && str_starts_with($base, $docRoot)) {
        $url = substr($base, strlen($docRoot));
    } else {
        // Fallback: dra ifrån filnamnet från SCRIPT_NAME för att hitta katalogen
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $scriptFile = isset($_SERVER['SCRIPT_FILENAME']) ? str_replace('\\', '/', (string)$_SERVER['SCRIPT_FILENAME']) : '';
        if ($scriptFile !== '' && str_starts_with($scriptFile, $base)) {
            // Den del av SCRIPT_NAME som motsvarar BASE_PATH
            $tailLen = strlen($scriptFile) - strlen($base);
            $url = substr($scriptName, 0, max(0, strlen($scriptName) - $tailLen));
        } else {
            $url = rtrim(dirname($scriptName), '/\\');
        }
    }
    define('BASE_URL', rtrim($url, '/'));
})();

// Maxstorlekar (bytes)
const MAX_IMAGE_SIZE = 8  * 1024 * 1024;   // 8 MB
const MAX_AUDIO_SIZE = 32 * 1024 * 1024;   // 32 MB
const MAX_VIDEO_SIZE = 128 * 1024 * 1024;  // 128 MB

// Tillåtna ändelser
const ALLOWED_IMAGE_EXT = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
const ALLOWED_AUDIO_EXT = ['mp3', 'wav', 'ogg', 'm4a'];
const ALLOWED_VIDEO_EXT = ['mp4', 'webm', 'mov'];

// Förbjudna ändelser oavsett kontext
const BLOCKED_EXT = ['php', 'phtml', 'phar', 'phps', 'htaccess', 'exe', 'sh', 'bat', 'cmd', 'js', 'html', 'htm'];

// Statusar
const STATUSAR = ['ny', 'pågår', 'väntar', 'klar', 'pausad'];

// Admin-lösenord. Standard: "admin" — byt direkt i produktion.
// Generera nytt med: php -r "echo password_hash('mittlösen', PASSWORD_DEFAULT), PHP_EOL;"
const ADMIN_PASSWORD_HASH = '$2y$12$kJqwecsJfRQuKrLnJECj9OUV4CjkYjr79yHLJNmURLThYbao5Hc3.';

// Session
const SESSION_NAME = 'todo_admin_sess';

// Tidszon
date_default_timezone_set('Europe/Stockholm');

// Felvisning – stäng av i produktion
ini_set('display_errors', '1');
error_reporting(E_ALL);
