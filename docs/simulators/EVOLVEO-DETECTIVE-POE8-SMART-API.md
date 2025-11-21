# EVOLVEO Detective POE8 SMART - API Dokumentace

**Verze:** 2.8.5  
**Protokol:** HTTP REST + WebSocket  
**Formát:** JSON  
**Autentizace:** Basic Auth (admin/admin)

---

## 📡 Základní informace

### Přístupové URL

```
HTTP API:  http://192.168.1.211:80
HTTPS API: https://192.168.1.211:443
RTSP:      rtsp://admin:password@192.168.1.211:554/stream1
ONVIF:     http://192.168.1.211:8080/onvif/device_service
WebSocket: ws://192.168.1.211:80
```

### Simulátor porty

| Kamera | Port | IP (simulovaná) | MAC Address |
|--------|------|-----------------|-------------|
| Camera-01 | 9201 | 192.168.1.211 | AA:BB:CC:DD:CC:01 |
| Camera-02 | 9202 | 192.168.1.211 | AA:BB:CC:DD:CC:02 |
| Camera-03 | 9203 | 192.168.1.211 | AA:BB:CC:DD:CC:03 |
| ... | ... | ... | ... |
| Camera-12 | 9212 | 192.168.1.211 | AA:BB:CC:DD:CC:0C |

---

## 🔌 HTTP REST API Endpointy

### 1. Root Endpoint

Získání přehledu všech dostupných endpointů.

```bash
curl http://localhost:9201/
```

**Odpověď:**
```json
{
  "device": "EVOLVEO Detective POE8 SMART Simulator",
  "model": "EVOLVEO Detective POE8 SMART",
  "firmware": "v2.8.5",
  "mac": "AA:BB:CC:DD:CC:01",
  "endpoints": {
    "deviceInfo": "/device-info",
    "status": "/status",
    "diagnostics": "/diagnostics",
    "snapshot": "/snapshot",
    "stream": "/stream (MJPEG)",
    "rtsp": {
      "main": "rtsp://192.168.1.211:554/stream1",
      "sub": "rtsp://192.168.1.211:554/stream2"
    },
    "onvif": "/onvif",
    "settings": "/settings (GET/POST)",
    "recording": {...},
    "analytics": {...},
    "control": {...}
  }
}
```

---

### 2. Device Info

Získání kompletních informací o hardware zařízení.

```bash
curl http://localhost:9201/device-info
```

**Odpověď:**
```json
{
  "status": "ok",
  "device": {
    "model": "EVOLVEO Detective POE8 SMART",
    "firmware": "v2.8.5",
    "hardwareVersion": "Rev 1.0",
    "serialNumber": "CAMERA-01",
    "macAddress": "AA:BB:CC:DD:CC:01",
    "manufacturer": "EVOLVEO"
  },
  "sensor": {
    "type": "1/2.5\" Progressive Scan CMOS",
    "resolution": "8MP (3840×2160)",
    "effectivePixels": 8294400,
    "minIllumination": {
      "color": "0.01 Lux @ F1.6",
      "bw": "0.001 Lux @ F1.6",
      "ir": "0 Lux with IR"
    },
    "wdr": "120 dB",
    "snRatio": 52
  },
  "lens": {
    "type": "Fixed focal",
    "focalLength": 2.8,
    "aperture": "F1.6",
    "fov": {
      "horizontal": 110,
      "vertical": 58,
      "diagonal": 130
    },
    "irCut": "Auto mechanical ICR",
    "focusRange": "3m to infinity"
  },
  "construction": {
    "type": "Dome",
    "material": "Metal housing + Polycarbonate dome",
    "vandal": "IK10 (20 Joules)",
    "weatherproof": "IP67",
    "dimensions": {
      "diameter": 140,
      "height": 95
    }
  },
  "network": {
    "interface": "10/100 Mbps Ethernet",
    "protocols": ["ONVIF Profile S/G/T", "RTSP", "HTTP", "HTTPS", "TCP/IP"],
    "ip": "192.168.1.211",
    "mac": "AA:BB:CC:DD:CC:01",
    "ports": {
      "http": 80,
      "https": 443,
      "rtsp": 554,
      "onvif": 8080
    }
  },
  "power": {
    "poe": {
      "standard": "IEEE 802.3af",
      "enabled": true
    },
    "consumption": {
      "idle": 8.5,
      "active": 12.95,
      "peak": 13.5
    }
  }
}
```

