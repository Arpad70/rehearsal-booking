# Analýza Device Integration - Doporučení a vylepšení

> **Datum analýzy:** 21. listopadu 2025  
> **Analyzované zdroje:**
> - `/docs/DEVICE_INTEGRATION.md` (474 řádků)
> - `/docs/simulators/*.md` (14 souborů, ~6000 řádků dokumentace)
> - Implementované Device Services (5 tříd)
> - Docker compose konfigurace

---

## 📊 Současný stav implementace

### ✅ CO JE IMPLEMENTOVÁNO v Rehearsal App

1. **Device Services** - Základní komunikační vrstva
   - `QRReaderService` - Komunikace s QR čtečkami (Entry E QR R1)
   - `ShellyService` - Ovládání Shelly Pro EM (světla, měření spotřeby)
   - `KeypadService` - RFID keypady (RFID Keypad 7612)
   - `CameraService` - IP kamery (EVOLVEO Detective POE8 SMART)
   - `MixerService` - Soundcraft Ui24R mixáže

2. **Webhook Controller** - Zpracování událostí ze zařízení
   - `DeviceWebhookController` - Příjem QR/RFID scanů
   
3. **Access Control Service** - Business logika autorizace
   - `AccessControlService` - Ověřování rezervací

4. **Database Model**
   - `Device` model s polymorfními meta daty
   - Vztah k místnostem přes `room_id`

5. **Docker Infrastructure**
   - Externí `simulator-network` pro komunikaci se simulátory
   - Environment proměnné pro device porty (QR_READER_BASE_PORT=9101, atd.)

### ✅ CO EXISTUJE v Simulátorech (/mnt/data/www/Simulace)

**Multi-Device Setup** - 26 IoT zařízení:
- **6x QR Čtečky** (Entry E QR R1) - porty 9101-9106, WebSocket ✅
- **12x IP Kamery** (EVOLVEO Detective POE8 SMART) - porty 9201-9212
- **6x Shelly Pro EM** - porty 9501-9506 (POZOR: Dokumentace říká 9301-9306!)
- **2x RFID Keypady** (RFID Keypad 7612) - porty 9401-9402, WebSocket ✅
- **1x Soundcraft Ui24R Mixer** - port 9301 (pro Lab-01 a Lab-02)

**Simulator Features:**
- ✅ Kompletní HTTP REST API
- ✅ WebSocket real-time events (QR, Keypad, Mixer)
- ✅ Heartbeat každých 10s
- ✅ Diagnostika, device info, status endpointy
- ✅ LED/Buzzer/Relay ovládání
- ✅ RTSP streamy pro kamery
- ✅ Power monitoring (Shelly)
- ✅ Show file management (Mixer)

### ❌ KRITICKÉ NESROVNALOSTI

#### 1. **Port Mapping Nesoulad**

| Zařízení | Dokumentace DEVICE_INTEGRATION.md | Simulátory README-MULTI.md | Skutečnost |
|----------|----------------------------------|---------------------------|------------|
| QR Čtečky | 9101-9106 ✅ | 9101-9106 ✅ | **OK** |
| IP Kamery | 9201-9206 ❌ | 9201-9212 ✅ | **12 kamer, ne 6!** |
| Shelly | 9501-9506 ✅ | 9301-9306 ❌ | **Konflikt!** |
| Keypady | 9401-9406 ❌ | 9401-9402 ✅ | **Pouze 2, ne 6!** |
| Mixer | 9301-9302 ❌ | 9301 ✅ | **Pouze Lab-01** |

**ZÁVAŽNÝ PROBLÉM:** Shelly používá v simulátorech porty 9301-9306, ale dokumentace říká 9501-9506!

#### 2. **Base URL v Device Services**

```php
// QRReaderService.php - řádek ~15
$this->baseUrl = "http://localhost:{$port}";  // ❌ NEFUNGUJE z Dockeru
```

**Problém:** Z Docker kontejneru `localhost` ukazuje na sám kontejner, ne na host.

