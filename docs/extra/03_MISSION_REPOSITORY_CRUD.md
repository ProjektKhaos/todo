# 03 Mission: TodoRepository med CRUD

## Mål
Skapa `app/TodoRepository.php` som hanterar all läsning och skrivning mot `data/todos.json`.

## Funktioner som ska finnas

```php
class TodoRepository
{
    public function all(): array;
    public function active(): array;
    public function archived(): array;
    public function find(string $id): ?array;
    public function create(array $data): array;
    public function update(string $id, array $data): bool;
    public function delete(string $id): bool;
    public function archive(string $id): bool;
    public function unarchive(string $id): bool;
}
```

## Krav
- Läs JSON från `data/todos.json`.
- Skriv JSON med `JSON_PRETTY_PRINT` och `JSON_UNESCAPED_UNICODE`.
- Använd fillåsning vid skrivning med `flock()`.
- Om JSON-filen saknas ska den skapas automatiskt.
- Om JSON är trasig ska appen inte krascha, utan visa begripligt fel.

## ID-generering
Exempel:

```php
$id = 'todo_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3));
```

## Tid kvar
Beräkna tid kvar från `datum_slut`.

Exempel:
- Om slutdatum är i framtiden: `2 dagar 3 timmar kvar`
- Om slutdatum passerat: `försenad med 1 dag 2 timmar`
- Om inget slutdatum finns: `ingen sluttid satt`

## Resultat
Efter missionen ska backend kunna skapa, läsa, uppdatera, arkivera och ta bort poster via PHP-kod.
