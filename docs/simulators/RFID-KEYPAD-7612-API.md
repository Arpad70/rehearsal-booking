# RFID Keypad 7612 - API Documentation

## 📡 Komunikační protokol: TCP/IP (HTTP REST + WebSocket)

**Base URL:** `http://192.168.1.201:9401` (Keypad-1), `http://192.168.1.202:9402` (Keypad-2)  
**WebSocket:** `ws://192.168.1.201:9401` (Keypad-1), `ws://192.168.1.202:9402` (Keypad-2)

---

## 🔧 HTTP REST API Endpoints

### 1. GET / - Device Information

Základní informace o zařízení a dostupných endpointech.

**Request:**
```bash
curl http://localhost:9401/
```

**Response:**
```json
{
  "device": "RFID Keypad 7612",
  "deviceId": "KEYPAD-1",
  "model": "RFID Keypad 7612",
  "firmware": "v4.1.2",
  "mac": "AA:BB:CC:DD:FF:01",
  "manufacturer": "Access Control Systems Ltd.",
  "endpoints": {
    "deviceInfo": "/device-info",
    "status": "/status",
    "diagnostics": "/diagnostics",
    "rfidScan": "/rfid-scan (POST)",
    "pinEntry": "/pin-entry (POST)",
    "authorize": "/authorize (POST)",
    "controlLED": "/control/led (POST)",
    "controlRelay": "/control/relay (POST)",
    "controlBuzzer": "/control/buzzer (POST)",
    "wiegandOutput": "/wiegand-output/:code",
    "door": "/door",
    "history": "/history",
    "accessLog": "/access-log",
    "stateLog": "/state-log",
    "simulate": "/simulate (POST)"
  }
}
```

---

### 2. GET /device-info - Hardware Specifications

Kompletní hardwarové specifikace zařízení.

**Request:**
```bash
curl http://localhost:9401/device-info
```

**Response:**
```json
{
  "status": "ok",
  "device": {
    "model": "RFID Keypad 7612",
    "firmware": "v4.1.2",
    "serialNumber": "KEYPAD-1",
    "macAddress": "AA:BB:CC:DD:FF:01",
    "hardwareVersion": "Rev 3.0",
    "manufacturer": "Access Control Systems Ltd."
  },
  "rfid": {
    "frequency": "125kHz",
    "supportedCards": ["EM4100", "EM4102", "TK4100", "TK4102"],
    "readRange": "5 cm",
    "readTime": "120 ms"
  },
  "keypad": {
    "type": "12-key",
    "layout": "numeric",
    "keys": ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "*", "#"],
    "pinLength": {
      "min": 4,
      "max": 8
    }
  },
  "interfaces": {
    "wiegand": {
      "enabled": true,
      "format": "26-bit"
    },
    "ethernet": {
      "enabled": true,
      "ip": "192.168.1.201",
      "mac": "AA:BB:CC:DD:FF:01"
    }
  },
  "io": {
    "led": "RGB (red, green, blue, yellow, cyan, magenta, white)",
    "relays": "2× NO/NC, 3A @ 30V DC",
    "buzzer": "2800 Hz, 85 dB",
    "inputs": "tamper, door sensor, exit button"
  }
}
```

---

### 3. GET /status - Device Status

Aktuální stav zařízení.

**Request:**
```bash
curl http://localhost:9401/status
```

**Response:**
```json
{
  "status": "ok",
  "device": {
    "serialNumber": "KEYPAD-1",
    "model": "RFID Keypad 7612",
    "firmware": "v4.1.2",
    "uptime": 3600,
    "temperature": "43.2",
    "voltage": "12.15"
  },
  "rfid": {
    "lastCardUID": 305419896,
    "lastCardTimestamp": "2025-11-20T22:30:00Z",
    "cardCount": 15
  },
  "keypad": {
    "lastPINTimestamp": "2025-11-20T22:30:05Z",
    "pinCount": 8
  },
  "io": {
    "led": {
      "color": "blue",
      "mode": "steady"
    },
    "relay1": false,
    "relay2": false,
    "buzzer": "off"
  }
}
```