**Řešení:** Použít `host.docker.internal` nebo Docker network hostnames:
```php
$host = config('devices.simulator_host', 'host.docker.internal');
$this->baseUrl = "http://{$host}:{$port}";
```

#### 3. **Chybějící docker-compose.simulators.yml**

Simulátory běží samostatně v `/mnt/data/www/Simulace`, ale:
- ❌ Nejsou propojené s rehearsal-app
- ❌ Není definován v rehearsal-app projektu
- ❌ Chybí jednoduchý způsob spuštění všeho najednou

#### 4. **WebSocket Implementace**

**Simulátory podporují:**
- ✅ QR Čtečky: `ws://localhost:9101` - heartbeat, qr_scan, door_unlock, error events
- ✅ Keypady: `ws://localhost:9401` - heartbeat, rfid_scan, pin_entry events
- ✅ Mixer: `ws://localhost:9301` - channel_updated, scene_loaded events

**Rehearsal App:**
- ❌ Žádná WebSocket integrace
- ❌ Neodebírá real-time eventy
- ❌ Pouze polling přes HTTP

## 🎯 DOPORUČENÍ NA ZLEPŠENÍ

### 1. Opravit Port Mapping a Integrovat Simulátory (PRIORITA 1)

#### A) Propojit rehearsal-app se simulátory

**Aktuální stav:**
- Simulátory běží samostatně: `/mnt/data/www/Simulace`
- Rehearsal-app: `/mnt/data/www/rehearsal-app`
- Nejsou propojené!

**Řešení 1: Používat existující simulátory**

Upravit `docker-compose.yml` v rehearsal-app:

```yaml
# docker-compose.yml
services:
  app:
    networks:
      - rehearsal-network
      - simulator-network  # ✅ Již existuje
    environment:
      # OPRAVIT porty dle skutečnosti!
      - QR_READER_BASE_PORT=9101        # ✅ OK
      - CAMERA_BASE_PORT=9201           # ✅ OK
      - SHELLY_BASE_PORT=9501           # ❌ Simulátory používají 9301-9306!
      - KEYPAD_BASE_PORT=9401           # ✅ OK
      - MIXER_BASE_PORT=9301            # ✅ OK
      - SIMULATOR_HOST=172.17.0.1       # ✅ OK (Docker bridge)

networks:
  simulator-network:
    external: true  # Připojí se k síti simulátorů
```

**KRITICKÁ OPRAVA:** Shelly porty jsou v simulátorech **9301-9306**, NE 9501-9506!

```bash
# Spustit simulátory
cd /mnt/data/www/Simulace
docker compose up -d

# Ověřit že běží
docker ps | grep -E "qr-reader|camera|shelly|keypad"

# Spustit rehearsal-app
cd /mnt/data/www/rehearsal-app
docker compose up -d

# Testovat konektivitu z rehearsal-app
docker exec rehearsal-app curl http://172.17.0.1:9101/device-info
docker exec rehearsal-app curl http://172.17.0.1:9501/status  # ❌ Nebude fungovat!
docker exec rehearsal-app curl http://172.17.0.1:9301/status  # ✅ Shelly port
```

#### B) Vytvořit startup script pro kompletní setup

```bash
#!/bin/bash
# scripts/start-all-devices.sh

echo "🚀 Starting IoT Device Simulators..."

# 1. Spustit simulátory
cd /mnt/data/www/Simulace
docker compose down
docker compose up -d --build

# Čekat na start (30s)
echo "⏳ Waiting for simulators to start..."
sleep 30

# 2. Ověřit dostupnost simulátorů
echo "🔍 Checking simulator health..."
for port in 9101 9102 9201 9202 9301 9401; do
  if curl -s http://localhost:$port/ > /dev/null; then
    echo "  ✅ Port $port - OK"
  else
    echo "  ❌ Port $port - FAILED"
  fi
done

# 3. Spustit rehearsal-app
echo "🏗️  Starting Rehearsal App..."
cd /mnt/data/www/rehearsal-app
docker compose up -d

# 4. Test konektivity z aplikace
echo "🧪 Testing connectivity from app..."
docker exec rehearsal-app curl -s http://172.17.0.1:9101/device-info | jq '.device.model'
docker exec rehearsal-app curl -s http://172.17.0.1:9301/status | jq '.switch[0].ison'

echo "✅ All systems ready!"
```

