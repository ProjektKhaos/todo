# Missionpaket: To Do App i JSON-format

Syfte: Bygga en enkel men komplett To Do-app där data sparas i JSON-fil och där admin/backend kan skapa, redigera, ta bort och arkivera poster.

Projektet ska stödja:
- Datum
- Datum start
- Datum slut
- Datum/tid kvar
- Kategori
- Underkategori
- Rubrik
- Text
- Bild
- Ljud
- Status
- Plats
- Aktiv
- Arkiv
- Upload av bilder, filmer och ljud

Rekommenderad teknikstack:
- PHP 8+
- JSON-fil som datalager
- HTML/CSS/JavaScript
- Enkel filuppladdning till `/uploads/`

Föreslagen struktur:

```text
todo_json_app/
├── index.php
├── admin/
│   ├── index.php
│   ├── create.php
│   ├── edit.php
│   ├── delete.php
│   └── archive.php
├── app/
│   ├── config.php
│   ├── helpers.php
│   ├── TodoRepository.php
│   └── UploadHandler.php
├── data/
│   └── todos.json
├── uploads/
│   ├── images/
│   ├── audio/
│   └── video/
├── assets/
│   ├── style.css
│   └── app.js
└── docs/
```

Arbeta igenom missionsfilerna i nummerordning.
