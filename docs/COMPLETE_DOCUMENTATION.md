# QR Reader System - Kompletní dokumentace

## Obsah
1. [Přehled systému](#přehled-systému)
2. [Fáze implementace](#fáze-implementace)
3. [Instalace a setup](#instalace-a-setup)
4. [API dokumentace](#api-dokumentace)
5. [Admin rozhraní](#admin-rozhraní)
6. [Databáze](#databáze)
7. [Konfiguraci](#konfigurace)
8. [Troubleshooting](#troubleshooting)

---

## Přehled systému

### Co je QR Reader System?

Úplně funkční systém pro správu přístupu do místností pomocí QR kódů. Podporuje:
- **Rezervace**: Hosté dostanou QR kód ke své rezervaci emailem
- **Servisní kódy**: Personál (čistění, údržba, admin) má permanentní přístupové kódy
- **Dvě úrovně čteček**: Lokální (místnost) a globální (hlavní vchod, servis)
- **Tři protokoly zámků**: Relay (GPIO), API (Smart lock), Webhook (Home Assistant)
- **Monitoring & Alerting**: Real-time sledování stavu zařízení
- **Reporting**: Detailní statistiky a analýza přístupů

---

## Fáze implementace

### Phase 1: Data Models ✅
- RoomReader model a tabulka (místnost-specifické čtečky)
- GlobalReader model a tabulka (globální vchody)
- ServiceAccess model a tabulka (servisní přístupy)
- Rozšíření Reservation (QR fields)
- Rozšíření AccessLog (QR logging)
- **5 migrací**: room_readers, global_readers, service_access, QR support, access_logs enhancements

### Phase 2: QR & Door Services ✅
- QRCodeService (generování, validace, cleanup)
- DoorLockService (3 protokoly: relay, API, webhook)
- QRAccessController API (4 endpointy)
- Rate limiting (100 req/min)
- Kompletní chybování a fallback strategie

### Phase 3: Admin Interface ✅
- RoomReaderResource (CRUD + test connection)
- GlobalReaderResource (CRUD + access windows)
- ServiceAccessResource (CRUD + generate QR + revoke)
- 9 Filament Pages pro kompletní správu

### Phase 4: Email Integration ✅
- ReservationQRCodeMail (odeslání QR emailem)
- ServiceAccessCodeMail (přístupový kód emailem)
- SendReservationQRCodeEmail job (asynchronní)
- SendServiceAccessCodeEmail job (asynchronní)
- ReservationObserver (automatický email na vytvoření)
- ServiceAccessObserver (automatický email na aktivaci)

### Phase 5: Statistics & Reports ✅
- AccessStatsOverview widget (hlavní metriky)
- AccessTrendChart widget (graf trendů)
- RoomUsageChart widget (využití místností)
- AccessReportResource (detailní report s filtrováním)
- ReaderStatsResource (statistiky čteček)
- 3 ovládací panely pro analýzu

### Phase 6: Advanced Features ✅
- BackupQRCode model (backup QR kódy pro redundanci)
- ReaderAlert model (monitoring a alerts)
- ReaderMonitoringService (health checks)
- MonitorReadersCommand (scheduling)
- ExportAccessLogsAction (export do CSV)
- ReaderAlertsWidget (zobrazení aktivních upozornění)

---

## Instalace a setup

### Předpoklady
- PHP 8.1+
- Laravel 10+
- MySQL 8.0+
- Composer
- Node.js (pro Frontend)

### 1. Klonovat a nainstalovat
```bash
git clone https://github.com/Arpad70/rehearsal-booking.git
cd rehearsal-booking
composer install
npm install
```

### 2. Konfigurace .env
```env
# Databáze
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rehearsal_booking
DB_USERNAME=root
DB_PASSWORD=

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_ADDRESS=noreply@rehearsal.local
MAIL_FROM_NAME="Rehearsal Booking"

# Queue (pro emails)
QUEUE_CONNECTION=database
```

### 3. Migrace databáze
```bash
php artisan migrate
```

Tímto se vytvoří všechny tabulky:
- users, rooms, reservations, access_logs
- room_readers, global_readers, service_access
- backup_qr_codes, reader_alerts
- jobs, cache, sessions

### 4. Seed databáze (optional)
```bash
php artisan db:seed
```

### 5. Vygenerovat app key
```bash
php artisan key:generate
```

### 6. Vytvořit storage symlink
```bash
php artisan storage:link
```

### 7. Spustit queue worker (pro emaily)
```bash
php artisan queue:work --queue=emails --sleep=1
```

### 8. Spustit dev server
```bash
php artisan serve
npm run dev
```

Aplikace bude dostupná na: `http://localhost:8000`

Filament Admin: `http://localhost:8000/admin`

---

## API dokumentace

### Base URL
```
http://localhost:8000/api/v1
```

### Authentication
Všechny endpointy (kromě `/qr/validate`) vyžadují Sanctum token:
```bash
Authorization: Bearer {sanctum_token}
```

Čtečky QR používají svůj vlastní token v URL nebo headeru:
```bash
Authorization: Bearer {reader_token}
```

### 1. Validace QR kódu

**Endpoint:** `POST /qr/validate`

**Popis:** Ověří QR kód a odemkne dveře

**Rate limit:** 100 pokusů/minutu

**Request:**
```json
{
  "qr_data": "{\"rid\":1,\"token\":\"abc...\",\"room\":1,\"start\":1705680000,\"end\":1705687200,\"type\":\"reservation\"}",
  "room_id": 1,
  "reader_token": "reader_secret_token",
  "reader_name": "MainDoor-01"
}
```

**Response (Success):**
```json
{
  "access": true,
  "message": "Access granted",
  "reservation_id": 1,
  "user_name": "John Doe",
  "door_unlocked": true,
  "unlock_duration": 5
}
```

**Response (Error):**
```json
{
  "access": false,
  "message": "Access denied",
  "reason": "TOO_EARLY",
  "next_access": "2025-01-18 10:00:00"
}
```

**Možné důvody zamítnutí:**
- `INVALID_QR` - Neplatný format QR
- `EXPIRED` - Rezervace skončila
- `TOO_EARLY` - Přístup není ještě dostupný (< 15 min před)
- `WRONG_ROOM` - QR je pro jinou místnost
- `INVALID_TOKEN` - Špatný access token
- `READER_UNAUTHORIZED` - Čtečka nemá přístup

---

### 2. Health Check

**Endpoint:** `GET /qr/status`

**Popis:** Ověří dostupnost API

**Response:**
```json
{
  "status": "online",
  "timestamp": "2025-01-18T10:30:00Z"
}
```

---

### 3. Heartbeat

**Endpoint:** `GET /qr/heartbeat`

**Popis:** Monitoring endpoint (používá se pro ping čteček)

**Response:**
```json
{
  "alive": true,
  "timestamp": "2025-01-18T10:30:05Z"
}
```

---

### 4. Test připojení čtečky

**Endpoint:** `POST /rooms/{roomId}/readers/{readerId}/test`

**Authentication:** Sanctum token

**Popis:** Testuje, zda je čtečka dostupná

**Response:**
```json
{
  "success": true,
  "message": "Reader online (245ms)",
  "response": "OK"
}
```

---

## Admin rozhraní

Všechno je dostupné v Filament admin panelu na `/admin`

### 1. QR Reader Management
**Navigation:** QR Reader > Room Readers

- Seznam všech místností a jejich čteček
- Přidání nové čtečky (IP, port, token, typ zámku)
- Konfigurace zámku (relay, API, webhook)
- Test připojení (tlačítko)
- Zapnutí/vypnutí

### 2. Global Readers
**Navigation:** QR Reader > Global Readers

- Správa hlavního vchodu, servisu, administrace
- Nastavení access windows (před/po rezervaci)
- Povolené typy servisních přístupů
- Test připojení

### 3. Service Access (Servisní přístupy)
**Navigation:** Access Control > Service Access

- Vytváření přístupů pro personál
- Výběr typu (čistění, údržba, admin)
- Omezení místností (nebo unlimited)
- Časové omezení (datum od/do)
- Generování QR kódu
- Zrušení přístupu s důvodem

### 4. Access Logs Report
**Navigation:** Reports > Access Reports

- Zobrazení všech přístupů (poslední)
- Filtrování: vysledek, typ, datum
- Export do CSV
- Detailní view jednotlivých pokusů

### 5. Reader Statistics
**Navigation:** Reports > Reader Statistics

- Statistiky za 30 dní
- Úspěšnost čtečky
- Počet pokusů
- Poslední aktivita

### 6. Backup QR kódy
**Navigation:** QR Reader > Backup QR Codes

- Zobrazení backup QR kódů pro rezervace
- Status (aktivní, použitý, zrušený, vypršelý)
- Možnost zrušit
- Zrušení původního QR

### 7. Dashboard

**Home page** zobrazuje:
- Dnešní přístupy (včetně neúspěšných)
- Rezervace tento týden
- Přístupy tento měsíc
- Úspěšnost procent (30 dní)
- Graf trendů (7 dní)
- Využití místností (30 dní)
- Aktivní upozornění

---

## Databáze

### Tabulky

#### room_readers
```sql
id, room_id (FK), reader_name, reader_ip, reader_port, 
reader_token, door_lock_type (relay|api|webhook),
door_lock_config (JSON), enabled, timestamps
```

#### global_readers
```sql
id, reader_name (UNIQUE), access_type (entrance|service|admin),
reader_ip, reader_port, reader_token, door_lock_type,
door_lock_config (JSON), access_minutes_before, access_minutes_after,
allowed_service_types (JSON), enabled, timestamps
```

#### service_access
```sql
id, user_id (FK), access_type (cleaning|maintenance|admin),
access_code (UNIQUE), description, allowed_rooms (JSON),
unlimited_access (bool), valid_from, valid_until,
usage_count, last_used_at, enabled, revoked, revoke_reason, timestamps
```

#### backup_qr_codes
```sql
id, reservation_id (FK), qr_code (path), qr_data (JSON),
sequence_number (1=primary), status (active|used|expired|revoked),
used_at, used_by_reader, timestamps
```

#### reader_alerts
```sql
id, alertable_type (polymorphic), alertable_id,
alert_type (offline|high_failure_rate|no_activity|suspicious_access|configuration_error),
message, metadata (JSON), severity (info|warning|critical),
acknowledged, acknowledged_at, acknowledged_by (FK), resolved_at, timestamps
```

#### access_logs (rozšířeno)
```sql
...existing columns...
access_code, access_type (reservation|service),
reader_type (room|global), global_reader_id (FK),
ip_address, user_agent, validated_at, timestamps
```

---

## Konfigurace

### config/reservations.php

```php
return [
    // QR Reader nastavení
    'qr_reader_rate_limit' => 100,           // req/min
    'qr_rate_window' => 1,                   // minuty
    'qr_access_minutes_before' => 15,        // min. před rezervací
    'qr_cleanup_days' => 30,                 // smazat staré QR
    
    // Relay (GPIO/Arduino)
    'default_relay_pin' => 1,                // GPIO pin
    'default_unlock_duration' => 5,          // sec
    
    // Global readers
    'default_global_unlock_duration' => 10,  // sec
    'global_reader_minutes_before' => 30,    // min. před
    'global_reader_minutes_after' => 30,     // min. po
    
    // QR generování
    'auto_generate_qr' => true,              // na vytvoření
    'backup_qr_count' => 2,                  // počet backupů
];
```

### config/database.php

Ujistěte se, že máte správný MySQL driver:
```php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST'),
    'port' => env('DB_PORT'),
    'database' => env('DB_DATABASE'),
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
],
```

---

## Monitoring a údržba

### Spustit Reader Monitoring

```bash
# Jednorazově
php artisan readers:monitor

# Vytvořit scheduling v Kernel.php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Každých 5 minut
    $schedule->command('readers:monitor')->everyFiveMinutes();
    
    // Čištění starých QR kódů (denně)
    $schedule->call(function() {
        app(\App\Services\QRCodeService::class)->cleanupOldQRCodes(30);
    })->daily();
    
    // Cleanup starých access logů (měsíčně)
    $schedule->call(function() {
        \App\Models\AccessLog::where('created_at', '<', now()->subMonths(6))->delete();
    })->monthly();
}
```

### Sledování alertů

```bash
# Zobrazit unresolved alerts
php artisan tinker
>>> App\Models\ReaderAlert::unresolved()->get();

# Zobrazit unacknowledged alerts
>>> App\Models\ReaderAlert::unacknowledged()->get();
```

---

## Troubleshooting

### Problem: "Reader unreachable"

**Příčiny:**
1. Čtečka je vypnutá
2. IP adresa je špatná
3. Port je špatný
4. Firewall blokuje komunikaci

**Řešení:**
1. Ověřte IP v admin panelu
2. Pingněte čtečku: `ping 192.168.1.100`
3. Vyzkoušejte: `curl http://192.168.1.100:8080/status`
4. Zkontrolujte firewall na čtečce

### Problem: "QR code generation failed"

**Příčiny:**
1. Žádné připojení k Internetu
2. API service je vypnutá
3. Chybí storage prostor

**Řešení:**
```bash
# Zkontrolujte storage prostor
df -h /storage

# Vytvořit QR ručně
php artisan tinker
>>> $res = App\Models\Reservation::find(1);
>>> app(App\Services\QRCodeService::class)->generateForReservation($res);
```

### Problem: "Email není odeslán"

**Příčiny:**
1. Queue worker není spuštěn
2. Email konfigurace je špatná
3. Job selhalo

**Řešení:**
```bash
# Spustit queue worker
php artisan queue:work

# Ověřit config
php artisan config:show mail

# Vyzkoušet email
php artisan tinker
>>> Mail::raw('Test', function($message) {
    $message->to('test@example.com')->subject('Test');
});
```

### Problem: "Access denied - WRONG_ROOM"

**Příčina:** QR je pro jinou místnost nebo reader

**Řešení:**
1. Ověřit room_id v Reservation
2. Ověřit room_id v RoomReader
3. Regenerovat QR kód

```bash
php artisan tinker
>>> $res = App\Models\Reservation::find(1);
>>> $res->update(['qr_code' => null, 'qr_generated_at' => null]);
>>> app(App\Services\QRCodeService::class)->generateForReservation($res);
```

### Problem: "Vysoký failure rate"

**Co dělat:**
1. Podívejte se na Alert dashboard
2. Zkontrolujte access_logs report
3. Testujte reader připojení
4. Zkontrolujte konfigurace zámku

```bash
php artisan tinker
>>> $reader = App\Models\RoomReader::find(1);
>>> $reader->testConnection();
>>> $reader->getSuccessRate(); // % úspěšnosti
```

---

## API Příklady pro QR čtečky

### Python (Raspberry Pi)
```python
import requests
import json
from pyzbar.pyzbar import decode
from PIL import Image

# Scan QR kód
img = Image.open('qr_scan.jpg')
decoded = decode(img)
qr_data = decoded[0].data.decode()

# Odeslat na server
response = requests.post(
    'http://192.168.1.50:8000/api/v1/qr/validate',
    json={
        'qr_data': qr_data,
        'room_id': 1,
        'reader_token': 'your_reader_token',
        'reader_name': 'MainDoor-01'
    }
)

if response.json()['access']:
    # Unlock GPIO pin
    GPIO.output(17, GPIO.HIGH)  # Pin pro relay
    time.sleep(5)
    GPIO.output(17, GPIO.LOW)
```

### JavaScript (Browser QR scanner)
```javascript
// Potřeba: html5-qrcode library
const html5QrcodeScanner = new Html5QrcodeScanner("reader", {fps: 10});

html5QrcodeScanner.render(success => {
    const decodedText = success;
    fetch('/api/v1/qr/validate', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            qr_data: decodedText,
            room_id: roomId,
            reader_token: readerToken
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.access) {
            alert('✅ Přístup povolen!');
        } else {
            alert('❌ Přístup zamítnut: ' + data.reason);
        }
    });
}, error => console.log(error));
```

### cURL
```bash
curl -X POST http://localhost:8000/api/v1/qr/validate \
  -H "Content-Type: application/json" \
  -d '{
    "qr_data": "{\"rid\":1,\"token\":\"abc\",\"room\":1,\"start\":1705680000,\"end\":1705687200,\"type\":\"reservation\"}",
    "room_id": 1,
    "reader_token": "your_token"
  }'
```

---

## Bezpečnost

### Implementované ochrany

1. **Rate Limiting** - Max 100 pokusů/min na QR endpoint
2. **Token Validation** - Unikátní token pro každou rezervaci
3. **Time Windows** - Přístup dostupný pouze v určité dobu
4. **HMAC-SHA256** - Webhook signing pro bezpečnost
5. **IP Whitelisting** - Volitelně omezit podle IP čtečky
6. **Audit Trail** - Vše se loguje do access_logs
7. **Bearer Tokens** - Sanctum pro admin API
8. **HTTPS** - V produkci používejte HTTPS

### Best Practices

1. Používejte silné tokeny (32+ znaků)
2. Střídejte tokeny pravidelně
3. Monitorujte reader alerts
4. Backupujte databázi
5. Logujte všechny administrační akce
6. Testujte čtečky pravidelně

---

## Součást laravel-rehearsal-booking

Tato dokumentace je součástí kompletního QR Reader systému.

**GitHub:** https://github.com/Arpad70/rehearsal-booking

**Verze:** 1.0 (Phase 1-6 Complete)

**Poslední aktualizace:** 18. listopadu 2025

---

## Licence

Tento projekt je licencován pod MIT licencí.

## Support

Máte otázky? Vytvořte issue na GitHubu.

