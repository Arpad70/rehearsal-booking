# Device Mapping - Skutečné porty a konfigurace

> **Poslední aktualizace:** 21. listopadu 2025  
> **Zdroj:** Analýza simulator dokumentace + skutečná implementace

---

## 🌐 Simulator Base URL

### Z Docker kontejnerů (rehearsal-app)
```
http://172.17.0.1:{PORT}
```

### Z host systému
```
http://localhost:{PORT}
```

### WebSocket
```
ws://localhost:{PORT}  # Z hostu
ws://172.17.0.1:{PORT}  # Z Docker kontejnerů
```

---

## 📱 QR Čtečky (Entry E QR R1)

| Místnost | Device ID | Port | WebSocket | Model | Firmware |
|----------|-----------|------|-----------|-------|----------|
| Lab-01 | qr-reader-1 | 9101 | ws://localhost:9101 | Entry E QR R1 | v3.2.1 |
| Lab-02 | qr-reader-2 | 9102 | ws://localhost:9102 | Entry E QR R1 | v3.2.1 |
| Lab-03 | qr-reader-3 | 9103 | ws://localhost:9103 | Entry E QR R1 | v3.2.1 |
| Lab-04 | qr-reader-4 | 9104 | ws://localhost:9104 | Entry E QR R1 | v3.2.1 |
| Lab-05 | qr-reader-5 | 9105 | ws://localhost:9105 | Entry E QR R1 | v3.2.1 |
| Lab-06 | qr-reader-6 | 9106 | ws://localhost:9106 | Entry E QR R1 | v3.2.1 |

### Podporované funkce
- ✅ QR kód scanning
- ✅ Webhook notifikace do backendu
- ✅ LED ovládání (red, green, blue, yellow, cyan, magenta, white)
- ✅ Bzučák ovládání
- ✅ Relé (zámek dveří)
- ✅ WebSocket real-time events
- ✅ Heartbeat (každých 10s)

### API Příklady

```bash
# Device info
curl http://172.17.0.1:9101/device-info | jq

# Diagnostika
curl http://172.17.0.1:9101/diagnostics | jq

# Simulovat scan
curl -X POST http://172.17.0.1:9101/scan \
  -H "Content-Type: application/json" \
  -d '{"code":"RESERVATION_123"}'

# Autorizovat přístup (backend response)
curl -X POST http://172.17.0.1:9101/authorize \
  -H "Content-Type: application/json" \
  -d '{"scanId":"scan_123","authorized":true,"unlockDuration":5}'

# Ovládání LED
curl -X POST http://172.17.0.1:9101/control/led \
  -H "Content-Type: application/json" \
  -d '{"color":"green","mode":"solid","duration":3000}'
```

### PHP Service Usage

```php
use App\Services\DeviceServices\QRReaderService;

$service = new QRReaderService('qr-reader-1', 9101);

// Device info
$info = $service->getDeviceInfo();

// Simulate scan
$result = $service->simulateScan('RESERVATION_123');

// Authorize
$result = $service->authorize('scan_123', true, 'Access granted', 5);

// Control LED
$result = $service->setLed('green', 'solid', 3000);
```

---

## 📹 IP Kamery (EVOLVEO Detective POE8 SMART)

**⚠️ POZNÁMKA:** Dokumentace uvádí 6 kamer (9201-9206), ale simulátory mají **12 kamer** (9201-9212)!

| Místnost | Kamery | Porty | RTSP Stream | Resolution |
|----------|--------|-------|-------------|------------|
| Lab-01 | camera-1, camera-2 | 9201-9202 | rtsp://localhost:9201/stream1 | 8MP (3840×2160) |
| Lab-02 | camera-3, camera-4 | 9203-9204 | rtsp://localhost:9203/stream1 | 8MP (3840×2160) |
| Lab-03 | camera-5, camera-6 | 9205-9206 | rtsp://localhost:9205/stream1 | 8MP (3840×2160) |
| Lab-04 | camera-7, camera-8 | 9207-9208 | rtsp://localhost:9207/stream1 | 8MP (3840×2160) |
| Lab-05 | camera-9, camera-10 | 9209-9210 | rtsp://localhost:9209/stream1 | 8MP (3840×2160) |
| Lab-06 | camera-11, camera-12 | 9211-9212 | rtsp://localhost:9211/stream1 | 8MP (3840×2160) |

