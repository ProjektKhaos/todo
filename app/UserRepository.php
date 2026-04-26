<?php
// app/UserRepository.php - användare lagrade i data/users.json Ⓐ Style
// Uppdaterad: 2026-04-26 | av: KlⒶssⓔ & Ⓐberg

declare(strict_types=1);

require_once __DIR__ . '/config.php';

class UserRepository
{
    private string $file;

    public function __construct(?string $file = null)
    {
        $this->file = $file ?? (DATA_PATH . '/users.json');
        $this->bootstrap();
    }

    /**
     * Skapa filen med ägaren Hans Åberg om den saknas.
     * Ägaren kan inte tas bort.
     */
    private function bootstrap(): void
    {
        if (!is_dir(dirname($this->file))) {
            @mkdir(dirname($this->file), 0755, true);
        }
        if (file_exists($this->file)) {
            return;
        }
        $owner = [
            'id'            => 'user_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)),
            'username'      => 'hanabe001',
            'name'          => 'Hans Åberg',
            'role'          => 'owner',
            'password_hash' => '$2y$12$PzLKcSMwdp3vaeGxl/TlGe6hYWNUQatBXv6fYqjMOMA4bkO9eU9cK',
            'created_at'    => date('Y-m-d H:i:s'),
        ];
        $this->writeAll([$owner]);
        @chmod($this->file, 0600);
    }

    private function readAll(): array
    {
        $raw = @file_get_contents($this->file);
        if ($raw === false || $raw === '') return [];
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            throw new RuntimeException('users.json är trasig: ' . json_last_error_msg());
        }
        return $data;
    }

    private function writeAll(array $data): void
    {
        $fp = fopen($this->file, 'c+');
        if (!$fp) throw new RuntimeException('Kan inte öppna users.json');
        try {
            if (!flock($fp, LOCK_EX)) throw new RuntimeException('Kan inte låsa users.json');
            ftruncate($fp, 0);
            rewind($fp);
            $json = json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            fwrite($fp, $json . "\n");
            fflush($fp);
            flock($fp, LOCK_UN);
        } finally {
            fclose($fp);
        }
        @chmod($this->file, 0600);
    }

    public function all(): array
    {
        $list = $this->readAll();
        usort($list, function ($a, $b) {
            // Ägare först, sen alfabetiskt
            $ar = ($a['role'] ?? '') === 'owner' ? 0 : 1;
            $br = ($b['role'] ?? '') === 'owner' ? 0 : 1;
            if ($ar !== $br) return $ar - $br;
            return strcasecmp($a['username'] ?? '', $b['username'] ?? '');
        });
        return $list;
    }

    public function find(string $id): ?array
    {
        foreach ($this->readAll() as $u) {
            if (($u['id'] ?? '') === $id) return $u;
        }
        return null;
    }

    public function findByUsername(string $username): ?array
    {
        $username = strtolower(trim($username));
        if ($username === '') return null;
        foreach ($this->readAll() as $u) {
            if (strtolower((string)($u['username'] ?? '')) === $username) return $u;
        }
        return null;
    }

    public function verify(string $username, string $password): ?array
    {
        $u = $this->findByUsername($username);
        if (!$u) {
            // Lika lång jämförelse oavsett — minska timing-läckage
            password_verify($password, '$2y$12$invalidinvalidinvalidinvalidinvalidinvalidinvalidinvalid');
            return null;
        }
        if (!password_verify($password, (string)($u['password_hash'] ?? ''))) return null;
        return $u;
    }

    public function create(string $username, string $name, string $password, string $role = 'admin'): array
    {
        $username = strtolower(trim($username));
        $name = trim($name);
        if (!preg_match('/^[a-z0-9._-]{3,32}$/', $username)) {
            throw new RuntimeException('Användarnamn måste vara 3–32 tecken (a–z, 0–9, . _ -).');
        }
        if ($name === '') {
            throw new RuntimeException('Namn krävs.');
        }
        if (strlen($password) < 8) {
            throw new RuntimeException('Lösenord måste vara minst 8 tecken.');
        }
        if ($this->findByUsername($username)) {
            throw new RuntimeException('Användarnamnet är upptaget.');
        }
        if ($role === 'owner') {
            throw new RuntimeException('Endast en ägare tillåts.');
        }
        $user = [
            'id'            => 'user_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)),
            'username'      => $username,
            'name'          => $name,
            'role'          => 'admin',
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'created_at'    => date('Y-m-d H:i:s'),
        ];
        $list = $this->readAll();
        $list[] = $user;
        $this->writeAll($list);
        return $user;
    }

    public function delete(string $id): bool
    {
        $list = $this->readAll();
        $new = [];
        $removed = false;
        foreach ($list as $u) {
            if (($u['id'] ?? '') === $id) {
                if (($u['role'] ?? '') === 'owner') {
                    throw new RuntimeException('Ägaren kan inte tas bort.');
                }
                $removed = true;
                continue;
            }
            $new[] = $u;
        }
        if ($removed) $this->writeAll($new);
        return $removed;
    }

    public function setPassword(string $id, string $password): bool
    {
        if (strlen($password) < 8) {
            throw new RuntimeException('Lösenord måste vara minst 8 tecken.');
        }
        $list = $this->readAll();
        $changed = false;
        foreach ($list as &$u) {
            if (($u['id'] ?? '') === $id) {
                $u['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
                $u['updated_at'] = date('Y-m-d H:i:s');
                $changed = true;
                break;
            }
        }
        unset($u);
        if ($changed) $this->writeAll($list);
        return $changed;
    }
}
