# Architektonické Srovnání: Laravel vs Joomla Implementation

## Shrnutí
Analýza Joomla komponenty `com_zkusebny` (QR/Shelly řízení přístupu) v porovnání s Laravel aplikací `rehearsal-app` odhalila klíčové architektonické rozdíly, limitace a příležitosti pro vylepšení.

---

## 1. OVĚŘOVÁNÍ & AUTORIZACE

### Joomla (com_zkusebny)
```php
// Autentifikace čtečky (token-based)
if (!$this->authenticateReader($roomId, $readerToken)) {
    return ['access' => false, 'code' => 'UNAUTHORIZED_READER'];
}

// Ověření v databázi
private function authenticateReader($roomId, $readerToken) {
    $sql = "SELECT qr_reader_token FROM #__zkusebny_rooms WHERE id = {$roomId}";
    $room = $result->fetch_object();
    return $room && $room->qr_reader_token === $readerToken;
}
```

**Charakteristika:**
- Jednoduché string porovnání ($token === $tokenFromDB)
- Přímý SQL query bez abstrakce
- Token uložen v plain-text v DB
- Bez rate-limiting/brute-force ochrany
- Bez audit logu jednotlivých pokusů

### Laravel (rehearsal-app)
```php
// app/Http/Controllers/Api/AccessController.php
$this->middleware('throttle:access-validation');

// app/Http/Middleware/ThrottleAccessValidation
// 60 req/min per IP + optional IP whitelist
```

**Charakteristika:**
- Middleware rate-limiting (60 req/min)
- IP whitelist pro správu/lokální čtečky
- Kompletní audit trail (AccessLog model)
- Token s immediate used_at timestamp
- HMAC signed Sanctum tokens

---

## 2. API DESIGN & KOMUNIKACE

### Joomla API (qr_api.php)
```php
// Lightweight, bez Joomla overhead
if (isset($_GET['api']) && $_GET['api'] === 'qr_access') {
    // Přímé MySQL připojení
    $mysqli = new mysqli($config->host, ...);
    
    // Podporované akce přes $_POST
    switch ($action = $_POST['action'] ?? '') {
        case 'verify_qr':
            $qrData = $_POST['qr_data'];
            $result = $accessController->verifyQRAccess($roomId, $qrData, $readerToken);
            echo json_encode($result);
            break;
        
        case 'verify_global_qr':
            // Globální přístup (hlavní vchod, servis)
            $result = $accessController->verifyGlobalQRAccess($globalReaderId, $qrData, $readerToken);
            break;
        
        case 'status':
            // Health check endpoint
            echo json_encode(['status' => 'online']);
            break;
        
        case 'heartbeat':
            // Monitoring
            echo json_encode(['alive' => true]);
            break;
    }
}
```

**Charakteristika:**
- Minimalistický design (nízké overhead pro embedded readers)
- CORS povoleno (`Access-Control-Allow-Origin: *`)
- OPTIONS pre-flight support
- Krátké response časy (direkt MySQL bez ORM)
- Status/heartbeat endpoints pro monitoring
- Chyby vrací HTTP 400/500 s JSON error details

### Laravel API (v1)
```php
// routes/api.php
Route::post('/validate-access', [AccessController::class, 'validateAccess'])
    ->middleware(['auth:sanctum', 'throttle:access-validation']);

// Standardní RESTful design
Route::prefix('v1')->group(function () {
    Route::get('/rooms', [RoomController::class, 'index']);
    Route::get('/rooms/{id}/availability', [RoomController::class, 'checkAvailability']);
});
```

**Charakteristika:**
- Standardní RESTful s versioning (/api/v1)
- Eloquent ORM (abstrakce, ale pomalejší)
- Centralizovaná autentifikace (Sanctum)
- Rate-limiting built-in
- JSON response standard

---

## 3. QR KÓD GENEROVÁNÍ & MANAGEMENT

