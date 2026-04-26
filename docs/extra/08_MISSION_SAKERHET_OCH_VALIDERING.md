# 08 Mission: Säkerhet och validering

## Mål
Göra appen tryggare och mer stabil.

## Krav
- Escapa all output med `htmlspecialchars()`.
- Validera alla formulärfält.
- Kräv rubrik vid skapande.
- Begränsa filstorlek på upload.
- Blockera farliga filtyper.
- Använd CSRF-token på adminformulär om möjligt.
- Skydda admin med enkel inloggning eller lösenord.

## Enkel admininloggning
Första versionen kan använda ett lösenord i config:

```php
const ADMIN_PASSWORD_HASH = '...';
```

Använd `password_hash()` och `password_verify()`.

## Rekommenderade rättigheter
På Linux-server:

```bash
find data uploads -type d -exec chmod 755 {} \;
find data uploads -type f -exec chmod 644 {} \;
```

Webbservern måste kunna skriva till:

```text
data/todos.json
uploads/
```

## Resultat
Admin ska inte vara helt öppen, och filuppladdning ska inte kunna användas för att ladda upp körbar kod.
