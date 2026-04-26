<?php
// app/TodoRepository.php - JSON-baserat repository för To Do-poster Ⓐ Style
// Uppdaterad: 2026-04-26 | av: KlⒶssⓔ & Ⓐberg

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

class TodoRepository
{
    private string $file;

    public function __construct(?string $file = null)
    {
        $this->file = $file ?? DATA_FILE;
        $this->ensureFile();
    }

    private function ensureFile(): void
    {
        $dir = dirname($this->file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (!file_exists($this->file)) {
            file_put_contents($this->file, "[]\n");
        }
    }

    private function readAll(): array
    {
        $raw = @file_get_contents($this->file);
        if ($raw === false || $raw === '') {
            return [];
        }
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            throw new RuntimeException('Datafilen är trasig (ogiltig JSON): ' . json_last_error_msg());
        }
        return $data;
    }

    private function writeAll(array $data): void
    {
        $fp = fopen($this->file, 'c+');
        if (!$fp) {
            throw new RuntimeException('Kan inte öppna datafilen för skrivning.');
        }
        try {
            if (!flock($fp, LOCK_EX)) {
                throw new RuntimeException('Kan inte låsa datafilen.');
            }
            ftruncate($fp, 0);
            rewind($fp);
            $json = json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($json === false) {
                throw new RuntimeException('Kan inte koda JSON: ' . json_last_error_msg());
            }
            fwrite($fp, $json . "\n");
            fflush($fp);
            flock($fp, LOCK_UN);
        } finally {
            fclose($fp);
        }
    }

    public function all(): array
    {
        $list = $this->readAll();
        usort($list, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        return $list;
    }

    public function active(): array
    {
        return array_values(array_filter($this->all(), fn($t) => empty($t['arkiv']) && !empty($t['aktiv'])));
    }

    public function archived(): array
    {
        return array_values(array_filter($this->all(), fn($t) => !empty($t['arkiv'])));
    }

    public function find(string $id): ?array
    {
        foreach ($this->readAll() as $t) {
            if (($t['id'] ?? '') === $id) {
                return $t;
            }
        }
        return null;
    }

    public function create(array $data): array
    {
        $now = date('Y-m-d H:i:s');
        $todo = $this->normalize(array_merge($this->defaults(), $data));
        $todo['id']         = $todo['id'] ?: $this->newId();
        $todo['created_at'] = $now;
        $todo['updated_at'] = $now;
        $todo['tid_kvar_text'] = tid_kvar_text($todo['datum_slut'] ?? null);

        $list = $this->readAll();
        $list[] = $todo;
        $this->writeAll($list);
        return $todo;
    }

    public function update(string $id, array $data): bool
    {
        $list = $this->readAll();
        $changed = false;
        foreach ($list as &$t) {
            if (($t['id'] ?? '') === $id) {
                // Bara fält som faktiskt skickades in får skriva över
                $patch = array_intersect_key($data, $this->defaults());
                $merged = $this->normalize(array_merge($t, $patch));
                $merged['id']         = $t['id'];
                $merged['created_at'] = $t['created_at'] ?? date('Y-m-d H:i:s');
                $merged['updated_at'] = date('Y-m-d H:i:s');
                $merged['tid_kvar_text'] = tid_kvar_text($merged['datum_slut'] ?? null);
                $t = $merged;
                $changed = true;
                break;
            }
        }
        unset($t);
        if ($changed) $this->writeAll($list);
        return $changed;
    }

    public function delete(string $id): bool
    {
        $list = $this->readAll();
        $new  = array_values(array_filter($list, fn($t) => ($t['id'] ?? '') !== $id));
        if (count($new) === count($list)) return false;
        $this->writeAll($new);
        return true;
    }

    public function archive(string $id): bool
    {
        return $this->update($id, ['arkiv' => true, 'aktiv' => false]);
    }

    public function unarchive(string $id): bool
    {
        return $this->update($id, ['arkiv' => false, 'aktiv' => true]);
    }

    public function setStatus(string $id, string $status): bool
    {
        if (!in_array($status, STATUSAR, true)) return false;
        return $this->update($id, ['status' => $status]);
    }

    public function newId(): string
    {
        return 'todo_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3));
    }

    private function defaults(): array
    {
        return [
            'id'             => null,
            'datum'          => null,
            'datum_start'    => null,
            'datum_slut'     => null,
            'tid_kvar_text'  => null,
            'kategori'       => '',
            'underkategori'  => '',
            'rubrik'         => '',
            'text'           => '',
            'bild'           => [],
            'ljud'           => [],
            'film'           => [],
            'status'         => 'ny',
            'plats'          => '',
            'aktiv'          => true,
            'arkiv'          => false,
        ];
    }

    /**
     * Säkerställ att posten har alla fält och rätt typer.
     */
    private function normalize(array $data): array
    {
        $out = array_merge($this->defaults(), array_intersect_key($data, $this->defaults()));

        foreach (['bild', 'ljud', 'film'] as $k) {
            if (!is_array($out[$k])) $out[$k] = [];
            $out[$k] = array_values(array_filter(array_map('strval', $out[$k])));
        }
        $out['aktiv'] = (bool)$out['aktiv'];
        $out['arkiv'] = (bool)$out['arkiv'];
        if (!in_array($out['status'], STATUSAR, true)) {
            $out['status'] = 'ny';
        }
        // Tomma datum → null
        foreach (['datum', 'datum_start', 'datum_slut'] as $k) {
            if ($out[$k] === '' ) $out[$k] = null;
        }
        return $out;
    }
}
