# QR Reader System - Phase Summary

## Kompletní Implementace Fází 1-3

Implementace QR a door lock systému byla úspěšně dokončena dle architektonické analýzy Joomla komponenty `com_zkusebny`.

---

## Phase 1: Datový Model ✅

### Vytvořené tabulky a modely:

#### 1. RoomReader
- **Tabulka:** `room_readers`
- **Účel:** Čtečky specifické pro jednotlivé místnosti
- **Klíčová pole:**
  - `room_id` - Vztah k místnosti
  - `reader_ip`, `reader_port`, `reader_token` - Síťová konfigurace
  - `door_lock_type` - Typ zámku (relay/api/webhook)
  - `door_lock_config` - JSON s konkrétní konfigurací
- **Funkce:**
  - `isHealthy()` - Zkontroluje, zda je čtečka dostupná
  - `testConnection()` - Testuje připojení k zařízení
  - `getLockConfig()` - Vrátí konfiguraci zámku

#### 2. GlobalReader
- **Tabulka:** `global_readers`
- **Účel:** Globální čtečky (hlavní vchod, servis, admin)
- **Klíčová pole:**
  - `access_type` (entrance/service/admin)
  - `access_minutes_before`, `access_minutes_after` - Rozšíření časového okna
  - `allowed_service_types` - Které typy servisních přístupů jsou povoleny
  - `door_lock_type`, `door_lock_config` - Konfigurace zámku
- **Funkce:**
  - `allowsServiceType()` - Ověří, zda je daný typ povolen
  - `getAccessBoundaries()` - Vrátí časové hranice přístupu
  - `testConnection()` - Testuje připojení

#### 3. ServiceAccess
- **Tabulka:** `service_access`
- **Účel:** Servisní přístupy pro čistící, údržbáře, adminy
- **Klíčová pole:**
  - `user_id` - Kterému zaměstnanci
  - `access_type` (cleaning/maintenance/admin)
  - `access_code` - Unikátní kód pro QR
  - `allowed_rooms` - JSON array s povolenými místnostmi
  - `valid_from`, `valid_until` - Časová omezení
  - `unlimited_access` - Boolean pro přístup do všech místností
  - `revoked`, `revoke_reason` - Zrušení přístupu
- **Funkce:**
  - `isValid()` - Zkontroluje, zda je přístup aktuálně platný
  - `allowsRoom()` - Ověří přístup do konkrétní místnosti
  - `revoke()` - Zruší přístup
  - `recordUsage()` - Zaznamená použití

#### 4. Reservation (rozšíření)
- **Nová pole:**
  - `access_token` - Token pro QR (již bylo)
  - `qr_code` - Cesta k QR obrázku (NOVÉ)
  - `qr_generated_at` - Kdy byl QR vygenerován (NOVÉ)
  - `qr_sent_at` - Kdy byl QR poslán emailem (NOVÉ)
- **Nové funkce:**
  - `isQRValid()` - Zkontroluje, zda je QR v platném časovém okně
  - `getQRAccessWindow()` - Vrátí přesné časové hranice

#### 5. AccessLog (rozšíření)
- **Nová pole:**
  - `access_code` - Kód důvodu (QR_SUCCESS, TOO_EARLY, EXPIRED, atd.)
  - `access_type` (reservation/service)
  - `reader_type` (room/global)
  - `global_reader_id` - Reference na globální čtečku
  - `ip_address`, `user_agent` - Detaily requesty
  - `validated_at` - Časové razítko
- **Rozšíření:** Nyní loguje QR specifické informace

### Migrační soubory:
- `2025_01_01_000005_create_room_readers_table.php`
- `2025_01_01_000006_create_global_readers_table.php`
- `2025_01_01_000007_create_service_access_table.php`
- `2025_01_01_000008_add_qr_support_to_reservations_table.php`
- `2025_01_01_000009_enhance_access_logs_for_qr_system.php`

### Relace (Relationships):
- `Room::readers()` → HasMany RoomReader
- `RoomReader::room()` → BelongsTo Room
- `GlobalReader::accessLogs()` → HasMany AccessLog
- `User::serviceAccess()` → HasMany ServiceAccess
- `AccessLog::globalReader()` → BelongsTo GlobalReader

---

## Phase 2: QR Kódy a Door Control ✅

### QRCodeService
**Soubor:** `app/Services/QRCodeService.php`

**Funkce:**

1. **Generování QR kódů**
   - `generateForReservation()` - Vygeneruje QR pro rezervaci
   - Fallback strategie:
     - Google Charts API (starší, ale kompatibilní)
     - QR Server API (www.qrserver.com)
     - QuickChart API (alternativa)
     - Text placeholder (fallback)

2. **Validace QR dat**
   - `validateQRData()` - Ověří QR data vs rezervaci
   - Checks:
     - Správný reservation ID
     - Správná místnost
     - Časové okno (15 min před až konec)
     - Kontrola chyb (TOO_EARLY, EXPIRED, WRONG_ROOM)

