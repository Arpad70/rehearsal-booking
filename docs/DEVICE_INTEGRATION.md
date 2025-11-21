# Device Integration - Dokumentace

## Přehled

Tato aplikace integruje 5 typů simulovaných IoT zařízení z Docker kontejnerů:

1. **QR čtečky** (Entry E QR R1) - přístupová kontrola
2. **RFID Keypady** (RFID Keypad 7612) - přístupová kontrola s PIN
3. **IP Kamery** (EVOLVEO Detective POE8 SMART) - monitoring a záznam
4. **Shelly Pro EM** - měření spotřeby energie
5. **Soundcraft Ui24R Mixer** - správa mixážních scén pro kapely

## Architektura

```
┌──────────────────────────────────────────────────────────────┐
│                    Laravel Backend                            │
│  ┌────────────────────────────────────────────────────────┐  │
│  │  Device Services Layer                                 │  │
│  │  - QRReaderService                                    │  │
│  │  - KeypadService                                      │  │
│  │  - CameraService                                      │  │
│  │  - ShellyService                                      │  │
│  │  - MixerService                                       │  │
│  └────────────────────────────────────────────────────────┘  │
│  ┌────────────────────────────────────────────────────────┐  │
│  │  Business Logic                                        │  │
│  │  - AccessControlService (QR/RFID autorizace)         │  │
│  │  - DeviceWebhookController (scan events)             │  │
│  └────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────┘
                         ▲  │
                    HTTP │  │ WebSocket
                         │  ▼
┌──────────────────────────────────────────────────────────────┐
│            Docker Simulace Network                            │
│  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌─────────┐        │
│  │QR-1     │  │Keypad-1 │  │Camera-1 │  │Shelly-1 │        │
│  │:9101    │  │:9401    │  │:9201    │  │:9501    │        │
│  └─────────┘  └─────────┘  └─────────┘  └─────────┘        │
│       ...          ...          ...          ...             │
│  ┌─────────┐  ┌─────────┐                                   │
│  │Mixer-1  │  │Gateway  │                                   │
│  │:9301    │  │:9000    │                                   │
│  └─────────┘  └─────────┘                                   │
└──────────────────────────────────────────────────────────────┘
```

## Mapování zařízení na místnosti

| Místnost | QR Port | Keypad Port | Camera Port | Shelly Port | Mixer Port |
|----------|---------|-------------|-------------|-------------|------------|
| Lab-01   | 9101    | 9401        | 9201        | 9501        | 9301       |
| Lab-02   | 9102    | 9402        | 9202        | 9502        | 9302       |
| Lab-03   | 9103    | 9403        | 9203        | 9503        | -          |
| Lab-04   | 9104    | 9404        | 9204        | 9504        | -          |
| Lab-05   | 9105    | 9405        | 9205        | 9505        | -          |
| Lab-06   | 9106    | 9406        | 9206        | 9506        | -          |

## API Endpointy

### Webhook Endpoints (od zařízení → Laravel)

**POST /api/webhooks/qr-scan**
```json
{
  "code": "RESERVATION_123_45_2",
  "deviceId": "qr-reader-1",
  "scanId": "abc123",
  "timestamp": "2025-11-21T10:30:00Z"
}
```

**POST /api/webhooks/rfid-scan**
```json
{
  "cardId": "12:34:56:78",
  "deviceId": "keypad-1",
  "scanId": "def456",
  "timestamp": "2025-11-21T10:30:00Z"
}
```

**POST /api/webhooks/motion-detected**
```json
{
  "deviceId": "camera-1",
  "timestamp": "2025-11-21T10:30:00Z",
  "confidence": 0.95
}
```

**POST /api/webhooks/power-update**
```json
{
  "deviceId": "shelly-pro-em-1",
  "timestamp": "2025-11-21T10:30:00Z",
  "lights": {
    "power": 125.3,
    "voltage": 230.0,
    "current": 0.545,
    "total": 2.456
  },
  "outlets": {
    "power": 149.5,
    "voltage": 230.0,
    "current": 0.65,
    "total": 3.678
  }
}
```

## Workflow: Přístup uživatele

### 1. QR kód scan
```
User → Scan QR → QR Reader → POST /api/webhooks/qr-scan
                  ↓
       AccessControlService.authorizeQRAccess()
                  ↓
       Check reservation validity (15 min buffer)
                  ↓
       ✅ Granted: unlock door, turn on lights, start recording
       ❌ Denied: red LED, error beep, log attempt
```