---

### 3. Status

Aktuální stav kamery (online/offline, nahrávání, stream, chyby).

```bash
curl http://localhost:9201/status
```

**Odpověď:**
```json
{
  "status": "ok",
  "state": {
    "online": true,
    "recording": false,
    "streamActive": false,
    "irActive": true,
    "error": null
  },
  "runtime": {
    "uptime": 3600,
    "startTime": "2025-11-20T22:00:00.000Z",
    "currentBitrate": 0,
    "currentFramerate": 0,
    "droppedFrames": 0,
    "totalFrames": 150
  }
}
```

---

### 4. Diagnostics

Detailní diagnostické informace (teplota, napájení, storage, IR, analytics).

```bash
curl http://localhost:9201/diagnostics
```

**Odpověď:**
```json
{
  "status": "ok",
  "environment": {
    "temperature": 47.2,
    "temperatureRange": {
      "min": -30,
      "max": 60
    },
    "humidity": 65,
    "humidityRange": {
      "min": 10,
      "max": 90
    }
  },
  "power": {
    "poe": {
      "enabled": true,
      "standard": "IEEE 802.3af",
      "voltage": 48.0,
      "current": "0.260",
      "power": 12.5
    },
    "consumption": {
      "current": 12.5,
      "idle": 8.5,
      "active": 12.95,
      "peak": 13.5
    }
  },
  "storage": {
    "type": "MicroSD",
    "capacity": 128,
    "used": 0,
    "available": 128,
    "recording": false,
    "overwrite": true,
    "health": 100
  },
  "ir": {
    "enabled": true,
    "currentIntensity": 80,
    "cutFilterState": "night"
  },
  "analytics": {
    "motionEnabled": true,
    "lineCrossingEnabled": false,
    "intrusionEnabled": false,
    "tamperingEnabled": true,
    "stats": {
      "motionDetections": 10,
      "lineCrossings": 0,
      "intrusions": 0,
      "tamperings": 0,
      "faceDetections": 0
    }
  }
}
```

---

### 5. Snapshot

Získání aktuálního snímku z kamery (JPEG).

```bash
# Výchozí rozlišení (1280x720)
curl http://localhost:9201/snapshot -o snapshot.jpg

# Vlastní rozlišení
curl "http://localhost:9201/snapshot?width=1920&height=1080" -o snapshot_hd.jpg

# 8MP rozlišení
curl "http://localhost:9201/snapshot?width=3840&height=2160" -o snapshot_8mp.jpg
```

**Parametry:**
- `width` - Šířka snímku (výchozí: 1280)
- `height` - Výška snímku (výchozí: 720)

**Odpověď:** JPEG obrázek

**Funkce:**
- OSD s časem, datem, IP, MAC
- IR indikátor (pokud aktivní)
- Teplota v pravém dolním rohu
- Motion detection boxy (červené, pokud pohyb detekován)
- Zaostřovací křížek uprostřed

---

### 6. MJPEG Stream

Live video stream v MJPEG formátu.

```bash
# V prohlížeči nebo curl
curl http://localhost:9201/stream

# Nebo otevřít v prohlížeči
http://localhost:9201/stream
```

**Vlastnosti:**
- Rozlišení: 640x480
- Framerate: 25 fps (sub stream)
- Codec: MJPEG
- Recording indikátor (červená tečka + REC)
- IR indikátor
- Animovaný pohyb

---

### 7. RTSP Stream Info

Informace o RTSP streamech a jejich URL.

```bash
curl http://localhost:9201/rtsp
```