### Joomla (QRManager.php)
```php
public function generateQRCode($reservationId) {
    // 1. Validace: payment_status === 'paid'
    if ($reservation->payment_status !== 'paid') {
        return ['success' => false, 'message' => 'Rezervace není zaplacena'];
    }
    
    // 2. Generování tokenu
    $accessToken = bin2hex(random_bytes(32)); // 64-char hex
    
    // 3. QR data (JSON strukturované)
    $qrData = json_encode([
        'reservation_id' => $reservationId,
        'access_token' => $accessToken,
        'user_id' => $reservation->user_id,
        'room_id' => $reservation->room_id,
        'slot_start' => $reservation->slot_start,
        'slot_end' => $reservation->slot_end,
        'generated_at' => date('Y-m-d H:i:s'),
        'type' => 'rehearsal_room_access'
    ]);
    
    // 4. Vytvoření obrázku - tři fallback strategie
    // a) Google Charts API (starší, ale nejjednoduší)
    $googleQRUrl = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=" . urlencode($qrData);
    
    // b) QR Server (www.qrserver.com)
    $qrServerUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrData);
    
    // c) Textový fallback (plain text)
    if (všechno selhalo) {
        $textQR = "data:text/plain;charset=utf-8,QR%20Code%20Data:" . urlencode($data);
    }
    
    // 5. Uložení do DB
    UPDATE zkusebny_reservations 
    SET qr_code = '{qrCodeUrl}', 
        access_token = '{accessToken}',
        qr_generated_at = NOW() 
    WHERE id = {$reservationId}
}

// Kompaktní QR pro kratší URL
private function createQRCodeImage($data, $reservationId) {
    $compactData = json_encode([
        'rid' => $reservationId,           // reservation ID (krátký)
        'token' => bin2hex(random_bytes(16)), // 32-char token (kratší)
        'type' => 'room_access'
    ]);
    // → Kratší JSON = menší QR kód = snazší skenování
}

// QR ověřování
public function validateQRCode($qrData, $accessToken) {
    $data = json_decode($qrData, true);
    if ($data['type'] !== 'rehearsal_room_access') {
        return ['valid' => false];
    }
    
    // Kontrola časového okna: 15 min před až konec rezervace
    $now = time();
    $start = strtotime($reservation->slot_start);
    $end = strtotime($reservation->slot_end);
    $accessStart = $start - 900;  // 15 minut
    $accessEnd = $end;
    
    if ($now < $accessStart) {
        return ['valid' => false, 'message' => "Příliš brzy. Za {$minutesUntil} minut"];
    }
    if ($now > $accessEnd) {
        return ['valid' => false, 'message' => 'Rezervace již skončila'];
    }
    
    return ['valid' => true];
}
```

**Charakteristika:**
- Plná JSON struktura v QR (všechny potřebné info)
- Fallback strategie (Google → QRServer → Text)
- Lokální cachování obrázků
- Kompaktní verze pro kratší URL
- Časové okno: 15 min před až konec
- Přímá validace bez DB lookup (QR data = zdroj pravdy)

### Laravel (rehearsal-app) - CHYBÍ!
```
❌ Není implementován QR kód generátor
❌ Není přímá validace z QR dat
✅ Má API token systém (Sanctum)
✅ Má reservation access_token
```

---

## 4. PŘÍSTUPOVÝ SYSTÉM - MÍSTNOSTI vs GLOBÁLNÍ

### Joomla - Dvoustupňová struktura

#### A) Místností-specifické čtečky
```php
// Tabulka: zkusebny_rooms
- qr_reader_enabled      // Je povolena
- qr_reader_ip          // IP adresa čtečky
- qr_reader_port        // Port (výchozí 8080)
- qr_reader_token       // Auth token
- door_lock_type        // 'relay', 'api', 'webhook'
- door_lock_config      // JSON s konfigurací
- qr_reader_ip, port, token

// Endpoint: POST /?api=qr_access&action=verify_qr&room_id=1
public function verifyQRAccess($roomId, $qrData, $readerToken) {
    // 1. Ověř token čtečky pro konkrétní místnost
    $authenticateReader($roomId, $readerToken)
    
    // 2. Validace QR dat
    $qrInfo = json_decode($qrData);
    if ($qrInfo['type'] !== 'room_access') fail;
    
    // 3. Ověř časové okno (15 min před až konec)
    // 4. Zaloguj přístup
    // 5. Odemkni dveře
    $unlockDoor($roomId);
}
```

