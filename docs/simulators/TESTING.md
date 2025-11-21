# 🧪 Testovací příručka - IoT Simulace

## 📡 WebSocket Monitoring

### Připojení k zařízením

```bash
# QR čtečka #1
wscat -c ws://localhost:9101

# Klávesnice #1
wscat -c ws://localhost:9401

# Shelly #1
wscat -c ws://localhost:9301

# IP Kamera #1
wscat -c ws://localhost:9201
```

## 🔍 Heartbeat (každých 10s)

Všechna zařízení posílají heartbeat každých 10 sekund:

```json
{
  "type": "heartbeat",
  "deviceId": "qr-reader-1",
  "status": "online",
  "uptime": 120,
  "temperature": 35.2,
  "error": null,
  "timestamp": "2025-11-20T20:00:00.000Z"
}
```

## 🚪 QR Čtečka - Test Workflow

### 1. Načtení QR kódu
```bash
curl -X POST http://localhost:9101/scan \
  -H "Content-Type: application/json" \
  -d '{"code":"USER_12345"}'
```

**Odpověď:**
```json
{
  "status": "success",
  "message": "QR kód načten, čeká se na autorizaci z backendu",
  "scan": {
    "code": "USER_12345",
    "scanId": "abc123",
    "deviceId": "qr-reader-1"
  }
}
```

**WebSocket event:**
```json
{
  "type": "qr_scan",
  "deviceId": "qr-reader-1",
  "scan": {...},
  "waitingForAuthorization": true
}
```

### 2. Backend autorizuje přístup
```bash
# POVOLENO
curl -X POST http://localhost:9101/authorize \
  -H "Content-Type: application/json" \
  -d '{"scanId":"abc123","authorized":true,"unlockDuration":5}'

# ZAMÍTNUTO
curl -X POST http://localhost:9101/authorize \
  -H "Content-Type: application/json" \
  -d '{"scanId":"abc123","authorized":false}'
```

**Povoleno - Zelená LED + 5V výstup:**
```json
{
  "status": "success",
  "access": {
    "granted": true,
    "ledGreen": true,
    "ledRed": false,
    "outputPin4": true,
    "outputVoltage": "5V"
  }
}
```

**WebSocket:**
```json
{
  "type": "door_unlock",
  "deviceId": "qr-reader-1",
  "door": {
    "locked": false,
    "outputPin4": true,
    "ledGreen": true
  }
}
```

**Zamítnuto - Červená LED:**
```json
{
  "status": "success",
  "access": {
    "granted": false,
    "ledGreen": false,
    "ledRed": true
  }
}
```

## 🔢 Klávesnice - Test Workflow

### 1. Zadání PIN
```bash
curl -X POST http://localhost:9401/verify-pin \
  -H "Content-Type: application/json" \
  -d '{"pin":"1234"}'
```

**WebSocket event:**
```json
{
  "type": "pin_entry",
  "deviceId": "keypad-1",
  "entry": {
    "pin": "1234",
    "entryId": "xyz789"
  },
  "waitingForAuthorization": true
}
```

### 2. Backend autorizuje
```bash
curl -X POST http://localhost:9401/authorize \
  -H "Content-Type: application/json" \
  -d '{"entryId":"xyz789","authorized":true,"unlockDuration":5}'
```

## 🔌 Shelly PRO 1 - Test

### Zapnutí relé
```bash
curl "http://localhost:9301/relay/0?turn=on"
```

**WebSocket:**
```json
{
  "type": "relay_change",
  "deviceId": "shelly-1",
  "relay": {
    "id": 0,
    "ison": true,
    "power": 125.3
  }
}
```

### Časovač
```bash
curl "http://localhost:9301/relay/0?turn=on&timer=10"
```

## 📹 IP Kamera - Test

### Detekce pohybu
Automaticky každých 5s (pokud je zapnuto):

**WebSocket:**
```json
{
  "type": "motion_detected",
  "deviceId": "camera-1",
  "event": {
    "zone": "Zone 2",
    "confidence": "0.87",
    "timestamp": "..."
  }
}
```

### Spuštění nahrávání
```bash
curl -X POST http://localhost:9201/recording/start
```

**WebSocket:**
```json
{
  "type": "recording_started",
  "deviceId": "camera-1"
}
```

## 🔥 Simulace poruch

### QR Čtečka

```bash
# Offline
curl -X POST http://localhost:9101/simulate \
  -H "Content-Type: application/json" \
  -d '{"action":"offline"}'

# Online
curl -X POST http://localhost:9101/simulate \
  -H "Content-Type: application/json" \
  -d '{"action":"online"}'

# Chyba
curl -X POST http://localhost:9101/simulate \
  -H "Content-Type: application/json" \
  -d '{"action":"error","data":{"message":"Scanner not responding"}}'

# Hardware porucha
curl -X POST http://localhost:9101/simulate \
  -H "Content-Type: application/json" \
  -d '{"action":"hardware_fault"}'

# Ztráta spojení
curl -X POST http://localhost:9101/simulate \
  -H "Content-Type: application/json" \
  -d '{"action":"connection_lost"}'

# Teplota
curl -X POST http://localhost:9101/simulate \
  -H "Content-Type: application/json" \
  -d '{"action":"temperature","data":{"value":65.5}}'
```