**Odpověď:**
```json
{
  "status": "ok",
  "rtsp": {
    "enabled": true,
    "port": 554,
    "authentication": true,
    "streams": {
      "main": {
        "url": "rtsp://admin:********@192.168.1.211:554/stream1",
        "resolution": "3840x2160",
        "framerate": 20,
        "bitrate": 8192,
        "codec": "H.265"
      },
      "sub": {
        "url": "rtsp://admin:********@192.168.1.211:554/stream2",
        "resolution": "1280x720",
        "framerate": 25,
        "bitrate": 1024,
        "codec": "H.264"
      }
    }
  }
}
```

**Použití RTSP URL:**
```bash
# VLC Player
vlc rtsp://admin:password@192.168.1.211:554/stream1

# FFmpeg
ffmpeg -rtsp_transport tcp -i rtsp://admin:password@192.168.1.211:554/stream1 -c copy output.mp4

# OpenCV (Python)
import cv2
cap = cv2.VideoCapture('rtsp://admin:password@192.168.1.211:554/stream1')
```

---

### 8. ONVIF Capabilities

ONVIF kompatibilita a dostupné služby.

```bash
curl http://localhost:9201/onvif
```

**Odpověď:**
```json
{
  "status": "ok",
  "onvif": {
    "enabled": true,
    "profile": "S/G/T",
    "discovery": true,
    "version": "2.0",
    "capabilities": {
      "analytics": true,
      "device": true,
      "events": true,
      "imaging": true,
      "media": true,
      "ptz": false
    },
    "services": {
      "device": "http://192.168.1.211:8080/onvif/device_service",
      "media": "http://192.168.1.211:8080/onvif/media_service",
      "events": "http://192.168.1.211:8080/onvif/event_service",
      "imaging": "http://192.168.1.211:8080/onvif/imaging_service",
      "analytics": "http://192.168.1.211:8080/onvif/analytics_service"
    }
  }
}
```

---

### 9. Settings (GET)

Získání aktuálních nastavení kamery.

```bash
curl http://localhost:9201/settings
```

**Odpověď:**
```json
{
  "status": "ok",
  "settings": {
    "video": {
      "compression": ["H.265+", "H.265", "H.264+", "H.264", "MJPEG"],
      "mainStream": {
        "resolution": "3840x2160",
        "framerate": 20,
        "bitrate": 8192,
        "codec": "H.265"
      },
      "subStream": {
        "resolution": "1280x720",
        "framerate": 25,
        "bitrate": 1024,
        "codec": "H.264"
      }
    },
    "analytics": {
      "motionDetection": {
        "enabled": true,
        "sensitivity": 80,
        "regions": [],
        "threshold": 80
      },
      "lineCrossing": {
        "enabled": false,
        "lines": [],
        "direction": "both"
      },
      "intrusion": {
        "enabled": false,
        "regions": [],
        "actions": ["enter", "exit", "appear", "disappear"]
      }
    },
    "ir": {
      "autoSwitch": true,
      "smartIR": true
    }
  }
}
```

---

### 10. Settings (POST)

Aktualizace nastavení kamery.

```bash
# Změna motion detection sensitivity
curl -X POST http://localhost:9201/settings \
  -H "Content-Type: application/json" \
  -d '{
    "analytics": {
      "motionDetection": {
        "enabled": true,
        "sensitivity": 90
      }
    }
  }'

# Změna video nastavení
curl -X POST http://localhost:9201/settings \
  -H "Content-Type: application/json" \
  -d '{
    "video": {
      "mainStream": {
        "framerate": 25,
        "bitrate": 10240
      }
    }
  }'

# Vypnutí IR auto switch
curl -X POST http://localhost:9201/settings \
  -H "Content-Type: application/json" \
  -d '{
    "ir": {
      "autoSwitch": false
    }
  }'
```

---

### 11. IR Control

Ovládání infračerveného osvětlení.