#### B) Globální čtečky (hlavní vchod, servis)
```php
// Tabulka: zkusebny_global_readers
- reader_name           // "Hlavní vchod", "Zadní brana"
- access_type           // 'entrance', 'service', 'admin'
- reader_ip, reader_port
- reader_token          // Auth
- door_lock_type        // 'relay', 'api', 'webhook'
- door_lock_config      // JSON
- status                // 0/1 enabled

// Endpoint: POST /?api=qr_access&action=verify_global_qr&global_reader_id=1
public function verifyGlobalQRAccess($globalReaderId, $qrData, $readerToken) {
    // 1. Ověř token čtečky
    $globalReader = $getGlobalReaderDetails($globalReaderId);
    if ($globalReader->reader_token !== $readerToken) fail;
    
    // 2. Rozliš typ QR
    switch ($qrInfo['type']) {
        case 'room_access':
            // Rezervační QR → Rozšířené časové okno (30 min před + 30 min po)
            return $verifyReservationQR($qrInfo, $globalReader);
        
        case 'service_access':
            // Servisní QR → Validita dle service access tabulky
            return $verifyServiceQR($qrInfo, $globalReader);
    }
}

// Servisní přístupy (čistič, údržbář, admin)
// Tabulka: zkusebny_service_access
- user_name             // "Jan Novák"
- access_type           // 'cleaning', 'maintenance', 'admin'
- access_code           // Unikátní kód pro QR
- valid_from, valid_until  // Časové omezení
- allowed_rooms         // CSV seznam ID místností nebo ALL
- unlimited_access      // Boolean

// Ověření
private function verifyServiceQR($qrInfo, $globalReader) {
    $accessCode = $qrInfo['access_code'];
    $serviceAccess = $getServiceAccessByCode($accessCode);
    
    // Validace času
    if ($now < $serviceAccess->valid_from || $now > $serviceAccess->valid_until) {
        return ['access' => false, 'code' => 'SERVICE_CODE_EXPIRED'];
    }
    
    // Kontrola povolených místností (pokud je omezeno)
    if ($serviceAccess->allowed_rooms != 'ALL') {
        $allowedRooms = explode(',', $serviceAccess->allowed_rooms);
        if (!in_array($roomId, $allowedRooms)) fail;
    }
}
```

**Charakteristika Joomla přístupu:**
- ✅ Oddělená místnost-specifická vs globální architektura
- ✅ Rozlišení typů přístupu (reservation, service, admin)
- ✅ Servisní QR kódy s časovými omezeními
- ✅ Flexibilní kontrola povolených místností
- ✅ Rozšířené časové okno pro globální přístup (30 min)
- ✅ Audit trail (access_log tabulka)

### Laravel (rehearsal-app) - CHYBÍ!
```php
// app/Models/Reservation.php existuje, ale:
❌ Nemá QR kód
❌ Nemá room-specifické reader konfiguraci
❌ Nemá service access kódy
❌ Nemá device control logiku (jen jobs)
❌ Chybí globální vs room-specific rozlišení
```

---

## 5. ŘÍZENÍ ZÁMKŮ - MULTI-PROTOCOL

### Joomla (QRAccessController.php)
```php
// door_lock_type rozlišuje 3 protokoly:

// 1) RELAY (GPIO, Arduino, Shelly)
private function unlockViaRelay($room, $config) {
    $url = "http://{$relayIP}:{$relayPort}/relay/{$relayPin}/on?duration={$duration}";
    // Příklad: http://192.168.1.100:8080/relay/1/on?duration=5
    // Vrácí: Relé aktivováno na N sekund
}

// 2) API (chytré zámky - Yale, August, atd.)
private function unlockViaAPI($room, $config) {
    $apiUrl = $config['api_url'];  // https://api.smartlock.com/unlock
    $apiKey = $config['api_key'];  // Auth token
    $lockId = $config['lock_id'];  // Identifikátor zámku
    
    POST $apiUrl {
        "action": "unlock",
        "lock_id": "$lockId",
        "duration": 5
    }
}

// 3) WEBHOOK (vlastní integrace, IoT platformy)
private function unlockViaWebhook($room, $config) {
    $webhookUrl = $config['webhook_url'];
    $secret = $config['webhook_secret'];
    
    // HMAC-SHA256 podpis pro bezpečnost
    $signature = hash_hmac('sha256', json_encode($payload), $secret);
    
    POST $webhookUrl {
        "room_id": 1,
        "action": "unlock",
        "timestamp": 1234567890,
        "X-Signature: sha256=abc123..."
    }
}

// Stejná logika pro globální dveře
// unlockViaGlobalRelay(), unlockViaGlobalAPI(), unlockViaGlobalWebhook()
```

