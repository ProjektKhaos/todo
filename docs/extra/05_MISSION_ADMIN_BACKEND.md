# 05 Mission: Admin/backend

## Mål
Bygga ett enkelt backend där admin kan lägga till, redigera, ta bort och arkivera To Do-poster.

## Sidor

```text
admin/index.php       - lista alla aktiva poster
admin/create.php      - skapa ny post
admin/edit.php?id=... - redigera post
admin/delete.php?id=... - ta bort post
admin/archive.php?id=... - arkivera post
```

## Formulärfält
Adminformuläret ska innehålla:

- Datum
- Datum start
- Datum slut
- Kategori
- Underkategori
- Rubrik
- Text
- Bild-upload, flera filer
- Ljud-upload, flera filer
- Film-upload, flera filer
- Status
- Plats
- Aktiv, checkbox
- Arkiv, checkbox

## Lista
Adminlistan ska visa:

- Rubrik
- Kategori / underkategori
- Status
- Datum start
- Datum slut
- Tid kvar
- Plats
- Aktiv
- Arkiv
- Knappar: Visa, Redigera, Arkivera, Ta bort

## Ta bort
Använd helst bekräftelse innan radering.

Exempel:

```html
onclick="return confirm('Vill du verkligen ta bort denna post?');"
```

## Resultat
Efter missionen ska hela CRUD-flödet fungera via webbläsaren.