**Použití:**
```bash
chmod +x scripts/start-all-devices.sh
./scripts/start-all-devices.sh
```

#### C) Dokumentovat Device Mapping

Vytvořit `/docs/DEVICE_MAPPING.md`:

```markdown
# Device Mapping - Skutečné porty

## 🌐 Simulator Base URL

Z Docker kontejnerů: `http://172.17.0.1:{PORT}`  
Z host systému: `http://localhost:{PORT}`

## 📱 QR Čtečky (Entry E QR R1)

| Místnost | Device ID | Port | WebSocket | Model |
|----------|-----------|------|-----------|-------|
| Lab-01 | qr-reader-1 | 9101 | ws://localhost:9101 | Entry E QR R1 v3.2.1 |
| Lab-02 | qr-reader-2 | 9102 | ws://localhost:9102 | Entry E QR R1 v3.2.1 |
| Lab-03 | qr-reader-3 | 9103 | ws://localhost:9103 | Entry E QR R1 v3.2.1 |
| Lab-04 | qr-reader-4 | 9104 | ws://localhost:9104 | Entry E QR R1 v3.2.1 |
| Lab-05 | qr-reader-5 | 9105 | ws://localhost:9105 | Entry E QR R1 v3.2.1 |
| Lab-06 | qr-reader-6 | 9106 | ws://localhost:9106 | Entry E QR R1 v3.2.1 |

**API Příklady:**
```bash
# Device info
curl http://172.17.0.1:9101/device-info

# Simulovat scan
curl -X POST http://172.17.0.1:9101/scan \
  -H "Content-Type: application/json" \
  -d '{"code":"RESERVATION_123"}'

# Autorizovat přístup
curl -X POST http://172.17.0.1:9101/authorize \
  -H "Content-Type: application/json" \
  -d '{"scanId":"scan_123","authorized":true,"unlockDuration":5}'
```

## 📹 IP Kamery (EVOLVEO Detective POE8 SMART)

**POZNÁMKA:** Dokumentace uvádí 6 kamer, ale simulátory mají 12!

| Místnost | Kamery | Porty | RTSP Stream |
|----------|--------|-------|-------------|
| Lab-01 | camera-1, camera-2 | 9201-9202 | rtsp://localhost:9201/stream1 |
| Lab-02 | camera-3, camera-4 | 9203-9204 | rtsp://localhost:9203/stream1 |
| Lab-03 | camera-5, camera-6 | 9205-9206 | rtsp://localhost:9205/stream1 |
| Lab-04 | camera-7, camera-8 | 9207-9208 | rtsp://localhost:9207/stream1 |
| Lab-05 | camera-9, camera-10 | 9209-9210 | rtsp://localhost:9209/stream1 |
| Lab-06 | camera-11, camera-12 | 9211-9212 | rtsp://localhost:9211/stream1 |

**API Příklady:**
```bash
# Snapshot
curl http://172.17.0.1:9201/snapshot --output snapshot.jpg

# MJPEG stream
curl http://172.17.0.1:9201/stream

# Motion detection webhook (simulátor volá automaticky)
# POST http://rehearsal-app/api/webhooks/motion-detected
```

## 🔌 Shelly Pro EM (Power Monitoring)

**⚠️ KRITICKÁ OPRAVA:** Simulátory používají porty **9301-9306**, NE 9501-9506!