---

### 4. GET /diagnostics - Enhanced Diagnostics

Rozšířená diagnostika s úspěšností operací.

**Request:**
```bash
curl http://localhost:9401/diagnostics
```

**Response:**
```json
{
  "status": "ok",
  "device": {
    "serialNumber": "KEYPAD-1",
    "model": "RFID Keypad 7612",
    "firmware": "v4.1.2",
    "uptime": 3600,
    "temperature": "43.2 °C",
    "voltage": "12.15 V"
  },
  "rfid": {
    "enabled": true,
    "frequency": "125kHz",
    "readRange": "5 cm",
    "lastCardUID": 305419896,
    "lastCardTimestamp": "2025-11-20T22:30:00Z",
    "totalScans": 20,
    "successfulScans": 18,
    "failedScans": 2,
    "successRate": "90.0 %"
  },
  "keypad": {
    "type": "12-key",
    "backlight": true,
    "lastPINTimestamp": "2025-11-20T22:30:05Z",
    "totalPINs": 10,
    "successfulPINs": 9,
    "failedPINs": 1,
    "successRate": "90.0 %"
  },
  "io": {
    "led": {
      "red": false,
      "green": false,
      "blue": true,
      "currentColor": "blue",
      "mode": "steady"
    },
    "relay1": {
      "state": false,
      "no": false,
      "nc": true,
      "activations": 15,
      "purpose": "door_lock"
    },
    "relay2": {
      "state": false,
      "no": false,
      "nc": true,
      "activations": 2,
      "purpose": "alarm"
    },
    "buzzer": {
      "enabled": false,
      "pattern": "off",
      "lastPattern": "success"
    },
    "tamper": {
      "triggered": false,
      "count": 0
    },
    "doorSensor": {
      "open": false,
      "openCount": 15
    }
  },
  "errors": {
    "errorCount": 0,
    "lastError": null
  },
  "maintenance": {
    "lastMaintenance": "2025-11-20T00:00:00Z",
    "nextMaintenance": "2026-02-18T00:00:00Z"
  }
}
```

---

### 5. POST /rfid-scan - Simulate RFID Card Scan

Simulace přiložení RFID karty.

**Request:**
```bash
curl -X POST http://localhost:9401/rfid-scan \
  -H "Content-Type: application/json" \
  -d '{
    "card_uid": 305419896
  }'
```

**Parameters:**
- `card_uid` (number, required): UID RFID karty (0-4294967295)

**Response:**
```json
{
  "status": "success",
  "message": "RFID karta načtena, čeká se na autorizaci",
  "card": {
    "uid": 305419896,
    "uidHex": "12345678",
    "timestamp": "2025-11-20T22:30:00Z",
    "scanId": "ABC123XYZ",
    "deviceId": "KEYPAD-1"
  },
  "device": {
    "led": "blue",
    "buzzer": "beep"
  }
}
```

**Workflow:**
1. Zařízení přečte RFID kartu
2. Modrá LED blikne (1s)
3. Bzučák "beep" (100ms)
4. Zařízení čeká na autorizaci z backendu
5. WebSocket notifikace: `rfid_scan`

---

### 6. POST /pin-entry - Simulate PIN Entry

Simulace zadání PIN kódu.

**Request:**
```bash
curl -X POST http://localhost:9401/pin-entry \
  -H "Content-Type: application/json" \
  -d '{
    "pin": "1234"
  }'
```

**Parameters:**
- `pin` (string, required): PIN kód (4-8 číslic)

**Response:**
```json
{
  "status": "success",
  "message": "PIN zadán, čeká se na autorizaci",
  "pin": {
    "pin": "1234",
    "pinMasked": "****",
    "length": 4,
    "timestamp": "2025-11-20T22:30:05Z",
    "entryId": "XYZ789ABC",
    "deviceId": "KEYPAD-1"
  },
  "device": {
    "led": "yellow",
    "buzzer": "beep"
  }
}
```

