<?php
// app/UploadHandler.php - säker filuppladdning för bild/ljud/film Ⓐ Style
// Uppdaterad: 2026-04-26 | av: KlⒶssⓔ & Ⓐberg

declare(strict_types=1);

require_once __DIR__ . '/config.php';

class UploadHandler
{
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
        [$dir, $allowedExt, $maxSize, $relDir] = match ($kind) {
            'image' => [UPLOAD_IMAGES, ALLOWED_IMAGE_EXT, MAX_IMAGE_SIZE, 'uploads/images'],
            'audio' => [UPLOAD_AUDIO,  ALLOWED_AUDIO_EXT, MAX_AUDIO_SIZE, 'uploads/audio'],
            'video' => [UPLOAD_VIDEO,  ALLOWED_VIDEO_EXT, MAX_VIDEO_SIZE, 'uploads/video'],
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