| Místnost | Device ID | Port (Simulátor) | Port (Dokumentace) | Kanál 0 | Kanál 1 |
|----------|-----------|------------------|---------------------|---------|---------|
| Lab-01 | shelly-pro-em-1 | **9301** ❌ | 9501 | Světla (relé) | Zásuvky (monitoring) |
| Lab-02 | shelly-pro-em-2 | **9302** ❌ | 9502 | Světla (relé) | Zásuvky (monitoring) |
| Lab-03 | shelly-pro-em-3 | **9303** ❌ | 9503 | Světla (relé) | Zásuvky (monitoring) |
| Lab-04 | shelly-pro-em-4 | **9304** ❌ | 9504 | Světla (relé) | Zásuvky (monitoring) |
| Lab-05 | shelly-pro-em-5 | **9305** ❌ | 9505 | Světla (relé) | Zásuvky (monitoring) |
| Lab-06 | shelly-pro-em-6 | **9306** ❌ | 9506 | Světla (relé) | Zásuvky (monitoring) |

**KONFLIKT S MIXEREM:** Port 9301 je používán mixerem i Shelly #1!

**Doporučené řešení:**
1. Přesunout Shelly na porty 9501-9506 (dle dokumentace)
2. Nebo přesunout Mixer na jiný port (např. 9800)

**API Příklady:**
```bash
# Status všech kanálů
curl http://172.17.0.1:9301/status | jq

# Zapnout světla (kanál 0)
curl "http://172.17.0.1:9301/relay/0?turn=on"

# Vypnout světla
curl "http://172.17.0.1:9301/relay/0?turn=off"

# Měření spotřeby (Gen2 RPC)
curl "http://172.17.0.1:9301/rpc/Switch.GetStatus?id=0" | jq
```

## 🔢 RFID Keypady (RFID Keypad 7612)

**POZNÁMKA:** Dokumentace uvádí 6 keypadů, ale simulátory mají pouze 2!

| Místnost | Device ID | Port | WebSocket | Model |
|----------|-----------|------|-----------|-------|
| Lab-01 | keypad-1 | 9401 | ws://localhost:9401 | RFID Keypad 7612 v4.1.2 |
| Lab-02 | keypad-2 | 9402 | ws://localhost:9402 | RFID Keypad 7612 v4.1.2 |

**API Příklady:**
```bash
# Device info
curl http://172.17.0.1:9401/device-info

# RFID scan
curl -X POST http://172.17.0.1:9401/rfid-scan \
  -H "Content-Type: application/json" \
  -d '{"cardId":"1234567890"}'

# PIN entry
curl -X POST http://172.17.0.1:9401/pin-entry \
  -H "Content-Type: application/json" \
  -d '{"pin":"1234"}'
```

## 🎵 Soundcraft Ui24R Mixer

**POZNÁMKA:** Pouze Lab-01 a Lab-02 mají mixer

| Místnost | Device ID | Port | WebSocket | Show Files |
|----------|-----------|------|-----------|------------|
| Lab-01 | soundcraft-ui24r-1 | 9301 | ws://localhost:9301 | ✅ |
| Lab-02 | soundcraft-ui24r-2 | 9302 | ❌ Není v simulátorech! | - |

**⚠️ KONFLIKT:** Port 9301 koliduje se Shelly Pro EM #1!

**API Příklady:**
```bash
# Mixer info
curl http://172.17.0.1:9301/api/info

# Kompletní stav
curl http://172.17.0.1:9301/api/state

# Upload show file
curl -X POST http://172.17.0.1:9301/api/shows/upload \
  -F "file=@band-setup.json"

# Load scene
curl -X POST http://172.17.0.1:9301/api/scenes/load/Band%201
```
```

**AKČNÍ BODY:**
1. ✅ Opravit port mapping v docker-compose.yml
2. ✅ Vyřešit konflikt Shelly vs. Mixer na portu 9301
3. ✅ Dokumentovat skutečné device mapping
4. ✅ Vytvořit startup script pro kompletní setup

### 2. Vylepšení Device Services - Oprava Base URL (PRIORITA 1)

#### A) Vytvořit BaseDeviceService s circuit breaker

```php
// app/Services/DeviceServices/BaseDeviceService.php
<?php

namespace App\Services\DeviceServices;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseDeviceService
{
    protected string $deviceId;
    protected string $baseUrl;
    protected int $timeout = 5;
    