**Charakteristika:**
- 3 protokoly v jednom kódu (DRY princip - duplikace)
- Timeout 5 sekund na HTTP requesty
- @ potlačuje chyby → tiché selhání (vhodné pro edge devices)
- Vrací success/failure zprávu
- Loggování jednotlivých pokusů

### Laravel (rehearsal-app) - ČÁSTEČNÉ
```php
// app/Jobs/TurnOnShellyJob.php
// Pouze Shelly relay support (HTTP API)

private function attemptGatewayToggle(string $mac, int $relay): bool {
    $url = "http://{$gatewayIp}:8080/rpc/Shelly.GetStatus?id={$relay}";
    $result = Http::timeout(5)->get($url);
    // Vrácí: Shelly HTTP API response
}

private function attemptDirectToggle(string $ip, int $relay): bool {
    $url = "http://{$ip}:80/relay/{$relay}?turn=on";
    $response = Http::timeout(3)->get($url);
}

// ❌ CHYBÍ: API a webhook support
// ❌ Nevýhoda: Monolitické řešení, problém při selhání
// ✅ Výhoda: Strukturovaný retry system s backoff
```

---

## 6. EMAIL & KOMUNIKACE SE ZÁKAZNÍKEM

### Joomla (QRManager.php)
```php
public function sendQRCodeEmail($reservationId) {
    // 1. Načti data
    $reservation = getFullReservationData($reservationId);
    
    // 2. Vygeneruj QR pokud chybí
    if (!$reservation->qr_code) {
        $qrResult = generateQRCode($reservationId);
    }
    
    // 3. Vygeneruj HTML email
    $emailContent = prepareEmailContent($reservation);
    // Vrátí: Strukturovaný HTML s QR kódem, detaily rezervace, instrukce
    
    // 4. Odešli
    sendEmail($toEmail, $toName, $subject, $emailContent);
    
    // 5. Zaznamenej
    UPDATE zkusebny_reservations SET email_sent_at = NOW()
}

// HTML obsahuje:
// - QR kód v <img> tagu (přiložený nebo URL)
// - Detaily: čas, místnost, cena
// - Instrukce: dostavit se 5 min před, skenovat QR, kontakt na správu
// - Upozornění: uklidit po sobě
```

### Laravel (rehearsal-app) - NENÍ!
```php
// app/Mail/ReservationCreatedMail.php existuje ale:
❌ Nemá QR kód
❌ Nemá instrukce pro skenování
❌ Nemá access token info
```

---

## 7. MONITORING & AUDIT LOG

### Joomla (QRAccessController.php)
```php
// Tabulka: zkusebny_access_log
- reservation_id        // Reference na rezervaci (0 = servis)
- room_id              // Která místnost
- access_granted       // 0/1 úspěšný přístup
- access_code          // 'QR_SUCCESS', 'TOO_EARLY', 'EXPIRED', 'WRONG_ROOM', itd.
- access_time          // DATETIME
- access_type          // 'reservation', 'service'
- reader_type          // 'room', 'global'
- global_reader_id     // Reference na globální čtečku

// Zalogování
private function logAccess($reservationId, $roomId, $success, $code) {
    INSERT INTO zkusebny_access_log VALUES (...)
}

private function logGlobalAccess($reservationId, $globalReaderId, $success, $code, $accessType, $serviceId) {
    INSERT INTO zkusebny_access_log VALUES (...)
}

// Status endpoint pro monitorování
case 'status':
    echo json_encode(['status' => 'online', 'timestamp' => time()]);
    break;

case 'heartbeat':
    echo json_encode(['alive' => true]);
    break;
```