### Podporované funkce
- ✅ MJPEG stream (HTTP)
- ✅ RTSP stream (Real-Time Streaming Protocol)
- ✅ Snapshot (JPEG)
- ✅ Motion detection
- ✅ Webhook pro motion events
- ✅ Pan/Tilt/Zoom (PTZ)
- ✅ Recording management
- ✅ Analytics (people counting, line crossing, intrusion)

### API Příklady

```bash
# Device info
curl http://172.17.0.1:9201/device-info | jq

# Snapshot
curl http://172.17.0.1:9201/snapshot --output snapshot.jpg

# MJPEG stream (živý náhled)
curl http://172.17.0.1:9201/stream

# Status
curl http://172.17.0.1:9201/status | jq

# Start recording
curl -X POST http://172.17.0.1:9201/recording/start \
  -H "Content-Type: application/json" \
  -d '{"duration":3600}'
```

### RTSP Stream Usage

```bash
# VLC player
vlc rtsp://localhost:9201/stream1

# FFmpeg
ffmpeg -i rtsp://localhost:9201/stream1 -c copy output.mp4
```

---

## 🔌 Shelly Pro EM (Power Monitoring)

**⚠️ KRITICKÁ OPRAVA:** Simulátory používají porty **9301-9306**, NE 9501-9506 jak uvádí dokumentace!

**⚠️ PORT KONFLIKT:** Port 9301 je používán mixerem i Shelly #1!

| Místnost | Device ID | Port (Simulátor) | Port (Docs) | Kanál 0 | Kanál 1 |
|----------|-----------|------------------|-------------|---------|---------|
| Lab-01 | shelly-pro-em-1 | **9301** ❌ | 9501 | Světla (relé) | Zásuvky (monitoring) |
| Lab-02 | shelly-pro-em-2 | **9302** ❌ | 9502 | Světla (relé) | Zásuvky (monitoring) |
| Lab-03 | shelly-pro-em-3 | **9303** ❌ | 9503 | Světla (relé) | Zásuvky (monitoring) |
| Lab-04 | shelly-pro-em-4 | **9304** ❌ | 9504 | Světla (relé) | Zásuvky (monitoring) |
| Lab-05 | shelly-pro-em-5 | **9305** ❌ | 9505 | Světla (relé) | Zásuvky (monitoring) |
| Lab-06 | shelly-pro-em-6 | **9306** ❌ | 9506 | Světla (relé) | Zásuvky (monitoring) |