    // Circuit Breaker config
    protected int $failureThreshold = 3;
    protected int $recoveryTimeout = 60; // seconds
    
    abstract protected function getServiceName(): string;
    
    /**
     * Konstruktor s automatickou detekcí hostu
     */
    public function __construct(string $deviceId, int $port)
    {
        $this->deviceId = $deviceId;
        
        // ✅ OPRAVA: Použít správný host pro Docker
        $host = config('devices.simulator_host', 'host.docker.internal');
        
        // Pokud je env SIMULATOR_HOST nastaveno, použít to
        if ($envHost = env('SIMULATOR_HOST')) {
            $host = $envHost;
        }
        
        $this->baseUrl = "http://{$host}:{$port}";
        
        Log::debug("[{$this->deviceId}] Initialized with base URL: {$this->baseUrl}");
    }
    
    /**
     * HTTP request s circuit breaker protection
     */
    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $cacheKey = "circuit_breaker:{$this->deviceId}";
        
        // Check circuit breaker state
        if ($this->isCircuitOpen($cacheKey)) {
            Log::warning("[{$this->deviceId}] Circuit breaker OPEN - device unavailable");
            return [
                'status' => 'unavailable',
                'message' => 'Device temporarily unavailable due to repeated failures'
            ];
        }
        
        try {
            $response = match($method) {
                'GET' => Http::timeout($this->timeout)->get($endpoint),
                'POST' => Http::timeout($this->timeout)->post($endpoint, $data),
                'PUT' => Http::timeout($this->timeout)->put($endpoint, $data),
                'DELETE' => Http::timeout($this->timeout)->delete($endpoint),
                default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}")
            };
            
            if ($response->successful()) {
                $this->recordSuccess($cacheKey);
                return $response->json() ?? ['status' => 'ok'];
            }
            
            $this->recordFailure($cacheKey);
            
            Log::warning("[{$this->deviceId}] HTTP {$response->status()}: {$response->body()}");
            
            return [
                'status' => 'error',
                'code' => $response->status(),
                'message' => $response->body()
            ];
            
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("[{$this->deviceId}] Connection failed: {$e->getMessage()}");
            $this->recordFailure($cacheKey);
            
            return [
                'status' => 'connection_error',
                'message' => "Failed to connect to device: {$e->getMessage()}"
            ];
            
        } catch (\Exception $e) {
            Log::error("[{$this->deviceId}] Request error: {$e->getMessage()}");
            $this->recordFailure($cacheKey);
            
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Check if circuit breaker is open
     */
    private function isCircuitOpen(string $key): bool
    {
        $failures = Cache::get("{$key}:failures", 0);
        $lastFailure = Cache::get("{$key}:last_failure");
        
        if ($failures >= $this->failureThreshold) {
            if ($lastFailure && (time() - $lastFailure) < $this->recoveryTimeout) {
                return true; // Circuit still open
            }
            
            // Recovery timeout passed - reset and try again
            Log::info("[{$this->deviceId}] Circuit breaker entering HALF-OPEN state");
            Cache::forget("{$key}:failures");
            Cache::forget("{$key}:last_failure");
        }
        
        return false;
    }
    
    /**
     * Record successful request
     */
    private function recordSuccess(string $key): void
    {
        if (Cache::has("{$key}:failures")) {
            Log::info("[{$this->deviceId}] Circuit breaker CLOSED - device recovered");
        }
        
        Cache::forget("{$key}:failures");
        Cache::forget("{$key}:last_failure");
    }
    
    /**
     * Record failed request
     */
    private function recordFailure(string $key): void
    {
        $failures = Cache::increment("{$key}:failures");
        Cache::put("{$key}:last_failure", time(), $this->recoveryTimeout * 2);
        
        if ($failures >= $this->failureThreshold) {
            Log::alert("[{$this->deviceId}] Circuit breaker OPENED after {$failures} consecutive failures");
        } else {
            Log::warning("[{$this->deviceId}] Failure {$failures}/{$this->failureThreshold}");
        }
    }
    
    /**
     * Health check endpoint
     */
    public function healthCheck(): array
    {
        $start = microtime(true);
        $result = $this->makeRequest('GET', "{$this->baseUrl}/device-info");
        $responseTime = round((microtime(true) - $start) * 1000); // ms
        
        return [
            'device_id' => $this->deviceId,
            'status' => $result['status'] === 'ok' ? 'online' : 'offline',
            'response_time_ms' => $responseTime,
            'base_url' => $this->baseUrl,
            'details' => $result
        ];
    }
    
    /**
     * Get diagnostic information
     */
    public function getDiagnostics(): array
    {
        return $this->makeRequest('GET', "{$this->baseUrl}/diagnostics");
    }
}
```

#### B) Refaktorovat QRReaderService

```php
// app/Services/DeviceServices/QRReaderService.php
<?php

