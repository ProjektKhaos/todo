<?php
// app/UploadHandler.php - säker filuppladdning för bild/ljud/film Ⓐ Style
// Uppdaterad: 2026-04-26 | av: KlⒶssⓔ & Ⓐberg

declare(strict_types=1);

require_once __DIR__ . '/config.php';

class UploadHandler
{
    private const ALLOWED_IMAGE_MIME = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    private const ALLOWED_AUDIO_MIME = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/x-wav', 'audio/wave', 'audio/ogg', 'application/ogg', 'audio/mp4', 'audio/x-m4a'];
    private const ALLOWED_VIDEO_MIME = ['video/mp4', 'video/webm', 'video/quicktime'];

    /**
     * Hantera ett <input type="file" name="X[]" multiple> fält.
     * Returnerar lista med relativa sökvägar (uploads/...).
     *
     * @param string $field Namn på POST-fältet
     * @param string $kind  'image' | 'audio' | 'video'
     * @return string[] sparade relativa sökvägar
     */
    public function handleMany(string $field, string $kind): array
    {
        if (empty($_FILES[$field]) || !is_array($_FILES[$field]['name'])) {
            return [];
        }
        $saved = [];
        $files = $this->reorganize($_FILES[$field]);
        foreach ($files as $f) {
            if (($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) continue;
            if (($f['error'] ?? 0) !== UPLOAD_ERR_OK) {
                throw new RuntimeException('Uppladdning misslyckades (kod ' . $f['error'] . ').');
            }
            $rel = $this->saveOne($f, $kind);
            if ($rel) $saved[] = $rel;
        }
        return $saved;
    }

    private function reorganize(array $arr): array
    {
        $out = [];
        $count = is_array($arr['name']) ? count($arr['name']) : 0;
        for ($i = 0; $i < $count; $i++) {
            $out[] = [
                'name'     => $arr['name'][$i],
                'type'     => $arr['type'][$i] ?? '',
                'tmp_name' => $arr['tmp_name'][$i] ?? '',
                'error'    => $arr['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size'     => $arr['size'][$i] ?? 0,
            ];
        }
        return $out;
    }

    private function saveOne(array $file, string $kind): ?string
    {
        [$dir, $allowedExt, $maxSize, $relDir, $allowedMime] = match ($kind) {
            'image' => [UPLOAD_IMAGES, ALLOWED_IMAGE_EXT, MAX_IMAGE_SIZE, 'uploads/images', self::ALLOWED_IMAGE_MIME],
            'audio' => [UPLOAD_AUDIO,  ALLOWED_AUDIO_EXT, MAX_AUDIO_SIZE, 'uploads/audio',  self::ALLOWED_AUDIO_MIME],
            'video' => [UPLOAD_VIDEO,  ALLOWED_VIDEO_EXT, MAX_VIDEO_SIZE, 'uploads/video',  self::ALLOWED_VIDEO_MIME],
            default => throw new InvalidArgumentException('Okänd filtyp: ' . $kind),
        };

        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (!is_writable($dir)) {
            throw new RuntimeException('Uppladdningsmappen är inte skrivbar: ' . $dir);
        }

        $size = (int)$file['size'];
        if ($size <= 0) return null;
        if ($size > $maxSize) {
            throw new RuntimeException(sprintf('Filen är för stor (%d bytes, max %d).', $size, $maxSize));
        }

        $origName = (string)$file['name'];
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        if ($ext === '' || in_array($ext, BLOCKED_EXT, true)) {
            throw new RuntimeException('Filtypen är inte tillåten: .' . $ext);
        }
        if (!in_array($ext, $allowedExt, true)) {
            throw new RuntimeException('Filtypen är inte tillåten för ' . $kind . ': .' . $ext);
        }

        $mime = $this->detectMime((string)$file['tmp_name']);
        if ($mime !== null && !$this->mimeMatches($mime, $allowedMime)) {
            throw new RuntimeException('Filens innehåll matchar inte filtypen (' . $mime . ').');
        }

        $base = $this->safeBase($origName);
        $stamp = date('Ymd_His');
        $rand  = bin2hex(random_bytes(2));
        $name  = $stamp . '_' . $rand . '_' . $base . '.' . $ext;
        $dest  = $dir . '/' . $name;

        if (!is_uploaded_file($file['tmp_name'])) {
            // Tillåt även icke-uploaded i CLI/test, annars hård
            if (PHP_SAPI !== 'cli') {
                throw new RuntimeException('Misstänkt fil – inte uppladdad via HTTP.');
            }
        }
        if (!@move_uploaded_file($file['tmp_name'], $dest)) {
            // Sista utväg vid CLI-test
            if (PHP_SAPI === 'cli' && @rename($file['tmp_name'], $dest)) {
                // ok
            } else {
                throw new RuntimeException('Kunde inte spara filen.');
            }
        }
        @chmod($dest, 0644);

        return $relDir . '/' . $name;
    }

    private function detectMime(string $tmp): ?string
    {
        if ($tmp === '' || !is_readable($tmp)) return null;
        if (function_exists('finfo_open')) {
            $fi = @finfo_open(FILEINFO_MIME_TYPE);
            if ($fi) {
                $mime = @finfo_file($fi, $tmp) ?: null;
                @finfo_close($fi);
                return $mime ? strtolower($mime) : null;
            }
        }
        if (function_exists('mime_content_type')) {
            $mime = @mime_content_type($tmp);
            return $mime ? strtolower($mime) : null;
        }
        return null;
    }

    private function mimeMatches(string $mime, array $allowed): bool
    {
        $mime = strtolower($mime);
        foreach ($allowed as $a) {
            if ($mime === strtolower($a)) return true;
        }
        return false;
    }

    private function safeBase(string $name): string
    {
        $base = pathinfo($name, PATHINFO_FILENAME);
        $base = strtolower($base);
        $base = strtr($base, ['å'=>'a','ä'=>'a','ö'=>'o','é'=>'e','è'=>'e','ü'=>'u']);
        $base = preg_replace('/[^a-z0-9_-]+/', '_', $base) ?? '';
        $base = trim($base, '_-');
        if ($base === '') $base = 'fil';
        return substr($base, 0, 50);
    }
}
