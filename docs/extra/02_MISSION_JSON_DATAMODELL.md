# 02 Mission: JSON-datamodell

## Mål
Definiera en stabil JSON-modell för varje To Do-post.

## Föreslagen poststruktur

```json
{
  "id": "todo_20260426_001",
  "created_at": "2026-04-26 14:00:00",
  "updated_at": "2026-04-26 14:00:00",
  "datum": "2026-04-26",
  "datum_start": "2026-04-26 14:00",
  "datum_slut": "2026-04-27 18:00",
  "tid_kvar_text": "1 dag 4 timmar",
  "kategori": "Privat",
  "underkategori": "Hemmet",
  "rubrik": "Exempeluppgift",
  "text": "Beskrivning av uppgiften.",
  "bild": [],
  "ljud": [],
  "film": [],
  "status": "ny",
  "plats": "Trollhättan",
  "aktiv": true,
  "arkiv": false
}
```

## Statusvärden
Använd gärna dessa som standard:

- `ny`
- `pågår`
- `väntar`
- `klar`
- `pausad`

## Fältförklaring

| Fält | Beskrivning |
|---|---|
| id | Unikt ID för posten |
| created_at | När posten skapades |
| updated_at | När posten senast ändrades |
| datum | Grunddatum för uppgiften |
| datum_start | Startdatum och starttid |
| datum_slut | Slutdatum och sluttid |
| tid_kvar_text | Beräknad text, exempelvis "2 dagar kvar" |
| kategori | Huvudkategori |
| underkategori | Underkategori |
| rubrik | Kort titel |
| text | Längre beskrivning |
| bild | Array med uppladdade bildfiler |
| ljud | Array med uppladdade ljudfiler |
| film | Array med uppladdade filmfiler |
| status | Status för uppgiften |
| plats | Plats/koppling |
| aktiv | true/false om posten är aktiv |
| arkiv | true/false om posten är arkiverad |

## Viktigt
Spara media som filnamn/sökvägar i JSON, inte själva filinnehållet.