```bash
# Zapnout IR (manuálně, pokud auto vypnuto)
curl -X POST http://localhost:9201/control/ir \
  -H "Content-Type: application/json" \
  -d '{"enabled": true}'

# Vypnout IR (manuálně)
curl -X POST http://localhost:9201/control/ir \
  -H "Content-Type: application/json" \
  -d '{"enabled": false}'

# Zapnout IR s konkrétní intenzitou
curl -X POST http://localhost:9201/control/ir \
  -H "Content-Type: application/json" \
  -d '{"enabled": true, "intensity": 60}'

# Vypnout automatický režim IR
curl -X POST http://localhost:9201/control/ir \
  -H "Content-Type: application/json" \
  -d '{"autoSwitch": false}'

# Zapnout automatický režim IR
curl -X POST http://localhost:9201/control/ir \
  -H "Content-Type: application/json" \
  -d '{"autoSwitch": true}'
```

**Odpověď:**
```json
{
  "status": "ok",
  "message": "IR zapnuto",
  "ir": {
    "enabled": true,
    "autoSwitch": false,
    "currentIntensity": 80,
    "cutFilterState": "night"
  }
}
```

**Poznámky:**
- V automatickém režimu se IR zapíná od 19:00 do 6:00
- IR zvyšuje teplotu kamery o ~5°C
- IR zvyšuje spotřebu POE o ~4W

---

### 12. Recording Start

Zahájení záznamu na MicroSD kartu.

```bash
curl -X POST http://localhost:9201/recording/start
```

**Odpověď:**
```json
{
  "status": "ok",
  "message": "Nahrávání spuštěno",
  "event": {
    "action": "start",
    "timestamp": "2025-11-20T22:50:00.000Z"
  },
  "storage": {
    "type": "MicroSD",
    "capacity": 128,
    "used": 0,
    "available": 128,
    "recording": true
  }
}
```

**Simulace:**
- Storage se plní rychlostí ~0.1 GB/s
- Po naplnění (pokud overwrite=true) se starší záznamy přepisují

---

### 13. Recording Stop

Zastavení záznamu.

```bash
curl -X POST http://localhost:9201/recording/stop
```

**Odpověď:**
```json
{
  "status": "ok",
  "message": "Nahrávání zastaveno",
  "event": {
    "action": "stop",
    "timestamp": "2025-11-20T22:51:00.000Z"
  },
  "storage": {
    "type": "MicroSD",
    "capacity": 128,
    "used": 6.2,
    "available": 121.8,
    "recording": false
  }
}
```

---

### 14. Recording Log

Historie nahrávání.

```bash
curl http://localhost:9201/recording/log
```

**Odpověď:**
```json
{
  "status": "ok",
  "count": 4,
  "log": [
    {
      "action": "stop",
      "timestamp": "2025-11-20T22:51:00.000Z"
    },
    {
      "action": "start",
      "timestamp": "2025-11-20T22:50:00.000Z"
    }
  ],
  "storage": {
    "type": "MicroSD",
    "capacity": 128,
    "used": 6.2,
    "available": 121.8,
    "recording": false
  }
}
```

---

### 15. Analytics - Motion Detection (GET)

Získání motion detection událostí.

```bash
curl http://localhost:9201/analytics/motion
```

**Odpověď:**
```json
{
  "status": "ok",
  "config": {
    "enabled": true,
    "sensitivity": 80,
    "regions": [],
    "threshold": 80
  },
  "events": [
    {
      "timestamp": "2025-11-20T22:50:42.556Z",
      "confidence": "0.64",
      "zone": "Zone 1",
      "type": "motion"
    }
  ],
  "count": 10
}
```

---

### 16. Analytics - Motion Detection (POST)

Konfigurace motion detection.

```bash
# Zapnout motion detection s vyšší citlivostí
curl -X POST http://localhost:9201/analytics/motion \
  -H "Content-Type: application/json" \
  -d '{
    "enabled": true,
    "sensitivity": 90,
    "threshold": 70
  }'

# Přidat detekční zóny
curl -X POST http://localhost:9201/analytics/motion \
  -H "Content-Type: application/json" \
  -d '{
    "enabled": true,
    "regions": [
      {
        "name": "Entry Door",
        "coordinates": [[100,100], [500,100], [500,400], [100,400]]
      },
      {
        "name": "Window",
        "coordinates": [[600,100], [1000,100], [1000,400], [600,400]]
      }
    ]
  }'
```

---

### 17. Analytics - Line Crossing (GET/POST)

Detekce překročení linie.