3. **Utility funkce**
   - `isQRCurrentlyValid()` - Lze dnes skenovat?
   - `getAccessWindow()` - Jaké je časové okno?
   - `cleanupOldQRCodes()` - Vymazání starých obrázků

**QR Data Format:**
```json
{
  "rid": 1,              // Reservation ID
  "token": "abc...",     // Access token (zkrácený)
  "room": 1,             // Room ID
  "start": 1705680000,   // Unix timestamp
  "end": 1705687200,     // Unix timestamp
  "type": "reservation"  // Typ
}
```

### DoorLockService
**Soubor:** `app/Services/DoorLockService.php`

**Funkce:**

1. **Odemykání místnosti**
   - `unlockRoom()` - Odemkne místnost přes RoomReader
   - Podporuje 3 protokoly:
     - Relay (GPIO/Arduino/Shelly)
     - API (Smart Lock)
     - Webhook (Home Assistant, atd.)

2. **Odemykání globálních čteček**
   - `unlockGlobalReader()` - Odemkne hlavní vchod
   - Prodloužené timeout (10s místo 5s)

3. **Jednotlivé implementace:**
   - `unlockViaRelay()` - HTTP GET na /relay/{pin}/on?duration={s}
   - `unlockViaAPI()` - HTTP POST s JSON payload
   - `unlockViaWebhook()` - HTTP POST s HMAC-SHA256 podpisem

4. **Testing**
   - `testConnection()` - Zkontroluje dostupnost čtečky

**Protokoly v detailu:**

#### Relay (Shelly, Arduino)
```
GET http://192.168.1.100:8080/relay/1/on?duration=5
Authorization: Bearer <token>
```

#### Smart Lock API
```
POST https://api.smartlock.com/unlock
{
  "action": "unlock",
  "lock_id": "room_123",
  "duration": 5
}
```

#### Webhook (HMAC-SHA256 signed)
```
POST https://webhook.example.com/unlock
X-Signature: sha256=<signature>
{
  "room_id": 1,
  "reader_id": 5,
  "action": "unlock",
  "timestamp": 1700000000
}
```

### QRAccessController
**Soubor:** `app/Http/Controllers/Api/QRAccessController.php`

**Endpoints:**

1. **POST /api/v1/qr/validate**
   - Ověří QR kód a odemkne místnost
   - Vyžaduje: qr_data, room_id, reader_token
   - Vrací: access (bool), message, door_unlocked status
   - Loguje všechny pokusy do AccessLog

2. **GET /api/v1/qr/status**
   - Health check pro čtečky
   - Vrací: online status, server time

3. **GET /api/v1/qr/heartbeat**
   - Monitoring endpoint
   - Vrací: alive (bool), timestamp

4. **POST /api/v1/rooms/{roomId}/readers/{readerId}/test**
   - Test připojení ke čtečce
   - Vyžaduje autentifikaci

### Konfigurace
**Soubor:** `config/reservations.php` (rozšířeno)

```php
'qr_reader_rate_limit' => 100,      // req/min
'qr_rate_window' => 1,              // minuty
'qr_access_minutes_before' => 15,   // min před
'qr_cleanup_days' => 30,            // cleanup old
'default_relay_pin' => 1,           // výchozí pin
'default_unlock_duration' => 5,     // sec
'default_global_unlock_duration' => 10,
'global_reader_minutes_before' => 30,
'global_reader_minutes_after' => 30,
```

---

## Phase 3: Admin Interface (Filament) ✅

### RoomReaderResource
**Soubor:** `app/Filament/Resources/RoomReaderResource.php`

**Funkce:**
- CRUD pro room readers
- Konfigurace pro Relay/API/Webhook
- Test Connection action
- Filtry: enabled status
- Tabulka se IP adresou, lock type, status

**Pages:**
- `ListRoomReaders` - Výpis všech čteček
- `CreateRoomReader` - Přidání nové čtečky
- `EditRoomReader` - Úprava existující

**Admin sekce:** Device Management (Správa zařízení)

### GlobalReaderResource
**Soubor:** `app/Filament/Resources/GlobalReaderResource.php`

**Funkce:**
- CRUD pro globální čtečky
- Nastavení access_type (entrance/service/admin)
- Konfigurace access windows (30 min before/after)
- Service type filtering
- Test Connection action

**Filtry:**
- access_type (entrance, service, admin)
- enabled status

**Admin sekce:** Device Management

### ServiceAccessResource
**Soubor:** `app/Filament/Resources/ServiceAccessResource.php`

**Funkce:**
- CRUD pro servisní přístupy
- Linkuje na Users (dropdown)
- Nastavení časových omezení
- Unlimited room access nebo konkrétní místnosti
- Tracking: usage_count, last_used_at
- Revoke action s důvodem

