# Entry E QR R1 - API Dokumentace (TCP/IP)

## 📡 Komunikační protokol

**Typ:** TCP/IP  
**Rozhraní:** HTTP REST API + WebSocket  
**Port:** 9101-9106 (6 čteček)  
**Formát:** JSON

---

## 🔧 API Endpointy

### 1. Device Information

#### GET /device-info
Získání informací o zařízení.

```bash
curl http://localhost:9101/device-info | jq
```

**Response:**
```json
{
  "status": "ok",
  "device": {
    "model": "Entry E QR R1",
    "firmware": "v3.2.1",
    "serialNumber": "qr-reader-1",
    "macAddress": "AA:BB:CC:DD:EE:01",
    "hardwareVersion": "Rev 2.0"
  },
  "interfaces": {
    "ethernet": {
      "enabled": true,
      "ip": "192.168.1.101",
      "mac": "AA:BB:CC:DD:EE:01",
      "dhcp": false,
      "port": 3000
    },
    "wiegand": {
      "enabled": false,
      "format": 26,
      "facilityCode": 1
    }
  },
  "supportedFormats": ["QR", "EAN13", "EAN8", "CODE128", "CODE39", "ITF"],
  "specifications": {
    "readDistance": "15 cm",
    "readTime": "80 ms",
    "minQRSize": "20 mm",
    "maxQRSize": "100 mm",
    "protection": "IP65"
  }
}
```

---

### 2. Diagnostics

#### GET /diagnostics
Diagnostické informace a I/O stavy.

```bash
curl http://localhost:9101/diagnostics | jq
```

**Response:**
```json
{
  "status": "ok",
  "diagnostics": {
    "temperature": "38.7 °C",
    "voltage": "12.3 V",
    "uptime": 1234,
    "errorCount": 0,
    "lastError": null,
    "totalScans": 45,
    "successfulScans": 43,
    "failedScans": 2,
    "successRate": "95.6%"
  },
  "io": {
    "led": {
      "red": false,
      "green": true,
      "blue": false,
      "mode": "solid",
      "brightness": 100
    },
    "relay": {
      "state": false,
      "activeTime": "N/A"
    },
    "buzzer": {
      "enabled": false,
      "frequency": 2500,
      "duration": 0,
      "pattern": "off"
    },
    "tamper": {
      "triggered": false,
      "lastEvent": null
    }
  }
}
```

---

### 3. QR Code Scanning

#### POST /scan
Načtení QR kódu (čtečka → backend).

```bash
curl -X POST http://localhost:9101/scan \
  -H "Content-Type: application/json" \
  -d '{"code":"ACCESS_TOKEN_USER001"}' | jq
```

**Request Body:**
```json
{
  "code": "ACCESS_TOKEN_USER001"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "QR kód načten, čeká se na autorizaci z backendu",
  "scan": {
    "code": "ACCESS_TOKEN_USER001",
    "type": "QR Code",
    "timestamp": "2025-11-20T22:00:00.000Z",
    "scanId": "abc123xyz",
    "deviceId": "qr-reader-1"
  },
  "device": {
    "ledGreen": false,
    "ledRed": false,
    "outputPin4": false,
    "doorLocked": true
  }
}
```

**WebSocket Event (broadcast):**
```json
{
  "type": "qr_scan",
  "deviceId": "qr-reader-1",
  "scan": {
    "code": "ACCESS_TOKEN_USER001",
    "scanId": "abc123xyz",
    "timestamp": "2025-11-20T22:00:00.000Z"
  },
  "waitingForAuthorization": true
}
```

---

### 4. Authorization

#### POST /authorize
Autorizace z backendu (backend → čtečka).

```bash
curl -X POST http://localhost:9101/authorize \
  -H "Content-Type: application/json" \
  -d '{
    "scanId": "abc123xyz",
    "authorized": true,
    "unlockDuration": 5
  }' | jq
```

**Request Body:**
```json
{
  "scanId": "abc123xyz",
  "authorized": true,
  "unlockDuration": 5
}
```