### 2. RFID karta + PIN
```
User → Card + PIN → Keypad → POST /api/webhooks/pin-entry
                     ↓
        AccessControlService.authorizeRFIDAccess()
                     ↓
        Find user by RFID, verify PIN
                     ↓
        Check active reservation
                     ↓
        ✅ Granted: unlock door, activate room
        ❌ Denied: red LED, error beep
```

## Konfigurace

### Environment Variables

```env
# Docker network
SIMULATOR_NETWORK=simulator-network

# Device ports
QR_READER_BASE_PORT=9101
KEYPAD_BASE_PORT=9401
CAMERA_BASE_PORT=9201
SHELLY_BASE_PORT=9501
MIXER_BASE_PORT=9301

# Power monitoring
ELECTRICITY_PRICE_PER_KWH=5.5
```

### Database

Migrace přidávají:
- `rooms`: device_id fieldy (qr_reader_device_id, keypad_device_id, atd.)
- `users`: rfid_card_id, pin_hash, band_name, mixer_preferences
- `shelly_logs`: power consumption data

## Device Services

### QRReaderService

```php
$service = new QRReaderService('qr-reader-1', 9101);

// Kontrola
$info = $service->getDeviceInfo();
$diagnostics = $service->getDiagnostics();

// Ovládání
$service->unlockDoor(5000); // 5 sec
$service->setLed('green', 'solid', 3000);
$service->setBuzzer('success');

// Historie
$history = $service->getAccessLog(50);
```

### ShellyService

```php
$service = new ShellyService('shelly-pro-em-1', 9501);

// Ovládání světel (Kanál 0)
$service->turnLightsOn();
$service->turnLightsOff();
$service->toggleLights();

// Měření spotřeby
$status = $service->getTotalPower();
/*
[
  'lights' => ['power' => 125.3, 'energy' => 2.456],
  'outlets' => ['power' => 149.5, 'energy' => 3.678],
  'total_power' => 274.8,
  'total_energy' => 6.134
]
*/

// Výpočet nákladů
$cost = $service->calculateCost(6.134, 5.5); // 33.74 Kč
```

### MixerService

```php
$service = new MixerService('soundcraft-ui24r-1', 9301);

// Upload show file pro kapelu
$showPath = $service->createShowFileFromReservation($reservationData);
$service->uploadShow($showPath);

// Načíst show a první scénu
$service->loadShow('Rock Band XYZ', loadFirstScene: true);

// Přepnout scénu
$service->loadScene('Song 2 - Heavy Drums');

// Zakázat přímý přístup (pouze backend)
$service->disableWebAccess();
```

### CameraService

```php
$service = new CameraService('camera-1', 9201);

// Snapshot
$image = $service->getSnapshot(1920, 1080);
file_put_contents('snapshot.jpg', $image);

// RTSP stream
$rtsp = $service->getRtspInfo();
// rtsp://localhost:8554/camera-1/main

// Recording
$service->startRecording();
$service->stopRecording();

// Motion detection
$service->setMotionDetection(enabled: true, sensitivity: 75);
```

## WebSocket Real-time Updates

Každé zařízení poskytuje WebSocket endpoint pro real-time události:

```javascript
// QR Reader
const ws = new WebSocket('ws://localhost:9101');
ws.onmessage = (event) => {
  const data = JSON.parse(event.data);
  if (data.event === 'qr_scan') {
    console.log('Scan:', data.code);
  }
};

// Camera
const ws = new WebSocket('ws://localhost:9201');
ws.onmessage = (event) => {
  const data = JSON.parse(event.data);
  if (data.event === 'motion_detected') {
    console.log('Motion at:', data.timestamp);
  }
};

// Shelly
const ws = new WebSocket('ws://localhost:9501');
ws.onmessage = (event) => {
  const data = JSON.parse(event.data);
  if (data.event === 'power_update') {
    console.log('Power:', data.power, 'W');
  }
};
```

## Testování

### Manuální test QR přístupu

```bash
# Simulovat QR scan
curl -X POST http://localhost:9101/scan \
  -H "Content-Type: application/json" \
  -d '{"code":"RESERVATION_1_2_1"}'

# Webhook se zavolá automaticky do Laravel
# Laravel vrátí authorized/denied response
```

### Test RFID

```bash
curl -X POST http://localhost:9401/rfid-scan \
  -H "Content-Type: application/json" \
  -d '{"cardId":"12:34:56:78"}'
```

### Test power monitoring

```bash
# Zapnout světla
curl "http://localhost:9501/relay/0?turn=on"

# Zjistit spotřebu
curl http://localhost:9501/status | jq '.em1'
```

## Troubleshooting

### Zařízení neodpovídá
```bash
# Check Docker container
docker ps | grep qr-reader-1

# Check logs
docker logs qr-reader-1

# Restart
docker restart qr-reader-1
```