**Workflow:**
1. Uživatel zadá PIN na klávesnici
2. Žlutá LED blikne (1s)
3. Bzučák "beep" (100ms)
4. Zařízení čeká na autorizaci z backendu
5. WebSocket notifikace: `pin_entry`

**Validace:**
- PIN musí mít 4-8 číslic
- Timeout: 10 sekund mezi stisky
- Potvrzení: tlačítko `#`
- Anulace: dlouhý stisk `*`

---

### 7. POST /authorize - Backend Authorization

Autorizace z backendu po skenování RFID nebo zadání PIN.

**Request (Access Granted):**
```bash
curl -X POST http://localhost:9401/authorize \
  -H "Content-Type: application/json" \
  -d '{
    "scanId": "ABC123XYZ",
    "authorized": true,
    "unlockDuration": 5
  }'
```

**Parameters:**
- `scanId` nebo `entryId` (string, required): ID z RFID scan nebo PIN entry
- `authorized` (boolean, required): Výsledek autorizace
- `unlockDuration` (number, optional): Doba odemčení v sekundách (default: 5)

**Response (Access Granted):**
```json
{
  "status": "success",
  "message": "Přístup povolen",
  "access": {
    "granted": true,
    "doorUnlocked": true,
    "relay1": true,
    "led": "green",
    "buzzer": "success",
    "duration": 5
  }
}
```

**Request (Access Denied):**
```bash
curl -X POST http://localhost:9401/authorize \
  -H "Content-Type: application/json" \
  -d '{
    "scanId": "ABC123XYZ",
    "authorized": false
  }'
```

**Response (Access Denied):**
```json
{
  "status": "success",
  "message": "Přístup zamítnut",
  "access": {
    "granted": false,
    "doorUnlocked": false,
    "relay1": false,
    "led": "red",
    "buzzer": "error"
  }
}
```

**Workflow (Access Granted):**
1. Backend odešle autorizaci
2. Relé 1 ON (dveřní zámek odemčen)
3. Zelená LED svítí (5s)
4. Bzučák "success" (500ms)
5. Po 5s: Relé 1 OFF, LED OFF
6. WebSocket notifikace: `access_granted`

**Workflow (Access Denied):**
1. Backend odešle zamítnutí
2. Červená LED bliká (3s)
3. Bzučák "error" (1000ms)
4. WebSocket notifikace: `access_denied`

---

### 8. POST /control/led - RGB LED Control

Ovládání RGB LED indikace.

**Request:**
```bash
curl -X POST http://localhost:9401/control/led \
  -H "Content-Type: application/json" \
  -d '{
    "color": "green",
    "mode": "blink",
    "duration": 5
  }'
```

**Parameters:**
- `color` (string, required): Barva LED
  - `red`, `green`, `blue`, `yellow`, `cyan`, `magenta`, `white`, `off`
- `mode` (string, optional): Režim svícení (default: `steady`)
  - `steady`: Trvalé svícení
  - `blink`: Pomalé blikání (1 Hz)
  - `fast_blink`: Rychlé blikání (4 Hz)
  - `pulse`: Pulzování (fade in/out)
- `duration` (number, optional): Automatické vypnutí po N sekundách (0 = permanent)

**Response:**
```json
{
  "status": "success",
  "message": "LED nastavena na green",
  "led": {
    "color": "green",
    "mode": "blink",
    "red": false,
    "green": true,
    "blue": false
  }
}
```

**Příklady:**

**Zelená LED trvalé svícení:**
```bash
curl -X POST http://localhost:9401/control/led \
  -d '{"color":"green","mode":"steady"}'
```

**Červená LED rychlé blikání 10s:**
```bash
curl -X POST http://localhost:9401/control/led \
  -d '{"color":"red","mode":"fast_blink","duration":10}'
```

**LED vypnout:**
```bash
curl -X POST http://localhost:9401/control/led \
  -d '{"color":"off"}'
```

---

### 9. POST /control/relay - Relay Control

Ovládání relé výstupů (dveřní zámek, alarm).