```bash
# GET - Získat události
curl http://localhost:9201/analytics/line-crossing

# POST - Konfigurovat
curl -X POST http://localhost:9201/analytics/line-crossing \
  -H "Content-Type: application/json" \
  -d '{
    "enabled": true,
    "direction": "both",
    "lines": [
      {
        "name": "Entry Line",
        "points": [[200, 500], [800, 500]]
      }
    ]
  }'
```

**Odpověď GET:**
```json
{
  "status": "ok",
  "config": {
    "enabled": false,
    "lines": [],
    "direction": "both"
  },
  "events": [],
  "count": 0
}
```

---

### 18. Analytics - Intrusion Detection (GET/POST)

Detekce vniknutí do zakázaných oblastí.

```bash
# POST - Konfigurovat
curl -X POST http://localhost:9201/analytics/intrusion \
  -H "Content-Type: application/json" \
  -d '{
    "enabled": true,
    "regions": [
      {
        "name": "Restricted Area",
        "coordinates": [[300,200], [700,200], [700,600], [300,600]]
      }
    ],
    "actions": ["enter", "exit"]
  }'
```

---

### 19. Analytics - Statistics

Souhrnné statistiky všech analytics.

```bash
curl http://localhost:9201/analytics/stats
```

**Odpověď:**
```json
{
  "status": "ok",
  "stats": {
    "motionDetections": 125,
    "lineCrossings": 5,
    "intrusions": 2,
    "tamperings": 0,
    "faceDetections": 0
  }
}
```

---

### 20. PTZ Control

Ovládání PTZ (Pan-Tilt-Zoom) - kamera nepodporuje, fixní objektiv.

```bash
curl -X POST http://localhost:9201/control/ptz \
  -H "Content-Type: application/json" \
  -d '{"action": "left", "value": 10}'
```

**Odpověď:**
```json
{
  "status": "ok",
  "message": "PTZ příkaz 'left' proveden",
  "action": "left",
  "value": 10,
  "note": "Tato kamera nepodporuje PTZ (fixní objektiv)"
}
```

---

### 21. State Log

Historie stavových změn kamery.

```bash
# Posledních 50 událostí (výchozí)
curl http://localhost:9201/state-log

# Posledních 100 událostí
curl "http://localhost:9201/state-log?limit=100"
```

**Odpověď:**
```json
{
  "status": "ok",
  "count": 156,
  "log": [
    {
      "type": "motion_detected",
      "message": "Pohyb detekován v Zone 1",
      "deviceId": "camera-01",
      "timestamp": "2025-11-20T22:50:42.556Z",
      "deviceStatus": {
        "online": true,
        "error": null,
        "temperature": 47.2,
        "uptime": 3600,
        "recording": false,
        "irActive": true,
        "streamActive": false
      }
    }
  ]
}
```

---

### 22. Simulate

Simulace různých událostí a poruch pro testování.

```bash
# Simulace offline
curl -X POST http://localhost:9201/simulate \
  -H "Content-Type: application/json" \
  -d '{"action": "offline"}'

# Simulace zpět online
curl -X POST http://localhost:9201/simulate \
  -H "Content-Type: application/json" \
  -d '{"action": "online"}'

# Simulace chyby
curl -X POST http://localhost:9201/simulate \
  -H "Content-Type: application/json" \
  -d '{"action": "error", "data": {"message": "Network timeout"}}'

# Změna teploty
curl -X POST http://localhost:9201/simulate \
  -H "Content-Type: application/json" \
  -d '{"action": "temperature", "data": {"value": 65.0}}'

# Plné úložiště
curl -X POST http://localhost:9201/simulate \
  -H "Content-Type: application/json" \
  -d '{"action": "storage_full"}'

# Vymazání úložiště
curl -X POST http://localhost:9201/simulate \
  -H "Content-Type: application/json" \
  -d '{"action": "clear_storage"}'

# Manuální trigger motion
curl -X POST http://localhost:9201/simulate \
  -H "Content-Type: application/json" \
  -d '{"action": "trigger_motion"}'

# Manuální trigger tampering
curl -X POST http://localhost:9201/simulate \
  -H "Content-Type: application/json" \
  -d '{"action": "trigger_tampering", "data": {"type": "cover"}}'
```

