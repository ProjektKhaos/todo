# 04 Mission: Upload av bilder, ljud och filmer

## Mål
Skapa `app/UploadHandler.php` som hanterar uppladdning av mediafiler.

## Mappar

```text
uploads/images/
uploads/audio/
uploads/video/
```

## Tillåtna filtyper

### Bilder
- jpg
- jpeg
- png
- webp
- gif

### Ljud
- mp3
- wav
- ogg
- m4a

### Film
- mp4
- webm
- mov

## Krav
- Validera filändelse.
- Validera MIME-typ där det går.
- Maxstorlek ska kunna ställas i `app/config.php`.
- Skapa säkra filnamn.
- Lägg gärna datum/tid i filnamnet.
- Returnera sparad relativ sökväg, till exempel:

```text
uploads/images/20260426_140000_min_bild.jpg
```

## Säkerhet
- Tillåt aldrig `.php`, `.phtml`, `.htaccess`, `.exe`, `.sh`.
- Spara inte originalnamn rakt av utan sanera.
- Kontrollera att upload-mappen finns och är skrivbar.

## Resultat
Admin ska kunna ladda upp flera bilder, ljud och filmer kopplade till en To Do-post.