### Doporučené řešení konfliktu
1. **Varianta A**: Přesunout Shelly na porty 9501-9506 (dle dokumentace) ✅
2. **Varianta B**: Přesunout Mixer na port 9800
3. **Varianta C**: Použít pouze jeden Shelly (#1) na portu 9301 pro testování

### Podporované funkce
- ✅ Dvoukanálové měření spotřeby (EM1:0, EM1:1)
- ✅ Relé ovládání (pouze kanál 0 - světla)
- ✅ Real-time power monitoring
- ✅ Voltage, current, power factor měření
- ✅ Gen2 RPC API
- ✅ Energy cost calculation

### API Příklady

```bash
# Status všech kanálů
curl http://172.17.0.1:9301/status | jq

# Zapnout světla (kanál 0)
curl "http://172.17.0.1:9301/relay/0?turn=on"

# Vypnout světla
curl "http://172.17.0.1:9301/relay/0?turn=off"

# Zapnout s časovačem (30s)
curl "http://172.17.0.1:9301/relay/0?turn=on&timer=30"

# Měření spotřeby (Gen2 RPC API) - Kanál 0 (Světla)
curl "http://172.17.0.1:9301/rpc/Switch.GetStatus?id=0" | jq

# Měření spotřeby - Kanál 1 (Zásuvky)
curl "http://172.17.0.1:9301/rpc/EM1.GetStatus?id=1" | jq
```

### PHP Service Usage

```php
use App\Services\DeviceServices\ShellyService;

$service = new ShellyService('shelly-pro-em-1', 9301);

// Status
$status = $service->getStatus();

// Zapnout světla
$result = $service->turnLightsOn();

// Vypnout světla
$result = $service->turnLightsOff();

// Získat spotřebu
$power = $service->getTotalPower();
// Returns: ['lights' => [...], 'outlets' => [...], 'total_power' => 150.5]

// Vypočítat náklady
$cost = $service->calculateCost(12.5, 5.5); // 12.5 kWh @ 5.5 Kč/kWh = 68.75 Kč
```

---

## 🔢 RFID Keypady (RFID Keypad 7612)

**⚠️ POZNÁMKA:** Dokumentace uvádí 6 keypadů (9401-9406), ale simulátory mají pouze **2 keypady** (9401-9402)!

| Místnost | Device ID | Port | WebSocket | Model | Firmware |
|----------|-----------|------|-----------|-------|----------|
| Lab-01 | keypad-1 | 9401 | ws://localhost:9401 | RFID Keypad 7612 | v4.1.2 |
| Lab-02 | keypad-2 | 9402 | ws://localhost:9402 | RFID Keypad 7612 | v4.1.2 |

### Podporované funkce
- ✅ RFID card reading (125kHz, EM4100/EM4102/TK4100)
- ✅ PIN keypad (4-8 digit PIN)
- ✅ Kombinovaná autentizace (RFID + PIN)
- ✅ LED indikace (RGB)
- ✅ Bzučák
- ✅ Relé (zámek dveří)
- ✅ Wiegand output
- ✅ WebSocket real-time events
- ✅ Heartbeat (každých 10s)

### API Příklady

```bash
# Device info
curl http://172.17.0.1:9401/device-info | jq

# RFID scan
curl -X POST http://172.17.0.1:9401/rfid-scan \
  -H "Content-Type: application/json" \
  -d '{"cardId":"1234567890"}'

# PIN entry
curl -X POST http://172.17.0.1:9401/pin-entry \
  -H "Content-Type: application/json" \
  -d '{"pin":"1234"}'

# Combined (RFID + PIN)
curl -X POST http://172.17.0.1:9401/verify \
  -H "Content-Type: application/json" \
  -d '{"cardId":"1234567890","pin":"1234"}'
```

---

## 🎵 Soundcraft Ui24R Mixer

**⚠️ POZNÁMKA:** Dokumentace uvádí 2 mixery (Lab-01, Lab-02), ale simulátory mají pouze **1 mixer** (Lab-01)!

**⚠️ PORT KONFLIKT:** Port 9301 koliduje se Shelly Pro EM #1!

| Místnost | Device ID | Port | WebSocket | Show Files | Status |
|----------|-----------|------|-----------|------------|--------|
| Lab-01 | soundcraft-ui24r-1 | 9301 | ws://localhost:9301 | ✅ | ✅ Běží |
| Lab-02 | soundcraft-ui24r-2 | 9302 | ❌ | - | ❌ Není v simulátorech |

### Doporučení
- Přesunout mixer na port **9800** pro vyřešení konfliktu
- Nebo vytvořit druhý mixer pro Lab-02

### Podporované funkce
- ✅ 24 kanálů (10x XLR/TRS combo, 10x XLR, 2x line, 2x USB)
- ✅ 4-band parametrický EQ
- ✅ Kompresory, gates
- ✅ 4x FX procesory (2x reverb, delay, chorus)
- ✅ 10x Aux mixy
- ✅ Scene management (Cue Recall)
- ✅ Show file upload/download
- ✅ WebSocket real-time updates
- ✅ Security (password, backend-only mode)

### API Příklady

```bash
# Mixer info
curl http://172.17.0.1:9301/api/info | jq

# Kompletní stav
curl http://172.17.0.1:9301/api/state | jq

# Seznam scén
curl http://172.17.0.1:9301/api/scenes | jq

# Load scénu
curl -X POST http://172.17.0.1:9301/api/scenes/load/Band%201

# Upload show file
curl -X POST http://172.17.0.1:9301/api/shows/upload \
  -F "file=@band-setup.json"

# Seznam kanálů
curl http://172.17.0.1:9301/api/channels | jq

# Nastavení kanálu
curl -X POST http://172.17.0.1:9301/api/channel/1 \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Vocal 1",
    "fader": 0.8,
    "pan": 0.5,
    "mute": false
  }'
```

---

## 🔧 Konfigurace v Laravel

### Environment Variables (.env)

```env
# Simulator Host
SIMULATOR_HOST=172.17.0.1

# Device Ports
QR_READER_BASE_PORT=9101
CAMERA_BASE_PORT=9201
SHELLY_BASE_PORT=9301  # WARNING: Conflicts with mixer!
KEYPAD_BASE_PORT=9401
MIXER_BASE_PORT=9800   # Moved to avoid conflict

# Circuit Breaker
DEVICE_FAILURE_THRESHOLD=3
DEVICE_RECOVERY_TIMEOUT=60
DEVICE_TIMEOUT=5

# Health Check
DEVICE_HEALTH_CHECK_INTERVAL=60

# Power Monitoring
ELECTRICITY_PRICE_PER_KWH=5.5
```

### Config (config/devices.php)

```php
return [
    'simulator_host' => env('SIMULATOR_HOST', '172.17.0.1'),
    
    'ports' => [
        'qr_reader' => env('QR_READER_BASE_PORT', 9101),
        'camera' => env('CAMERA_BASE_PORT', 9201),
        'shelly' => env('SHELLY_BASE_PORT', 9301),
        'keypad' => env('KEYPAD_BASE_PORT', 9401),
        'mixer' => env('MIXER_BASE_PORT', 9800),
    ],
];
```

---

## 🧪 Testing

### Manual Test Scripts

```bash
# Test všech zařízení
./scripts/test-device-integration.sh

# Health check command
docker exec rehearsal-app php artisan devices:health-check

# Health check konkrétního typu
docker exec rehearsal-app php artisan devices:health-check --type=qr_reader
```

### Test z Dockeru

```bash
# QR Reader
docker exec rehearsal-app curl -s http://172.17.0.1:9101/device-info | jq

# Shelly
docker exec rehearsal-app curl -s http://172.17.0.1:9301/status | jq

# Camera
docker exec rehearsal-app curl -s http://172.17.0.1:9201/snapshot --output /tmp/snapshot.jpg
```

---

## 📊 WebSocket Events

### QR Reader Events

```json
// Heartbeat (každých 10s)
{
  "type": "heartbeat",
  "deviceId": "qr-reader-1",
  "status": "online",
  "uptime": 3600,
  "temperature": 38.5,
  "timestamp": "2025-11-21T17:00:00.000Z"
}

// QR Scan
{
  "type": "qr_scan",
  "deviceId": "qr-reader-1",
  "code": "RESERVATION_123",
  "scanId": "scan_abc123",
  "timestamp": "2025-11-21T17:00:00.000Z"
}

// Door Unlock
{
  "type": "door_unlock",
  "deviceId": "qr-reader-1",
  "duration": 5000,
  "timestamp": "2025-11-21T17:00:00.000Z"
}
```

### Keypad Events

```json
// RFID Scan
{
  "type": "rfid_scan",
  "deviceId": "keypad-1",
  "cardId": "1234567890",
  "timestamp": "2025-11-21T17:00:00.000Z"
}

// PIN Entry
{
  "type": "pin_entry",
  "deviceId": "keypad-1",
  "pin": "****",
  "timestamp": "2025-11-21T17:00:00.000Z"
}
```

### Mixer Events

```json
// Channel Updated
{
  "type": "channel_updated",
  "deviceId": "soundcraft-ui24r-1",
  "channel": 1,
  "changes": {"fader": 0.8, "mute": false},
  "timestamp": "2025-11-21T17:00:00.000Z"
}

// Scene Loaded
{
  "type": "scene_loaded",
  "deviceId": "soundcraft-ui24r-1",
  "scene": "Band 1 - Rock Setup",
  "timestamp": "2025-11-21T17:00:00.000Z"
}
```

---

## ⚠️ Known Issues

1. **Port Conflict (9301)**
   - Shelly #1 a Mixer #1 sdílejí port 9301
   - Řešení: Přesunout mixer na port 9800

2. **Device Count Mismatch**
   - Docs: 6 kamer → Reality: 12 kamer ✅
   - Docs: 6 keypadů → Reality: 2 keypady ❌
   - Docs: 2 mixery → Reality: 1 mixer ❌

3. **Missing Devices**
   - Lab-02 mixer není v simulátorech
   - Keypady 3-6 nejsou v simulátorech

4. **WebSocket Not Implemented**
   - Laravel aplikace neposlouchá WebSocket eventy
   - Pouze HTTP polling implementováno

---

## 📚 Dokumentace

- [DEVICE_INTEGRATION.md](./DEVICE_INTEGRATION.md) - Původní dokumentace
- [DEVICE_INTEGRATION_ANALYSIS.md](./DEVICE_INTEGRATION_ANALYSIS.md) - Kompletní analýza a doporučení
- [simulators/](./simulators/) - Dokumentace jednotlivých simulátorů
- [README-MULTI.md](./simulators/README-MULTI.md) - Multi-device setup