namespace App\Services\DeviceServices;

class QRReaderService extends BaseDeviceService
{
    protected function getServiceName(): string
    {
        return 'QR Reader';
    }
    
    /**
     * Získat informace o zařízení
     */
    public function getDeviceInfo(): array
    {
        return $this->makeRequest('GET', "{$this->baseUrl}/device-info");
    }
    
    /**
     * Simulovat načtení QR kódu
     */
    public function simulateScan(string $code): array
    {
        Log::info("[{$this->deviceId}] Simulating QR scan: {$code}");
        
        return $this->makeRequest('POST', "{$this->baseUrl}/scan", [
            'code' => $code
        ]);
    }
    
    /**
     * Autorizovat přístup (callback z backendu)
     */
    public function authorize(string $scanId, bool $granted, int $unlockDuration = 5): array
    {
        Log::info("[{$this->deviceId}] Authorizing scan {$scanId}: " . ($granted ? 'GRANTED' : 'DENIED'));
        
        return $this->makeRequest('POST', "{$this->baseUrl}/authorize", [
            'scanId' => $scanId,
            'authorized' => $granted,
            'unlockDuration' => $unlockDuration
        ]);
    }
    
    /**
     * Ovládat LED
     */
    public function setLed(string $color, string $mode = 'solid', int $duration = 3000): array
    {
        return $this->makeRequest('POST', "{$this->baseUrl}/control/led", [
            'color' => $color,      // red, green, blue, yellow, cyan, magenta, white
            'mode' => $mode,        // solid, blink, pulse
            'duration' => $duration // milliseconds
        ]);
    }
    
    /**
     * Ovládat bzučák
     */
    public function setBuzzer(int $frequency = 2500, int $duration = 200): array
    {
        return $this->makeRequest('POST', "{$this->baseUrl}/control/buzzer", [
            'enabled' => true,
            'frequency' => $frequency, // Hz
            'duration' => $duration    // ms
        ]);
    }
    