**Request:**
```bash
curl -X POST http://localhost:9401/control/relay \
  -H "Content-Type: application/json" \
  -d '{
    "relay": 1,
    "state": true,
    "duration": 5
  }'
```

**Parameters:**
- `relay` (number, required): Číslo relé (1 nebo 2)
  - `1`: Dveřní zámek
  - `2`: Alarm/auxiliary
- `state` (boolean, required): Stav relé
  - `true`: ON (NO zapnuto, NC vypnuto)
  - `false`: OFF (NO vypnuto, NC zapnuto)
- `duration` (number, optional): Automatické vypnutí po N sekundách (0 = permanent)

**Response:**
```json
{
  "status": "success",
  "message": "Relé 1 nastaveno na ON",
  "relay1": {
    "state": true,
    "no": true,
    "nc": false,
    "maxCurrent": 3.0,
    "maxVoltage": 30,
    "purpose": "door_lock",
    "activations": 16
  },
  "relay2": {
    "state": false,
    "no": false,
    "nc": true,
    "maxCurrent": 3.0,
    "maxVoltage": 30,
    "purpose": "alarm",
    "activations": 2
  }
}
```

**Příklady:**

**Odemknout dveře na 5 sekund:**
```bash
curl -X POST http://localhost:9401/control/relay \
  -d '{"relay":1,"state":true,"duration":5}'
```

**Aktivovat alarm trvale:**
```bash
curl -X POST http://localhost:9401/control/relay \
  -d '{"relay":2,"state":true}'
```

**Vypnout alarm:**
```bash
curl -X POST http://localhost:9401/control/relay \
  -d '{"relay":2,"state":false}'
```

---

### 10. POST /control/buzzer - Buzzer Control

Ovládání bzučáku.

**Request:**
```bash
curl -X POST http://localhost:9401/control/buzzer \
  -H "Content-Type: application/json" \
  -d '{
    "pattern": "success"
  }'
```

**Parameters:**
- `pattern` (string, required): Vzor bzučáku
  - `beep`: Krátký pípnutí (100ms)
  - `success`: Úspěch (500ms)
  - `error`: Chyba (1000ms)
  - `warning`: Varování (800ms)
  - `off`: Vypnout

**Response:**
```json
{
  "status": "success",
  "message": "Bzučák přehrává: success",
  "buzzer": {
    "enabled": true,
    "pattern": "success",
    "frequency": 2800
  }
}
```

**Příklady:**

**Přehrát úspěch:**
```bash
curl -X POST http://localhost:9401/control/buzzer \
  -d '{"pattern":"success"}'
```

**Přehrát chybu:**
```bash
curl -X POST http://localhost:9401/control/buzzer \
  -d '{"pattern":"error"}'
```

---

### 11. GET /wiegand-output/:code - Wiegand Diagnostics

Diagnostika Wiegand protokolu (facility code + card number).

**Request:**
```bash
curl http://localhost:9401/wiegand-output/305419896
```

**Response:**
```json
{
  "status": "ok",
  "message": "Wiegand diagnostika",
  "input": {
    "code": 305419896,
    "codeHex": "12345678"
  },
  "wiegand": {
    "format": 26,
    "facilityCode": 1,
    "cardNumber": 13415,
    "rawBits": "01000000100011010001110111",
    "rawHex": "1034677",
    "timestamp": "2025-11-20T22:30:00Z"
  },
  "timing": {
    "pulseWidth": "50 μs",
    "intervalWidth": "2000 μs",
    "totalTime": "52 ms"
  }
}
```

**Příklad s různými kartami:**

**Karta 1:**
```bash
curl http://localhost:9401/wiegand-output/305419896
# Facility: 1, Card: 13415
```

**Karta 2:**
```bash
curl http://localhost:9401/wiegand-output/287454020
# Facility: 1, Card: 54820
```

---

### 12. GET /door - Door Status

Stav dveří a senzoru.

**Request:**
```bash
curl http://localhost:9401/door
```

