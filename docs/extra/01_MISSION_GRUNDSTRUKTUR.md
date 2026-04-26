# 01 Mission: Skapa grundstruktur

## Mål
Skapa fil- och mappstruktur för en portabel PHP-baserad To Do-app som använder JSON som datalager.

## Krav
Skapa följande mappar:

```text
app/
admin/
assets/
data/
uploads/images/
uploads/audio/
uploads/video/
docs/
```

Skapa följande startfiler:

```text
index.php
admin/index.php
app/config.php
app/helpers.php
app/TodoRepository.php
app/UploadHandler.php
assets/style.css
assets/app.js
data/todos.json
```

## Viktigt
Alla PHP-filer ska ha tydlig filkommentar i början enligt Åberg/Klasse-stil.

Exempel:

```php
<?php
// index.php - offentlig startsida för To Do-appen Ⓐ Style
```

Alla nya PHP-sidor ska ha full HTML-struktur där det är en visuell sida:

```html
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="utf-8">
    <title>...</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
...
</body>
</html>
```

## todos.json
Starta med tom array:

```json
[]
```

## Resultat
Efter missionen ska appen kunna öppnas utan fatal errors, även om den ännu inte gör något avancerat.