**Akce:**
- Generate QR - Vygeneruje access_code
- Revoke - Zruší přístup s logem důvodu

**Admin sekce:** Access Control (Přístupová práva)

### Filament Pages
```
RoomReaderResource/
├── Pages/
│   ├── ListRoomReaders.php
│   ├── CreateRoomReader.php
│   └── EditRoomReader.php

GlobalReaderResource/
├── Pages/
│   ├── ListGlobalReaders.php
│   ├── CreateGlobalReader.php
│   └── EditGlobalReader.php

ServiceAccessResource/
├── Pages/
│   ├── ListServiceAccess.php
│   ├── CreateServiceAccess.php
│   └── EditServiceAccess.php
```

---

## Bezpečnost ✅

### Implementované ochrany:

1. **Rate Limiting**
   - 100 pokusů/minutu na QR endpoint
   - IP-based throttling

2. **Autentifikace**
   - Reader token (Bearer token)
   - Per-reader authorization
   - Per-room authorization

3. **Audit Trail**
   - Všechny pokusy v AccessLog
   - IP adresa, user agent
   - Access codes (důvody selhání)
   - Timestamps

4. **Webhook Security**
   - HMAC-SHA256 podpis
   - Secret key validation

5. **Time Windows**
   - 15 min QR plavidla (reservations)
   - 30 min globální přístup (entrance)
   - Timezone-aware validation

6. **Token Validation**
   - Unique per reservation
   - Expiration checking
   - Format validation

---

## Testy ✅

### Testování v aplikaci:

```bash
# Health check
curl http://localhost:8000/api/v1/qr/status

# Validace QR (example data)
curl -X POST http://localhost:8000/api/v1/qr/validate \
  -H "Content-Type: application/json" \
  -d '{
    "qr_data": "{\"rid\":1,\"token\":\"abc\",\"room\":1,\"start\":1705680000,\"end\":1705687200,\"type\":\"reservation\"}",
    "room_id": 1,
    "reader_token": "your_token"
  }'

# Test připojení čtečky (requires auth)
curl -X POST http://localhost:8000/api/v1/rooms/1/readers/1/test \
  -H "Authorization: Bearer <sanctum_token>"
```

### Filament Testing:
1. Přidat RoomReader
2. Kliknout "Test Connection"
3. Ověřit odpověď

### Database Testing:
```sql
SELECT * FROM access_logs ORDER BY created_at DESC LIMIT 10;
SELECT * FROM room_readers WHERE enabled = 1;
SELECT * FROM service_access WHERE revoked = 0;
```

---

## Dokumentace ✅

Vytvořené dokumenty:

1. **ARCHITECTURAL_REVIEW.md** (12 sekcí)
   - Srovnání Joomla vs Laravel
   - Klíčové rozdíly
   - Doporučená vylepšení
   - Implementační plán

2. **QR_IMPLEMENTATION_GUIDE.md** (11 sekcí)
   - Hardware konfigurace
   - Filament setup guide
   - API dokumentace
   - Troubleshooting
   - Use cases

3. **PHASE_SUMMARY.md** (aktuální)
   - Co bylo implementováno
   - Detaily jednotlivých komponent
   - Bezpečnost
   - Testing

---

## Git Commits ✅

Veškerý kód byl pushnut na GitHub v 5 commits:

1. **25874da** - Architectural review (srovnání s Joomla)
2. **9a20c25** - Phase 1: Data models (migrations, models)
3. **48096d6** - Phase 2: Services (QRCode, DoorLock, API)
4. **6080a41** - Phase 3a: Filament readers (RoomReader, GlobalReader)
5. **daac87c** - Phase 3b: ServiceAccess resource
6. **a03d996** - Implementation guide

**Repository:** https://github.com/Arpad70/rehearsal-booking

---

## Co Zbývá (Phase 4+)

### Phase 4: Email Integration
- [ ] Odeslání QR emailem při vytvoření rezervace
- [ ] Email s instrukcemi pro přístup
- [ ] Servisní emaily pro ServiceAccess

### Phase 5: Statistics & Reports
- [ ] Dashboard s počtem přístupů
- [ ] Reports o používání místností
- [ ] Analýza servisních přístupů

### Phase 6: Advanced Features
- [ ] Multiple QR per reservation (backup)
- [ ] Access history export
- [ ] Reader firmware updates
- [ ] Multi-language support

---

## Shrnutí

✅ **Kompletně implementováno:**
- Datový model pro readers, service access
- QR generátor se fallbacky
- 3-protokolový door lock sistem
- API endpoints s rate limitingem
- Filament admin interface
- Komprehenzivní dokumentace
- Security best practices

🎯 **Výsledek:** Plně funkční QR reader systém pro kontrolu přístupu do místností s admin interfacem a API.

**Stav:** Ready for testing and deployment ✅

---

**Datum:** 18. listopadu 2025
**Verze:** 1.0 (Phase 1-3)
**Autor:** GitHub Copilot