**Dostupné akce:**
- `offline` - Kamera offline
- `online` - Kamera online
- `error` - Simulace chyby
- `clear_error` - Vymazání chyby
- `connection_lost` - Ztráta spojení s NVR
- `temperature` - Změna teploty
- `storage_full` - Plné úložiště
- `clear_storage` - Vymazání úložiště
- `trigger_motion` - Manuální motion event
- `trigger_tampering` - Manuální tampering event

---

## 🔄 WebSocket Events

Připojení k WebSocket serveru pro real-time události.

```javascript
const ws = new WebSocket('ws://localhost:9201');

ws.onmessage = (event) => {
  const data = JSON.parse(event.data);
  console.log('Event:', data.type, data);
};
```

### Event typy

#### 1. connected
```json
{
  "type": "connected",
  "deviceId": "camera-01",
  "message": "Připojeno k EVOLVEO Detective POE8 SMART",
  "timestamp": "2025-11-20T22:00:00.000Z"
}
```

#### 2. heartbeat (každých 10s)
```json
{
  "type": "heartbeat",
  "deviceId": "camera-01",
  "status": "online",
  "uptime": 3600,
  "temperature": "47.2",
  "power": {
    "consumption": 12.5,
    "voltage": 48.0,
    "current": "0.260"
  },
  "error": null,
  "recording": false,
  "streamActive": false,
  "irActive": true,
  "analytics": true,
  "storage": {
    "used": 6.2,
    "available": 121.8,
    "health": 100
  },
  "timestamp": "2025-11-20T22:00:10.000Z"
}
```

#### 3. motion_detected
```json
{
  "type": "motion_detected",
  "deviceId": "camera-01",
  "event": {
    "timestamp": "2025-11-20T22:50:42.556Z",
    "confidence": "0.64",
    "zone": "Zone 1",
    "type": "motion"
  }
}
```

#### 4. line_crossing
```json
{
  "type": "line_crossing",
  "deviceId": "camera-01",
  "event": {
    "timestamp": "2025-11-20T22:51:00.000Z",
    "line": "Entry Line",
    "direction": "A->B",
    "type": "line_crossing"
  }
}
```

#### 5. intrusion_detected
```json
{
  "type": "intrusion_detected",
  "deviceId": "camera-01",
  "event": {
    "timestamp": "2025-11-20T22:52:00.000Z",
    "region": "Restricted Area",
    "action": "enter",
    "type": "intrusion"
  }
}
```

#### 6. tampering_detected
```json
{
  "type": "tampering_detected",
  "deviceId": "camera-01",
  "event": {
    "timestamp": "2025-11-20T22:53:00.000Z",
    "type": "cover",
    "severity": "high"
  }
}
```

#### 7. recording_started / recording_stopped
```json
{
  "type": "recording_started",
  "deviceId": "camera-01",
  "event": {
    "action": "start",
    "timestamp": "2025-11-20T22:50:00.000Z"
  }
}
```

#### 8. ir_enabled / ir_disabled
```json
{
  "type": "ir_enabled",
  "deviceId": "camera-01",
  "intensity": 80,
  "timestamp": "2025-11-20T19:00:00.000Z"
}
```

#### 9. stream_started / stream_stopped / stream_closed
```json
{
  "type": "stream_started",
  "deviceId": "camera-01",
  "log": {
    "type": "stream_started",
    "message": "MJPEG stream zahájen",
    "timestamp": "2025-11-20T22:54:00.000Z"
  }
}
```

#### 10. device_offline / device_online
```json
{
  "type": "device_offline",
  "deviceId": "camera-01",
  "timestamp": "2025-11-20T22:55:00.000Z"
}
```

#### 11. device_error
```json
{
  "type": "device_error",
  "deviceId": "camera-01",
  "error": "Network timeout",
  "timestamp": "2025-11-20T22:56:00.000Z"
}
```