**Response:**
```json
{
  "status": "ok",
  "door": {
    "locked": true,
    "relay1": false,
    "doorSensor": {
      "open": false,
      "lastChange": "2025-11-20T22:25:00Z"
    }
  }
}
```

---

### 13. GET /history - Entry History

Historie skenování a zadání PIN.

**Request:**
```bash
curl http://localhost:9401/history?limit=10
```

**Parameters:**
- `limit` (number, optional): Počet záznamů (default: 50)

**Response:**
```json
{
  "status": "ok",
  "count": 23,
  "history": [
    {
      "pin": "1234",
      "pinMasked": "****",
      "length": 4,
      "timestamp": "2025-11-20T22:30:05Z",
      "entryId": "XYZ789ABC",
      "deviceId": "KEYPAD-1"
    }
  ]
}
```

---

### 14. GET /access-log - Access Log

Log přístupových událostí.

**Request:**
```bash
curl http://localhost:9401/access-log?limit=20
```

**Response:**
```json
{
  "status": "ok",
  "count": 15,
  "log": [
    {
      "action": "door_unlock",
      "relay1": true,
      "led": "green",
      "buzzer": "success",
      "duration": 5,
      "timestamp": "2025-11-20T22:30:10Z",
      "deviceId": "KEYPAD-1"
    }
  ]
}
```

---

### 15. GET /state-log - State Log

Log změn stavu zařízení.

**Request:**
```bash
curl http://localhost:9401/state-log?limit=30
```

**Response:**
```json
{
  "status": "ok",
  "count": 150,
  "log": [
    {
      "type": "access_granted",
      "message": "Přístup povolen - ID: ABC123XYZ",
      "deviceId": "KEYPAD-1",
      "timestamp": "2025-11-20T22:30:10Z",
      "deviceStatus": {
        "temperature": "43.2",
        "voltage": "12.15",
        "uptime": 3600,
        "error": null
      }
    }
  ]
}
```

---

### 16. POST /simulate - Fault Simulation

Simulace poruch a chyb.

**Request:**
```bash
curl -X POST http://localhost:9401/simulate \
  -H "Content-Type: application/json" \
  -d '{
    "action": "rfid_fault"
  }'
```

**Available Actions:**
- `offline`: Zařízení offline
- `online`: Zařízení online
- `error`: Obecná chyba
- `clear_error`: Vymazat chybu
- `temperature`: Změna teploty
- `rfid_fault`: Porucha RFID čtečky
- `keypad_fault`: Porucha klávesnice
- `tamper`: Detekce manipulace
- `connection_lost`: Ztráta spojení s backendem

**Response:**
```json
{
  "status": "ok",
  "message": "Akce rfid_fault provedena",
  "diagnostics": {
    "temperature": "43.2",
    "voltage": "12.15",
    "error": "Hardware fault: RFID reader not responding",
    "errorCount": 1
  }
}
```

**Příklady:**

**Simulace poruchy RFID:**
```bash
curl -X POST http://localhost:9401/simulate \
  -d '{"action":"rfid_fault"}'
```

**Simulace detekce manipulace:**
```bash
curl -X POST http://localhost:9401/simulate \
  -d '{"action":"tamper"}'
```

**Vymazání chyby:**
```bash
curl -X POST http://localhost:9401/simulate \
  -d '{"action":"clear_error"}'
```

---

## 🔄 WebSocket Events

Připojení: `ws://localhost:9401`

### Event Types:

#### 1. `connected`
```json
{
  "type": "connected",
  "deviceId": "KEYPAD-1",
  "model": "RFID Keypad 7612",
  "firmware": "v4.1.2",
  "message": "Připojeno k RFID Keypad 7612",
  "timestamp": "2025-11-20T22:30:00Z"
}
```

#### 2. `heartbeat` (každých 10s)
```json
{
  "type": "heartbeat",
  "deviceId": "KEYPAD-1",
  "status": "online",
  "uptime": 3600,
  "temperature": "43.2",
  "voltage": "12.15",
  "rfidScans": 15,
  "pinEntries": 8,
  "relay1": false,
  "relay2": false,
  "led": "blue",
  "error": null,
  "timestamp": "2025-11-20T22:30:00Z"
}
```