### WebSocket nefunguje
- Zkontrolovat firewall: porty 9101-9506 musí být otevřené
- Zkontrolovat Laravel logs: `tail -f storage/logs/laravel.log`

### Autorizace selhává
- Zkontrolovat časové okno rezervace (±15 min buffer)
- Ověřit QR formát: `RESERVATION_{id}_{user_id}_{room_id}`
- Zkontrolovat RFID kartu v databázi: `SELECT * FROM users WHERE rfid_card_id = '12:34:56:78'`

## 🚀 Deployment

### Docker Network Setup (První spuštění)

Před prvním spuštěním je nutné vytvořit externí Docker network pro komunikaci se simulátory:

```bash
# Vytvoření external network pro device simulátory
docker network create simulator-network

# Ověření existence network
docker network ls | grep simulator-network

# Inspekce network (volitelné)
docker network inspect simulator-network
```

**Poznámka**: Tento krok je potřeba provést pouze jednou. Network zůstává persistentní i po `docker-compose down`.

### Docker Deployment (Development/Local)

1. **Build a spuštění kontejnerů**:
   ```bash
   # První build (s clean cache)
   docker-compose build --no-cache
   
   # Spuštění všech služeb na pozadí
   docker-compose up -d
   
   # Ověření běžících kontejnerů
   docker ps
   
   # Sledování logů
   docker-compose logs -f app
   ```

2. **Ověření network connectivity** (důležité pro device integration):
   ```bash
   # Test připojení k QR Reader simulátoru
   docker exec rehearsal-app curl http://qr-reader-1:9101/device-info
   
   # Test připojení ke kameře
   docker exec rehearsal-app curl http://camera-1:9201/status
   
   # Test připojení k Shelly power monitoru
   docker exec rehearsal-app curl http://shelly-pro-em-1:9501/status
   
   # Inspekce simulator-network (ověření členů)
   docker network inspect simulator-network | grep -A 5 rehearsal-app
   ```

3. **Spuštění migrací uvnitř kontejneru**:
   ```bash
   docker exec rehearsal-app php artisan migrate --force
   ```

4. **Optimalizace cache**:
   ```bash
   docker exec rehearsal-app php artisan config:cache
   docker exec rehearsal-app php artisan route:cache
   docker exec rehearsal-app php artisan view:cache
   docker exec rehearsal-app php artisan filament:optimize
   ```

5. **Restart queue workers**:
   ```bash
   docker exec rehearsal-app php artisan queue:restart
   ```

### Production Deployment (Railway/Cloud)

1. **Nastavení Environment Variables v Railway**:
   ```bash
   # Device Integration
   QR_READER_BASE_PORT=9101
   KEYPAD_BASE_PORT=9401
   CAMERA_BASE_PORT=9201
   SHELLY_BASE_PORT=9501
   MIXER_BASE_PORT=9301
   SIMULATOR_HOST=<public-ip-or-domain>
   
   # Power Monitoring
   ELECTRICITY_PRICE_PER_KWH=5.5
   
   # Access Control
   ACCESS_CONTROL_TIME_BUFFER_MINUTES=15
   ACCESS_CONTROL_AUTO_UNLOCK_DURATION=5000
   
   # Camera Settings
   CAMERA_RTSP_ENABLED=true
   CAMERA_AUTO_START_RECORDING=true
   CAMERA_MOTION_DETECTION_ENABLED=true
   ```

2. **GitHub Secrets (pro automatický Railway deploy)**:
   - `RAILWAY_TOKEN`: Z Railway account settings
   - `RAILWAY_SERVICE_ID`: Z Railway project settings
   - `SIMULATOR_HOST`: Veřejná IP nebo doména simulátorové sítě

3. **Spuštění Queue Workers** (na production serveru):
   ```bash
   php artisan queue:work --queue=devices,default --tries=3 --daemon
   ```

4. **Migrace databáze**:
   ```bash
   php artisan migrate --force
   ```

5. **Cache optimalizace**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan filament:optimize
   ```

### GitHub Actions Workflow

Automatický deploy na Railway při push do `main` branch:

- **Workflow**: `.github/workflows/railway-deploy.yml`
- **Trigger**: Push to main/master
- **Kroky**: Build → Deploy → Migrate → Optimize → Queue Restart
- **Environment**: Automaticky nastaví všechny device integration env variables

## TODO: Budoucí vylepšení

- [ ] Livewire komponenta pro real-time room status
- [ ] Grafana dashboard pro power monitoring
- [ ] Automatické nahrávání mixážních scén do databáze
- [ ] Email notifikace při neautorizovaném přístupu
- [ ] Mobile app pro správu přístupu