### Laravel (rehearsal-app) - ČÁSTEČNÉ
```php
// app/Models/AccessLog.php
- user_id              // Který uživatel
- validation_result    // 'success', 'invalid_token', 'rate_limit'
- ip_address          // Kde se validovalo
- user_agent          // Jaké zařízení
- validated_at        // Kdy

// ✅ Je implementováno, ale:
❌ Nemá room_id
❌ Nemá access_code (důvod selhání)
❌ Nemá reader_type (room vs global)
❌ Nemá global_reader_id
❌ Chybí heartbeat endpoint

// app/Models/AuditLog.php
- Loguje create/update/delete na Reservation
- ✅ Kompletní, ale chybí device control events
```

---

## 8. BEZPEČNOST - DETAILNÍ POROVNÁNÍ

| Aspekt | Joomla | Laravel |
|--------|--------|---------|
| **Token Auth** | Plain-text v DB | Hashed (Sanctum) |
| **Token Signing** | Žádný | HMAC (Sanctum) |
| **Rate Limiting** | ❌ CHYBÍ | ✅ 60 req/min |
| **IP Whitelist** | ❌ CHYBÍ | ✅ Pro manage. |
| **Brute Force** | Zranitelný | Chráněný |
| **CORS** | `*` (otevřeno) | Specifické domény |
| **Audit Trail** | ✅ Kompletní | ⚠️ Částečné |
| **Time Window** | 15 min pro room, 30 min pro global | N/A (chybí) |
| **Webhook Signing** | HMAC-SHA256 | Podporováno |
| **Options Pre-flight** | ✅ Explicitně | ✅ Implicitně |

---

## 9. DOPORUČENÁ VYLEPŠENÍ PRO LARAVEL

### PRIORITA 1 (Kritické)

#### 1.1 Implementovat QR kód generátor
```php
// app/Services/QRCodeService.php
public function generateForReservation(Reservation $reservation): string {
    // JSON struktura s reservation_id, access_token, metadata
    // Fallback: Google Charts → QRServer → Text
    // Uložit do media/qrcodes/{reservation_id}_{timestamp}.png
}

public function validateQRData(string $qrData): ?array {
    // Dekóduj JSON, validuj časové okno, vrať booking info
}

// Migration: ALTER TABLE reservations 
// ADD COLUMN qr_code VARCHAR(255)
// ADD COLUMN access_token VARCHAR(64) UNIQUE
// ADD COLUMN qr_generated_at TIMESTAMP
```

#### 1.2 Oddělená architektura: Room-specific vs Global readers
```php
// app/Models/RoomReader.php (NEW)
- room_id
- reader_ip, reader_port
- reader_token
- door_lock_type ('relay', 'api', 'webhook')
- door_lock_config (JSON)

// app/Models/GlobalReader.php (NEW)
- reader_name ("Hlavní vchod", "Servis")
- access_type ('entrance', 'service', 'admin')
- reader_ip, reader_port, reader_token
- door_lock_type, door_lock_config

// app/Models/ServiceAccess.php (NEW)
- user_id
- access_type ('cleaning', 'maintenance', 'admin')
- access_code
- valid_from, valid_until
- allowed_rooms (JSON array nebo '*')
```

#### 1.3 Multi-protocol door control
```php
// app/Services/DoorLockService.php
public function unlock(Room $room, string $protocol): bool {
    switch ($room->door_lock_type) {
        case 'relay':
            return $this->unlockRelay($room);
        case 'api':
            return $this->unlockAPI($room);
        case 'webhook':
            return $this->unlockWebhook($room);
    }
}

// Stejné pro GlobalReader s expandovaným timeout pro entrance
```

#### 1.4 Rozšírený audit log
```php
// Migration: reservations & access_logs
- access_code (VARCHAR: 'QR_SUCCESS', 'TOO_EARLY', 'EXPIRED', 'WRONG_ROOM', 'NOT_PAID')
- access_type ('reservation', 'service')
- reader_type ('room', 'global')
- global_reader_id (nullable)

// app/Models/AccessLog
// Přidat: room_id, access_code, reader_type, global_reader_id
```

### PRIORITA 2 (Vysoká)