#### 3. `rfid_scan`
```json
{
  "type": "rfid_scan",
  "deviceId": "KEYPAD-1",
  "card": {
    "uid": 305419896,
    "uidHex": "12345678",
    "timestamp": "2025-11-20T22:30:00Z",
    "scanId": "ABC123XYZ"
  },
  "wiegand": {
    "format": 26,
    "facilityCode": 1,
    "cardNumber": 13415
  },
  "waitingForAuthorization": true,
  "authMode": "card_or_pin"
}
```

#### 4. `pin_entry`
```json
{
  "type": "pin_entry",
  "deviceId": "KEYPAD-1",
  "pin": {
    "pinMasked": "****",
    "length": 4,
    "timestamp": "2025-11-20T22:30:05Z",
    "entryId": "XYZ789ABC"
  },
  "waitingForAuthorization": true,
  "authMode": "card_or_pin"
}
```

#### 5. `access_granted`
```json
{
  "type": "access_granted",
  "deviceId": "KEYPAD-1",
  "id": "ABC123XYZ",
  "event": {
    "action": "door_unlock",
    "relay1": true,
    "led": "green",
    "buzzer": "success",
    "duration": 5
  }
}
```

#### 6. `access_denied`
```json
{
  "type": "access_denied",
  "deviceId": "KEYPAD-1",
  "id": "ABC123XYZ",
  "led": "red",
  "buzzer": "error"
}
```

#### 7. `relay_change`
```json
{
  "type": "relay_change",
  "deviceId": "KEYPAD-1",
  "relay1": {
    "state": true,
    "no": true,
    "nc": false,
    "purpose": "door_lock"
  },
  "relay2": {
    "state": false,
    "no": false,
    "nc": true,
    "purpose": "alarm"
  }
}
```

#### 8. `led_change`
```json
{
  "type": "led_change",
  "deviceId": "KEYPAD-1",
  "led": {
    "color": "green",
    "mode": "steady",
    "red": false,
    "green": true,
    "blue": false
  }
}
```

#### 9. `buzzer_change`
```json
{
  "type": "buzzer_change",
  "deviceId": "KEYPAD-1",
  "buzzer": {
    "pattern": "success",
    "enabled": true,
    "frequency": 2800
  }
}
```

#### 10. `state_change`
```json
{
  "type": "state_change",
  "deviceId": "KEYPAD-1",
  "log": {
    "type": "access_granted",
    "message": "Přístup povolen - ID: ABC123XYZ",
    "timestamp": "2025-11-20T22:30:10Z",
    "deviceStatus": {
      "temperature": "43.2",
      "voltage": "12.15",
      "uptime": 3600,
      "error": null
    }
  }
}
```

#### 11. `tamper_alert`
```json
{
  "type": "tamper_alert",
  "deviceId": "KEYPAD-1",
  "timestamp": "2025-11-20T22:30:00Z"
}
```

#### 12. `hardware_fault`
```json
{
  "type": "hardware_fault",
  "deviceId": "KEYPAD-1",
  "error": "Hardware fault: RFID reader not responding",
  "timestamp": "2025-11-20T22:30:00Z"
}
```

---

## 🧪 Testovací scénáře

### Scénář 1: RFID přístup

```bash
# 1. Přiložení RFID karty
curl -X POST http://localhost:9401/rfid-scan \
  -d '{"card_uid":305419896}'

# Backend obdrží WebSocket notifikaci "rfid_scan"
# Backend ověří kartu v databázi

# 2. Backend odešle autorizaci
curl -X POST http://localhost:9401/authorize \
  -d '{"scanId":"ABC123XYZ","authorized":true,"unlockDuration":5}'

# Výsledek:
# - Relé 1 ON (5s)
# - Zelená LED (5s)
# - Bzučák "success" (500ms)
# - WebSocket: "access_granted"
# - Po 5s: Relé 1 OFF, LED OFF
```