    /**
     * Odemknout dveře
     */
    public function unlockDoor(int $duration = 5): array
    {
        return $this->makeRequest('POST', "{$this->baseUrl}/control/door", [
            'action' => 'unlock',
            'duration' => $duration * 1000 // convert to ms
        ]);
    }
}
```

#### C) Přidat config soubor pro devices

```php
// config/devices.php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Simulator Host
    |--------------------------------------------------------------------------
    |
    | Host where device simulators are running.
    | - Docker: use 'host.docker.internal' or bridge IP (172.17.0.1)
    | - Local: use 'localhost'
    |
    */
    'simulator_host' => env('SIMULATOR_HOST', 'host.docker.internal'),
    
    /*
    |--------------------------------------------------------------------------
    | Device Ports
    |--------------------------------------------------------------------------
    |
    | Base ports for each device type.
    | Device #1 = base_port, #2 = base_port+1, etc.
    |
    */
    'ports' => [
        'qr_reader' => env('QR_READER_BASE_PORT', 9101),
        'camera' => env('CAMERA_BASE_PORT', 9201),
        'shelly' => env('SHELLY_BASE_PORT', 9501), // WARNING: Simulators use 9301!
        'keypad' => env('KEYPAD_BASE_PORT', 9401),
        'mixer' => env('MIXER_BASE_PORT', 9301),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Circuit Breaker
    |--------------------------------------------------------------------------
    |
    | Configuration for circuit breaker pattern.
    |
    */
    'circuit_breaker' => [
        'failure_threshold' => env('DEVICE_FAILURE_THRESHOLD', 3),
        'recovery_timeout' => env('DEVICE_RECOVERY_TIMEOUT', 60), // seconds
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Timeouts
    |--------------------------------------------------------------------------
    */
    'timeout' => env('DEVICE_TIMEOUT', 5), // seconds
    'health_check_interval' => env('DEVICE_HEALTH_CHECK_INTERVAL', 60), // seconds
];
```

#### D) Aktualizovat .env

```bash
# .env

# Device Simulator Configuration
SIMULATOR_HOST=172.17.0.1

# Device Ports
QR_READER_BASE_PORT=9101
CAMERA_BASE_PORT=9201
SHELLY_BASE_PORT=9501  # NOTE: Actual simulators use 9301!
KEYPAD_BASE_PORT=9401
MIXER_BASE_PORT=9301

# Circuit Breaker
DEVICE_FAILURE_THRESHOLD=3
DEVICE_RECOVERY_TIMEOUT=60
DEVICE_TIMEOUT=5
```

### 3. Database migrace (PRIORITA 2)

#### A) Vytvořit shelly_logs tabulku

```php
// database/migrations/2025_11_22_000001_create_shelly_logs_table.php
Schema::create('shelly_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('device_id')->constrained()->onDelete('cascade');
    $table->foreignId('room_id')->nullable()->constrained()->onDelete('set null');
    $table->decimal('lights_power', 10, 3)->default(0);
    $table->decimal('lights_energy', 12, 6)->default(0);
    $table->decimal('outlets_power', 10, 3)->default(0);
    $table->decimal('outlets_energy', 12, 6)->default(0);
    $table->decimal('total_power', 10, 3)->default(0);
    $table->decimal('total_energy', 12, 6)->default(0);
    $table->decimal('voltage', 6, 2)->default(0);
    $table->decimal('cost', 10, 2)->default(0);  // Vypočtená cena
    $table->timestamp('measured_at');
    $table->timestamps();
    
    $table->index(['device_id', 'measured_at']);
    $table->index(['room_id', 'measured_at']);
});
```

#### B) Vytvořit device_health_checks tabulku

```php
// database/migrations/2025_11_22_000002_create_device_health_checks_table.php
Schema::create('device_health_checks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('device_id')->constrained()->onDelete('cascade');
    $table->enum('status', ['online', 'offline', 'error', 'degraded'])->default('offline');
    $table->integer('response_time_ms')->nullable();
    $table->json('diagnostics')->nullable();
    $table->text('error_message')->nullable();
    $table->timestamp('checked_at');
    $table->timestamps();
    
    $table->index(['device_id', 'checked_at']);
    $table->index('status');
});
```

### 4. Filament Admin Interface (PRIORITA 3)

#### A) Vytvořit DeviceResource

```bash
docker exec rehearsal-app php artisan make:filament-resource Device --generate
```

```php
// app/Filament/Resources/DeviceResource.php
public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('name')
                ->label('Název')
                ->searchable(),
            
            Tables\Columns\TextColumn::make('type')
                ->label('Typ')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'qr_reader' => 'primary',
                    'keypad' => 'info',
                    'camera' => 'success',
                    'shelly' => 'warning',
                    'mixer' => 'danger',
                    default => 'gray',
                }),
            
            Tables\Columns\TextColumn::make('room.name')
                ->label('Místnost')
                ->searchable(),
            
            Tables\Columns\TextColumn::make('ip')
                ->label('IP adresa')
                ->copyable(),
            
            Tables\Columns\IconColumn::make('is_online')
                ->label('Online')
                ->boolean()
                ->getStateUsing(fn ($record) => app(DeviceHealthService::class)->isOnline($record->id)),
        ])
        ->actions([
            Tables\Actions\Action::make('health_check')
                ->label('Health Check')
                ->icon('heroicon-o-heart')
                ->action(function (Device $record) {
                    $service = app(DeviceHealthService::class);
                    $result = $service->performHealthCheck($record);
                    
                    Notification::make()
                        ->title($result['status'] === 'online' ? 'Device je online' : 'Device je offline')
                        ->body("Response time: {$result['response_time_ms']}ms")
                        ->success()
                        ->send();
                }),
            
            Tables\Actions\EditAction::make(),
        ]);
}
```

#### B) Vytvořit Dashboard Widgets

```php
// app/Filament/Widgets/DeviceStatusOverview.php
class DeviceStatusOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $total = Device::count();
        $online = Device::whereHas('healthChecks', function($q) {
            $q->where('status', 'online')
              ->where('checked_at', '>=', now()->subMinutes(5));
        })->count();
        
        return [
            Stat::make('Celkem zařízení', $total)
                ->description('Všechna registrovaná zařízení')
                ->icon('heroicon-o-cpu-chip'),
            
            Stat::make('Online', $online)
                ->description(round(($online / max($total, 1)) * 100) . '% dostupnost')
                ->color('success')
                ->icon('heroicon-o-signal'),
            
            Stat::make('Offline', $total - $online)
                ->description('Nedostupná zařízení')
                ->color('danger')
                ->icon('heroicon-o-exclamation-triangle'),
        ];
    }
    
    protected function getPollingInterval(): ?string
    {
        return '30s';
    }
}
```

### 5. Testing Infrastructure (PRIORITA 3)

#### A) Vytvořit Device Service Tests

```php
// tests/Unit/Services/QRReaderServiceTest.php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\DeviceServices\QRReaderService;
use Illuminate\Support\Facades\Http;