**Response (authorized=true):**
```json
{
  "status": "success",
  "message": "Přístup povolen",
  "scanId": "abc123xyz",
  "authorized": true,
  "door": {
    "locked": false,
    "outputPin4": true,
    "outputVoltage": "12V",
    "duration": 5
  }
}
```

**Response (authorized=false):**
```json
{
  "status": "denied",
  "message": "Přístup zamítnut",
  "scanId": "abc123xyz",
  "authorized": false,
  "door": {
    "locked": true,
    "outputPin4": false
  }
}
```

**Chování při authorized=true:**
- ✅ Zelená LED zapne (5s)
- ✅ Relé zapne (5s)
- ✅ Bzučák zahraje "success" tón
- ✅ Automatické vypnutí po `unlockDuration`

**Chování při authorized=false:**
- ❌ Červená LED zapne (3s)
- ❌ Bzučák zahraje "error" tón
- ❌ Relé zůstane vypnuto

---

## 🎨 LED Ovládání

### POST /control/led
Ovládání RGB LED.

```bash
# Zelená LED (solid)
curl -X POST http://localhost:9101/control/led \
  -H "Content-Type: application/json" \
  -d '{"color":"green","mode":"solid","duration":3000}' | jq

# Červená LED (blink)
curl -X POST http://localhost:9101/control/led \
  -H "Content-Type: application/json" \
  -d '{"color":"red","mode":"blink","duration":5000}' | jq

# Vypnutí LED
curl -X POST http://localhost:9101/control/led \
  -H "Content-Type: application/json" \
  -d '{"color":"off"}' | jq
```

**Podporované barvy:**
- `red` - Červená
- `green` - Zelená
- `blue` - Modrá
- `yellow` - Žlutá (red + green)
- `cyan` - Azurová (green + blue)
- `magenta` - Purpurová (red + blue)
- `white` - Bílá (red + green + blue)
- `off` - Vypnuto

**Podporované režimy:**
- `solid` - Konstantní svit
- `blink` - Blikání
- `pulse` - Pulzování
- `off` - Vypnuto

**Parametry:**
- `color` (required) - Barva LED
- `mode` (optional, default: "solid") - Režim svícení
- `duration` (optional, default: 0) - Doba trvání v ms (0 = trvale)

---

## 🔊 Bzučák

### POST /control/buzzer
Ovládání bzučáku.

```bash
# Jednoduchý pípnutí
curl -X POST http://localhost:9101/control/buzzer \
  -H "Content-Type: application/json" \
  -d '{"pattern":"beep"}' | jq

# Úspěšný tón
curl -X POST http://localhost:9101/control/buzzer \
  -H "Content-Type: application/json" \
  -d '{"pattern":"success"}' | jq

# Chybový tón
curl -X POST http://localhost:9101/control/buzzer \
  -H "Content-Type: application/json" \
  -d '{"pattern":"error"}' | jq

# Varovný tón
curl -X POST http://localhost:9101/control/buzzer \
  -H "Content-Type: application/json" \
  -d '{"pattern":"warning"}' | jq
```

**Podporované vzory:**
- `beep` - Krátký pípnutí (200ms)
- `success` - Dva rychlé tóny (250ms)
- `error` - Dlouhý tón (500ms)
- `warning` - Dva střední tóny (400ms)

---

## 🔌 Relé Ovládání

### POST /control/relay
Ovládání relé výstupu.

```bash
# Zapnutí relé na 5 sekund
curl -X POST http://localhost:9101/control/relay \
  -H "Content-Type: application/json" \
  -d '{"state":true,"duration":5000}' | jq

# Trvalé zapnutí relé
curl -X POST http://localhost:9101/control/relay \
  -H "Content-Type: application/json" \
  -d '{"state":true}' | jq

# Vypnutí relé
curl -X POST http://localhost:9101/control/relay \
  -H "Content-Type: application/json" \
  -d '{"state":false}' | jq
```

**Parametry:**
- `state` (required) - true/false
- `duration` (optional, default: 0) - Doba zapnutí v ms (0 = trvale)