#### 12. storage_full
```json
{
  "type": "storage_full",
  "deviceId": "camera-01",
  "timestamp": "2025-11-20T22:57:00.000Z"
}
```

#### 13. state_change
```json
{
  "type": "state_change",
  "deviceId": "camera-01",
  "log": {
    "type": "settings_updated",
    "message": "Nastavení kamery aktualizováno",
    "deviceId": "camera-01",
    "timestamp": "2025-11-20T22:58:00.000Z",
    "deviceStatus": {
      "online": true,
      "error": null,
      "temperature": 47.2,
      "uptime": 3600,
      "recording": false,
      "irActive": true,
      "streamActive": false
    }
  }
}
```

---

## 📊 Příklady použití

### Python - Monitor všech kamer

```python
import requests
import json

cameras = [f"http://localhost:{9200+i}" for i in range(1, 13)]

for camera in cameras:
    try:
        response = requests.get(f"{camera}/")
        data = response.json()
        print(f"{data['model']} - {data['mac']}")
    except Exception as e:
        print(f"Error: {e}")
```

### Python - WebSocket monitoring

```python
import asyncio
import websockets
import json

async def monitor_camera():
    uri = "ws://localhost:9201"
    async with websockets.connect(uri) as websocket:
        while True:
            message = await websocket.recv()
            data = json.loads(message)
            if data['type'] == 'motion_detected':
                print(f"Motion! Zone: {data['event']['zone']}")
            elif data['type'] == 'heartbeat':
                print(f"Temp: {data['temperature']}°C, IR: {data['irActive']}")

asyncio.run(monitor_camera())
```

### Node.js - Snapshot download

```javascript
const axios = require('axios');
const fs = require('fs');

async function downloadSnapshot(cameraPort, filename) {
    const response = await axios({
        method: 'get',
        url: `http://localhost:${cameraPort}/snapshot?width=1920&height=1080`,
        responseType: 'stream'
    });
    
    response.data.pipe(fs.createWriteStream(filename));
    console.log(`Snapshot saved to ${filename}`);
}

// Download snapshots from all cameras
for (let i = 1; i <= 12; i++) {
    downloadSnapshot(9200 + i, `camera-${i.toString().padStart(2, '0')}.jpg`);
}
```

### Bash - Monitoring script

```bash
#!/bin/bash

# Monitor všech kamer
for port in {9201..9212}; do
    echo "=== Camera port $port ==="
    
    # Status
    status=$(curl -s http://localhost:$port/status | jq -r '.state.online')
    echo "Online: $status"
    
    # Temperature
    temp=$(curl -s http://localhost:$port/diagnostics | jq -r '.environment.temperature')
    echo "Temperature: ${temp}°C"
    
    # Analytics
    motion=$(curl -s http://localhost:$port/analytics/stats | jq -r '.stats.motionDetections')
    echo "Motion events: $motion"
    
    echo ""
done
```

---

## 🔍 Časté dotazy

### Q: Jak zjistím, zda je IR aktivní?
```bash
curl http://localhost:9201/diagnostics | jq '.ir'
```

### Q: Jak změním citlivost motion detection?
```bash
curl -X POST http://localhost:9201/analytics/motion \
  -H "Content-Type: application/json" \
  -d '{"sensitivity": 90}'
```

### Q: Jak resetuji kameru?
```bash
# Simulace offline a online
curl -X POST http://localhost:9201/simulate -d '{"action":"offline"}'
sleep 2
curl -X POST http://localhost:9201/simulate -d '{"action":"online"}'
```

### Q: Jak stáhnu všechny snapshots?
```bash
for i in {1..12}; do
    port=$((9200 + i))
    curl "http://localhost:$port/snapshot" -o "camera-$(printf '%02d' $i).jpg"
done
```

### Q: Jak monitoruji storage všech kamer?
```bash
for port in {9201..9212}; do
    echo -n "Port $port: "
    curl -s http://localhost:$port/diagnostics | jq -r '.storage | "Used: \(.used)GB / \(.capacity)GB"'
done
```

---

*Dokumentace vytvořena: 20. listopadu 2025*  
*Verze: 1.0*  
*Firmware: v2.8.5*
