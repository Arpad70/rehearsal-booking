## Popis změn
Oprava chyby při odesílání e-mailu s potvrzením rezervace: Symfony vracel chybu, protože plain-text část byla interpretována jako název view namísto surového textu.

Co bylo změněno:
- `app/Mail/ReservationCreatedMail.php`: před-renderování HTML a plain-text šablon. `htmlString` je nyní před-renderovaný HTML string; `text` je předán jako Closure vracející HtmlString (zabraňuje chybě při hledání view).
- Přidány/unit a feature testy:
  - `tests/Unit/ReservationCreatedMailTest.php` – ověření content typů
  - `tests/Feature/ReservationMailIntegrationTest.php` – integrace: faking Mail, ověření odeslání a přílohy (qr.png), kontrola mime a velikosti přílohy
- `.github/workflows/phpunit.yml` – workflow pro spuštění PHPUnit na push/PR

Proč tato změna: Zabránit InvalidArgumentException: "View [..] not found" když Laravel/Symfony zpracovávají textovou část e-mailu.

Testy: Přidány a lokálně spuštěny; všechny aktuální testy procházejí.

Poznámky pro nasazení:
- Pokud budete posílat e-maily asynchronně, zvažte implementaci `ShouldQueue` pro mailable a spuštění queue workeru (`php artisan queue:work`).
- CI workflow spustí PHPUnit; pokud přidáte externí mailery nebo integrace, přidejte odpovídající secrets.


***