class QRReaderServiceTest extends TestCase
{
    public function test_can_get_device_info()
    {
        Http::fake([
            'http://qr-reader-1:9101/device-info' => Http::response([
                'deviceId' => 'qr-reader-1',
                'model' => 'Entry E QR R1',
                'firmware' => '2.4.1'
            ], 200)
        ]);
        
        $service = new QRReaderService('qr-reader-1', 9101);
        $info = $service->getDeviceInfo();
        
        $this->assertEquals('qr-reader-1', $info['deviceId']);
        $this->assertEquals('Entry E QR R1', $info['model']);
    }
    
    public function test_circuit_breaker_opens_after_failures()
    {
        Http::fake([
            '*' => Http::response([], 500)
        ]);
        
        $service = new QRReaderService('qr-reader-1', 9101);
        
        // Simulovat 3 selhání
        for ($i = 0; $i < 3; $i++) {
            $service->getDeviceInfo();
        }
        
        // Circuit breaker by měl být otevřený
        $result = $service->getDeviceInfo();
        $this->assertEquals('unavailable', $result['status']);
    }
}
```

#### B) Integration test s mock simulátory

```php
// tests/Feature/DeviceIntegrationTest.php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Device;
use App\Models\Room;
use Illuminate\Support\Facades\Http;

class DeviceIntegrationTest extends TestCase
{
    public function test_qr_scan_webhook_authorizes_access()
    {
        $room = Room::factory()->create();
        $device = Device::factory()->create([
            'type' => 'qr_reader',
            'room_id' => $room->id
        ]);
        
        $reservation = Reservation::factory()->create([
            'room_id' => $room->id,
            'start_at' => now()->subMinutes(5),
            'end_at' => now()->addHour()
        ]);
        
        $code = "RESERVATION_{$reservation->id}_{$reservation->user_id}_{$room->id}";
        
        $response = $this->postJson('/api/webhooks/qr-scan', [
            'code' => $code,
            'deviceId' => $device->meta['name'],
            'scanId' => 'test_scan_123',
            'timestamp' => now()->toIso8601String()
        ]);
        
        $response->assertOk()
                 ->assertJson(['granted' => true]);
    }
}
```

### 6. Startovací skripty (PRIORITA 1)

#### A) Vytvoř