**Response:**
```json
{
  "status": "ok",
  "message": "Relé zapnuto",
  "relay": {
    "state": true,
    "no": true,
    "nc": false,
    "maxCurrent": 3.0,
    "voltage": 12.0,
    "activeTime": 1763675764918
  }
}
```

---

## 🚪 Dveře a Zámek

### GET /door
Stav dveří a relé.

```bash
curl http://localhost:9101/door | jq
```

**Response:**
```json
{
  "status": "ok",
  "door": {
    "locked": true,
    "outputPin4": false,
    "outputVoltage": "0V"
  },
  "timestamp": "2025-11-20T22:00:00.000Z"
}
```

### POST /door/unlock
Manuální odemčení dveří.

```bash
curl -X POST http://localhost:9101/door/unlock \
  -H "Content-Type: application/json" \
  -d '{"duration":5}' | jq
```

### POST /door/lock
Manuální zamčení dveří.

```bash
curl -X POST http://localhost:9101/door/lock | jq
```

---

## 📊 Logy a Historie

### GET /history
Historie skenů.

```bash
curl http://localhost:9101/history | jq
```

### GET /access-log
Log přístupů.

```bash
curl http://localhost:9101/access-log?limit=20 | jq
```

### GET /state-log
Stavový log.

```bash
curl http://localhost:9101/state-log?limit=50 | jq
```

---

## 🔍 Diagnostika

### GET /wiegand-output/:code
Wiegand diagnostika (simulace).

```bash
curl http://localhost:9101/wiegand-output/ACCESS_TOKEN_USER001 | jq
```

**Response:**
```json
{
  "status": "ok",
  "message": "Wiegand data simulována (pouze diagnostika)",
  "wiegand": {
    "format": 26,
    "facilityCode": 1,
    "cardNumber": 1403,
    "timestamp": "2025-11-20T22:00:00.000Z"
  },
  "note": "Zařízení používá TCP/IP, Wiegand je pouze pro diagnostické účely"
}
```

---

## 🌐 WebSocket Events

### Připojení
```javascript
const ws = new WebSocket('ws://localhost:9101');

ws.onopen = () => {
  console.log('Připojeno k Entry E QR R1');
};

ws.onmessage = (event) => {
  const data = JSON.parse(event.data);
  console.log('Event:', data.type, data);
};
```

### Události

#### heartbeat
Každých 10 sekund.
```json
{
  "type": "heartbeat",
  "deviceId": "qr-reader-1",
  "model": "Entry E QR R1",
  "firmware": "v3.2.1",
  "status": "online",
  "uptime": 3600,
  "temperature": "38.7",
  "voltage": "12.3",
  "scanCount": 45,
  "led": {
    "red": false,
    "green": true,
    "blue": false,
    "mode": "solid",
    "brightness": 100
  },
  "relay": false,
  "error": null,
  "timestamp": "2025-11-20T22:00:00.000Z"
}
```

#### qr_scan
QR kód načten.
```json
{
  "type": "qr_scan",
  "deviceId": "qr-reader-1",
  "scan": {
    "code": "ACCESS_TOKEN_USER001",
    "type": "QR Code",
    "timestamp": "2025-11-20T22:00:00.000Z",
    "scanId": "abc123xyz"
  },
  "waitingForAuthorization": true
}
```

#### led_change
Změna stavu LED.
```json
{
  "type": "led_change",
  "led": {
    "color": "green",
    "mode": "solid",
    "rgb": {
      "red": false,
      "green": true,
      "blue": false,
      "mode": "solid",
      "brightness": 100
    }
  },
  "timestamp": "2025-11-20T22:00:00.000Z"
}
```

#### relay_change
Změna stavu relé.
```json
{
  "type": "relay_change",
  "relay": {
    "state": true,
    "no": true,
    "nc": false,
    "maxCurrent": 3.0,
    "voltage": 12.0,
    "activeTime": 1763675764918
  },
  "doorLocked": false,
  "timestamp": "2025-11-20T22:00:00.000Z"
}
```

