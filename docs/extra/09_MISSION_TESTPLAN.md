# 09 Mission: Testplan

## Mål
Verifiera att appen fungerar innan den används på riktigt.

## Tester

### Skapa post
- Skapa post med endast rubrik.
- Skapa post med alla fält.
- Skapa post med bild, ljud och film.

### Redigera post
- Ändra rubrik.
- Ändra status.
- Ändra datum slut och kontrollera tid kvar.
- Lägg till mer media.

### Arkiv
- Arkivera post.
- Kontrollera att den försvinner från aktiv lista.
- Kontrollera att den finns i arkivlista.
- Återaktivera post om funktionen finns.

### Ta bort
- Ta bort post.
- Kontrollera att JSON-filen uppdateras.

### Upload
- Testa tillåten bild.
- Testa tillåtet ljud.
- Testa tillåten film.
- Testa blockerad `.php`-fil.
- Testa för stor fil.

### JSON
- Kontrollera att `data/todos.json` fortfarande är giltig JSON.

Kommando:

```bash
php -r "json_decode(file_get_contents('data/todos.json')); echo json_last_error_msg();"
```

## Resultat
När alla tester är gröna kan appen betraktas som första fungerande version.