**WebSocket při poruše:**
```json
{
  "type": "device_offline",
  "deviceId": "qr-reader-1",
  "timestamp": "..."
}
```

### Klávesnice

```bash
# Porucha klávesnice
curl -X POST http://localhost:9401/simulate \
  -H "Content-Type: application/json" \
  -d '{"action":"keypad_fault"}'
```

### Shelly PRO 1

```bash
# Přehřátí
curl -X POST http://localhost:9301/simulate \
  -H "Content-Type: application/json" \
  -d '{"action":"overheating","data":{"value":85.0}}'

# Přetížení
curl -X POST http://localhost:9301/simulate \
  -H "Content-Type: application/json" \
  -d '{"action":"overpower"}'
```

**WebSocket při přehřátí:**
```json
{
  "type": "overheating",
  "deviceId": "shelly-1",
  "temperature": 85.0,
  "timestamp": "..."
}
```

### IP Kamera

```bash
# Ztráta spojení
curl -X POST http://localhost:9201/simulate \
  -H "Content-Type: application/json" \
  -d '{"action":"connection_lost"}'
```

## 📊 Stavové logy

Všechna zařízení zaznamenávají stavové změny:

```bash
# QR čtečka
curl http://localhost:9101/state-log

# Klávesnice
curl http://localhost:9401/state-log

# Shelly
curl http://localhost:9301/state-log

# Kamera
curl http://localhost:9201/state-log
```

**Příklad odpovědi:**
```json
{
  "status": "ok",
  "count": 25,
  "log": [
    {
      "type": "qr_scan",
      "message": "QR kód načten: USER_12345",
      "deviceId": "qr-reader-1",
      "timestamp": "2025-11-20T20:00:00.000Z",
      "deviceStatus": {
        "online": true,
        "error": null,
        "temperature": 35.2
      }
    }
  ]
}
```

## 🎯 Kompletní test scénář

### 1. Monitorování všech zařízení
Otevřete 4 terminály s WebSocket připojeními:
```bash
# Terminál 1
wscat -c ws://localhost:9101

# Terminál 2
wscat -c ws://localhost:9401

# Terminál 3
wscat -c ws://localhost:9301

# Terminál 4
wscat -c ws://localhost:9201
```

### 2. Test přístupu QR kódem
```bash
# Načtení kódu
curl -X POST http://localhost:9101/scan -H "Content-Type: application/json" -d '{"code":"USER_001"}'

# Sledujte WebSocket: qr_scan event

# Autorizace
curl -X POST http://localhost:9101/authorize -H "Content-Type: application/json" -d '{"scanId":"ZKOPIRUJTE_ID","authorized":true,"unlockDuration":5}'

# Sledujte WebSocket: door_unlock, pak za 5s door_lock
```

### 3. Test poruchy
```bash
# Simulace offline
curl -X POST http://localhost:9101/simulate -H "Content-Type: application/json" -d '{"action":"offline"}'

# Sledujte WebSocket: device_offline event + heartbeat se přestane posílat

# Obnovení
curl -X POST http://localhost:9101/simulate -H "Content-Type: application/json" -d '{"action":"online"}'

# Sledujte WebSocket: device_online event + heartbeat obnoveno
```

### 4. Kontrola logů
```bash
curl http://localhost:9101/state-log | jq '.log[] | {type, message, timestamp}'
```

## 📋 Dostupné akce pro /simulate

### Všechna zařízení
- `offline` - zařízení offline
- `online` - zařízení online
- `error` - obecná chyba (+ data.message)
- `clear_error` - vymazání chyby
- `temperature` - změna teploty (+ data.value)
- `connection_lost` - ztráta spojení s backendem

### QR čtečka specifické
- `hardware_fault` - hardwarová porucha skeneru

### Klávesnice specifické
- `keypad_fault` - porucha klávesnice

### Shelly specifické
- `overheating` - přehřátí (+ data.value)
- `overpower` - přetížení

## 🧪 Příklad Python WebSocket klienta

```python
import asyncio
import websockets
import json

async def monitor_device(url, device_name):
    async with websockets.connect(url) as websocket:
        print(f"✅ Připojeno k {device_name}")
        
        async for message in websocket:
            data = json.loads(message)
            event_type = data.get('type')
            
            if event_type == 'heartbeat':
                print(f"💓 {device_name} heartbeat - uptime: {data['uptime']}s")
            elif event_type == 'qr_scan':
                print(f"📱 QR kód: {data['scan']['code']}")
            elif event_type == 'door_unlock':
                print(f"🔓 Dveře odemčeny!")
            elif event_type == 'device_offline':
                print(f"⚠️  {device_name} OFFLINE!")
            else:
                print(f"📩 {device_name}: {event_type}")

# Spuštění
asyncio.run(monitor_device('ws://localhost:9101', 'QR Čtečka 1'))
```