### Scénář 2: PIN přístup

```bash
# 1. Zadání PIN kódu
curl -X POST http://localhost:9401/pin-entry \
  -d '{"pin":"1234"}'

# Backend obdrží WebSocket notifikaci "pin_entry"
# Backend ověří PIN v databázi

# 2. Backend odešle autorizaci
curl -X POST http://localhost:9401/authorize \
  -d '{"entryId":"XYZ789ABC","authorized":true,"unlockDuration":5}'

# Výsledek stejný jako Scénář 1
```

### Scénář 3: Dual authentication (RFID + PIN)

```bash
# 1. Přiložení RFID karty
curl -X POST http://localhost:9401/rfid-scan \
  -d '{"card_uid":305419896}'

# 2. Zadání PIN kódu
curl -X POST http://localhost:9401/pin-entry \
  -d '{"pin":"1234"}'

# Backend obdrží obě notifikace
# Backend ověří kombinaci karty + PIN

# 3. Backend odešle autorizaci
curl -X POST http://localhost:9401/authorize \
  -d '{"scanId":"ABC123XYZ","authorized":true,"unlockDuration":5}'
```

### Scénář 4: Zamítnutý přístup

```bash
# 1. Přiložení neplatné karty
curl -X POST http://localhost:9401/rfid-scan \
  -d '{"card_uid":999999999}'

# Backend ověří kartu - nenalezena

# 2. Backend zamítne přístup
curl -X POST http://localhost:9401/authorize \
  -d '{"scanId":"ABC123XYZ","authorized":false}'

# Výsledek:
# - Červená LED blikání (3s)
# - Bzučák "error" (1000ms)
# - WebSocket: "access_denied"
```

### Scénář 5: Manuální ovládání

```bash
# Odemknout dveře na 10 sekund
curl -X POST http://localhost:9401/control/relay \
  -d '{"relay":1,"state":true,"duration":10}'

# Zelená LED trvalé svícení
curl -X POST http://localhost:9401/control/led \
  -d '{"color":"green","mode":"steady"}'

# Přehrát úspěch
curl -X POST http://localhost:9401/control/buzzer \
  -d '{"pattern":"success"}'
```

### Scénář 6: Simulace poruchy

```bash
# Simulace poruchy RFID čtečky
curl -X POST http://localhost:9401/simulate \
  -d '{"action":"rfid_fault"}'

# Výsledek:
# - RFID disabled
# - Červená LED rychlé blikání
# - error: "Hardware fault: RFID reader not responding"
# - WebSocket: "hardware_fault"

# Vymazání chyby
curl -X POST http://localhost:9401/simulate \
  -d '{"action":"clear_error"}'
```

---

## 📊 Srovnání s Entry E QR R1

| Funkce | Entry E QR R1 | RFID Keypad 7612 |
|--------|---------------|------------------|
| **Čtečka** | QR/Barcode | RFID 125kHz |
| **Vstup** | Optické skenování | Bezkontaktní karta + PIN |
| **Čtecí vzdálenost** | 15 cm | 5 cm |
| **Čtecí čas** | 80 ms | 120 ms |
| **RGB LED** | ✅ 7 barev, 4 režimy | ✅ 7 barev, 4 režimy |
| **Relé** | ✅ 1× (NO/NC, 3A) | ✅ 2× (NO/NC, 3A) |
| **Bzučák** | ✅ 4 vzory | ✅ 4 vzory |
| **Wiegand** | ✅ Diagnostika | ✅ 26-bit + keypad 35-bit |
| **Komunikace** | TCP/IP (HTTP + WS) | TCP/IP (HTTP + WS) |
| **Dual Auth** | ❌ | ✅ RFID + PIN |
| **Klávesnice** | ❌ | ✅ 12-key numeric |
| **Standalone** | ✅ | ✅ |

---

*Dokument vytvořen: 20. listopadu 2025*  
*Verze: 1.0*  
*Autor: GitHub Copilot (Claude Sonnet 4.5)*