#### 2.1 API endpoints pro device management
```php
// routes/api.php
POST /api/v1/qr/validate              # Ověření QR od čtečky
POST /api/v1/rooms/{id}/unlock        # Odemčení místnosti
POST /api/v1/status                   # Health check
POST /api/v1/heartbeat               # Monitoring

// Endpointy bez authentication (jen reader token)
// Nízký overhead pro embedded readers
```

#### 2.2 Email se QR kódem
```php
// app/Mail/ReservationCreatedMail.php
// Přidat:
// - QR kód obrázek
// - access_token (textově)
// - Instrukce pro skenování
// - Časové okno přístupu (15 min před - konec)
```

#### 2.3 Health check & monitoring
```php
// routes/api.php - bez auth
GET /api/v1/status
// Vrátí: online, timestamp, room_readers_status, global_readers_status

GET /api/v1/heartbeat
// Vrátí: alive, timestamp
```

### PRIORITA 3 (Střední)

#### 3.1 Servisní přístupy (service access)
```php
// Umožnit čistícímu, údržbáři, adminovi přístup bez rezervace
// Podrobně viz Joomla implementace ServiceAccess
```

#### 3.2 Flexibilní webhook integrace
```php
// Místo přímo v kódu, umožnit konfiguraci webhook URL
// S HMAC-SHA256 podpisem
```

#### 3.3 IP whitelist pro lokální čtečky
```php
// config/reservations.php
'local_reader_ips' => ['192.168.1.100', '192.168.1.101'],
// Bypass rate limiting pro lokální čtečky
```

---

## 10. IMPLEMENTAČNÍ PLÁN

### Fáze 1: Datový model (1-2 dny)
1. Migrations pro RoomReader, GlobalReader, ServiceAccess
2. Updatovat Reservation model (qr_code, access_token)
3. Updatovat AccessLog schema (access_code, reader_type, global_reader_id)

### Fáze 2: QR systém (2-3 dny)
1. QRCodeService s fallback strategií
2. Generování QR při vytvoření rezervace
3. Email s QR kódem
4. QR validace endpoint

### Fáze 3: Reader management (2-3 dny)
1. Admin rozhraní pro RoomReader (Filament)
2. Admin rozhraní pro GlobalReader (Filament)
3. Konfigurační formuláře (door_lock_type, API credentials)
4. Test připojení k čtečce (connection test endpoint)

### Fáze 4: Odemykání (2-3 dny)
1. DoorLockService s 3 protokoly
2. Relay (Shelly, Arduino) - refactor stávajícího kódu
3. API (chytré zámky)
4. Webhook (vlastní integrace)

### Fáze 5: Testing (2 dny)
1. QR validace testy
2. Door unlock testy (mockované)
3. Audit log verifikace
4. Security (rate limiting, brute force)

### Fáze 6: Documentation (1 den)
1. API dokumentace
2. Reader setup guide
3. Service access setup

---

## 11. KLÍČOVÉ VÝTĚŽKY

| Aspekt | Status | Dopad |
|--------|--------|-------|
| **QR kódy** | ❌ Chybí | HIGH - Nutné pro fyzický přístup |
| **Reader management** | ❌ Chybí | HIGH - Kritické pro běh systému |
| **Multi-protocol doors** | ⚠️ Jen Shelly | MEDIUM - Rozšíření pro budoucnost |
| **Service access** | ❌ Chybí | MEDIUM - Čistící, údržba |
| **Email komunikace** | ⚠️ Bez QR | MEDIUM - UX zlepšení |
| **Audit trail** | ✅ Částečné | LOW - Vylepšit schéma |
| **Rate limiting** | ✅ OK | OK - Už implementováno |
| **Bezpečnost** | ✅ OK | OK - Dostatečné |

---

## 12. ZÁVĚR

Joomla komponenta `com_zkusebny` demonstruje **robustnější přístup k QR/door control systému** s:
- Oddělením room-specific vs global readers
- Servisními přístupy a jejich validací
- Multi-protocol door locking
- Podrobným audit logem

Laravel aplikace má **solidní základy** v bezpečnosti (rate limiting, Sanctum tokens), ale **chybí jí fyzická integrační vrstva** pro:
- QR kódy
- Reader management
- Door control

**Doporučuji implementovat Prioritu 1 (QR + Reader management)** pro dosažení parity s Joomla systémem a umožnit fyzický přístup do místností.