#### buzzer_play
Bzučák přehrává tón.
```json
{
  "type": "buzzer_play",
  "pattern": "success",
  "timestamp": "2025-11-20T22:00:00.000Z"
}
```

---

## 🧪 Testovací Scénáře

### Scénář 1: Autorizovaný přístup

```bash
# 1. Čtečka načte QR kód
curl -X POST http://localhost:9101/scan \
  -H "Content-Type: application/json" \
  -d '{"code":"ACCESS_LAB_001"}'

# Poznamenej scanId z odpovědi (např. "abc123xyz")

# 2. Backend autorizuje přístup
curl -X POST http://localhost:9101/authorize \
  -H "Content-Type: application/json" \
  -d '{
    "scanId": "abc123xyz",
    "authorized": true,
    "unlockDuration": 5
  }'

# Výsledek:
# ✅ Zelená LED svítí 5s
# ✅ Relé zapnuto 5s (dveře odemčeny)
# ✅ Bzučák zahraje "success"
```

### Scénář 2: Zamítnutý přístup

```bash
# 1. Čtečka načte neplatný kód
curl -X POST http://localhost:9101/scan \
  -H "Content-Type: application/json" \
  -d '{"code":"INVALID_CODE_123"}'

# 2. Backend zamítne přístup
curl -X POST http://localhost:9101/authorize \
  -H "Content-Type: application/json" \
  -d '{
    "scanId": "abc123xyz",
    "authorized": false
  }'

# Výsledek:
# ❌ Červená LED svítí 3s
# ❌ Bzučák zahraje "error"
# ❌ Relé zůstane vypnuto
```

### Scénář 3: Manuální ovládání

```bash
# RGB LED test
curl -X POST http://localhost:9101/control/led \
  -H "Content-Type: application/json" \
  -d '{"color":"yellow","mode":"blink","duration":5000}'

# Bzučák test
curl -X POST http://localhost:9101/control/buzzer \
  -H "Content-Type: application/json" \
  -d '{"pattern":"warning"}'

# Relé test
curl -X POST http://localhost:9101/control/relay \
  -H "Content-Type: application/json" \
  -d '{"state":true,"duration":10000}'
```

---

## 📍 Přehled 6 čteček

| Čtečka | Port | Serial Number | MAC Address | Zkušebna |
|--------|------|---------------|-------------|----------|
| QR-1 | 9101 | qr-reader-1 | AA:BB:CC:DD:EE:01 | Lab-01 |
| QR-2 | 9102 | qr-reader-2 | AA:BB:CC:DD:EE:02 | Lab-02 |
| QR-3 | 9103 | qr-reader-3 | AA:BB:CC:DD:EE:03 | Lab-03 |
| QR-4 | 9104 | qr-reader-4 | AA:BB:CC:DD:EE:04 | Lab-04 |
| QR-5 | 9105 | qr-reader-5 | AA:BB:CC:DD:EE:05 | Lab-05 |
| QR-6 | 9106 | qr-reader-6 | AA:BB:CC:DD:EE:06 | Lab-06 |

---

## ⚙️ Technické Specifikace

**Model:** Entry E QR R1  
**Firmware:** v3.2.1  
**Hardware:** Rev 2.0  
**Komunikace:** TCP/IP (HTTP REST + WebSocket)  
**Napájení:** 12V DC  
**Příkon:** 2-15W  
**Krytí:** IP65  

**Čtení:**
- Vzdálenost: 15 cm
- Čas čtení: 80 ms
- Min. velikost QR: 20 mm
- Max. velikost QR: 100 mm

**I/O:**
- RGB LED (red, green, blue)
- Relé výstup (3A @ 12V DC)
- Bzučák (2.5 kHz)
- Tamper switch

**Podporované formáty:**
- QR Code
- EAN13, EAN8
- CODE128, CODE39
- Interleaved 2 of 5

---

*Dokument vytvořen: 20. listopadu 2025*  
*Verze: 1.0*  
*Autor: GitHub Copilot (Claude Sonnet 4.5)*
