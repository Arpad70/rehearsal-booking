# EVOLVEO Detective POE8 SMART - Analýza a návrh připojení

## 📋 Předpokládané specifikace (8MP POE IP kamera antivandal)

**Model:** EVOLVEO Detective POE8 SMART  
**Typ:** IP kamera antivandal s POE napájením  
**Rozlišení:** 8 Megapixelů (3840×2160 - 4K UHD)

---

## 🔧 Technické specifikace (průmyslový standard 8MP POE)

### Sensor a obraz

#### Image Sensor:
- **Typ:** 1/2.5" Progressive Scan CMOS
- **Rozlišení:** 8MP (3840×2160)
- **Minimální osvětlení:**
  - Barevné: 0.01 Lux @ F1.6
  - ČB: 0.001 Lux @ F1.6
  - 0 Lux s IR LED
- **S/N ratio:** ≥ 52 dB
- **IR vzdálenost:** 20-30m (typicky 6× IR LED)
- **WDR:** 120 dB (Wide Dynamic Range)

#### Objektiv:
- **Typ:** Fixed focal / Motorized varifocal
- **Ohnisková vzdálenost:** 2.8mm / 2.8-12mm
- **Apertura:** F1.6-F2.8
- **Zorný úhel:**
  - 2.8mm: 110° (H) / 58° (V)
  - 12mm: 30° (H) / 17° (V)
- **IR-Cut filter:** Automatický mechanický (ICR)

### Video

#### Komprese:
- **H.265+** / H.265 / **H.264+** / H.264 / MJPEG
- **Smart Codec:** Inteligentní komprese pro úsporu bandwidth

#### Framerate:
- **Main Stream:**
  - 8MP (3840×2160) @ 20fps
  - 5MP (2592×1944) @ 25fps
  - 4MP (2560×1440) @ 30fps
- **Sub Stream:**
  - 720p (1280×720) @ 25fps
  - D1 (704×576) @ 25fps

#### Video funkce:
- **ROI (Region of Interest):** Až 8 oblastí
- **Privacy Mask:** Až 8 oblastí
- **OSD:** Datum, čas, název kamery
- **Image rotation:** 0°/90°/180°/270°
- **Mirror:** Horizontální/Vertikální/Oba
- **BLC / HLC / DWDR**
- **3D DNR (Digital Noise Reduction)**
- **Smart IR:** Automatická regulace IR intenzity

### Audio (volitelné)

- **Komprese:** G.711a / G.711μ / G.726 / AAC
- **Sampling rate:** 8 kHz / 16 kHz
- **Audio input:** 1× Line In / Built-in microphone
- **Audio output:** 1× Line Out

### Konstrukce (Antivandal)

#### Materiál:
- **Housing:** Kovový (hliník/zinek)
- **Dome cover:** Polykarbonát (PC) / PMMA
- **Vandal-proof:** IK10 (20 Joules nárazová odolnost)
- **Weatherproof:** IP67 (prach a voda)

#### Rozměry (typické):
- **Průměr:** Ø140mm
- **Výška:** 95-120mm
- **Hmotnost:** 600-800g
- **Montáž:** Strop/Stěna (3-axis gimbal)

### Síťové funkce

#### Protokoly:
- **ONVIF Profile S/G/T** (kompatibilní s třetími stranami)
- **RTSP / RTMP / HTTP / HTTPS**
- **TCP/IP, UDP, ICMP, DHCP, DNS**
- **NTP, SMTP, FTP, SFTP**
- **UPnP, DDNS, PPPoE**
- **IPv4 / IPv6**
- **QoS, SNMP**

#### Síťové rozhraní:
- **Ethernet:** 1× RJ45 10/100 Mbps
- **PoE:** IEEE 802.3af (max 12.95W)
- **Alternative power:** DC 12V ±10%

### Inteligentní funkce (AI)

#### Motion Detection:
- **Zóny:** Až 8 oblastí
- **Citlivost:** 0-100
- **Scheduling:** 24/7 nebo podle kalendáře

#### Line Crossing:
- **Směr:** A→B, B→A, Both
- **Až 4 linie**

#### Intrusion Detection:
- **Až 4 oblasti**
- **Enter / Exit / Appear / Disappear**

#### Advanced Analytics (volitelné):
- **Face Detection:** Detekce obličejů
- **People Counting:** Počítání osob
- **Heat Map:** Teplotní mapa pohybu
- **Tampering Detection:** Detekce manipulace s kamerou
- **Audio Detection:** Detekce zvuku (křik, rozbití skla)

### Alarm & Events

#### Alarm Input:
- **1× GPIO** (optional)
- **Typy:** NO (Normally Open) / NC (Normally Closed)

#### Alarm Output:
- **1× Relay** (optional)
- **Zatížení:** 30V DC / 1A, 125V AC / 0.5A

#### Triggery:
- Motion Detection
- Line Crossing
- Intrusion Detection
- Audio Detection
- Tampering
- Network Disconnect
- Storage Full
- Storage Error

#### Actions:
- **Email notification** (s snapshot)
- **FTP/SFTP upload**
- **HTTP POST**
- **Relay aktivace**
- **Push notifikace** (mobilní app)
- **Video záznam** (SD card / NVR)

### Storage

#### Local Storage:
- **MicroSD/SDHC/SDXC:** Až 256GB
- **Recording:** Motion / Schedule / Alarm / Manual
- **Overwrite:** Circular recording (ANR)

#### Network Storage:
- **NAS (NFS / SMB/CIFS)**
- **Cloud storage** (volitelné)

### Napájení

#### PoE (doporučeno):
- **Standard:** IEEE 802.3af
- **Napětí:** 48V DC ±10%
- **Max spotřeba:** 12.95W
- **Výhody:**
  - Jedno kabel (data + napájení)
  - Vzdálenost až 100m
  - Centralizovaný UPS backup

#### DC Power:
- **Napětí:** 12V DC ±10%
- **Spotřeba:** Max 10W
- **Konektor:** DC jack (5.5mm/2.1mm)

### Provozní podmínky

#### Teplota:
- **Provozní:** -30°C ~ +60°C
- **Storage:** -40°C ~ +70°C
- **Humidity:** 10% ~ 90% RH (non-condensing)

---

## 🔌 Varianty připojení

### Varianta 1: PoE Switch (DOPORUČENO) ✅

```
┌──────────────────────────────────────────────────┐
│     EVOLVEO Detective POE8 SMART                 │
│     IP: 192.168.1.211 (Camera-1)                 │
├──────────────────────────────────────────────────┤
│                                                  │
│  RJ45 (PoE) ─────────────────────┐              │
│                                   │              │
└───────────────────────────────────┼──────────────┘
                                    │
                       Cat5e/6 (max 100m)
                                    │
                    ┌───────────────┴──────────────┐
                    │   PoE Switch                 │
                    │   (8-16 portů 802.3af)       │
                    ├──────────────────────────────┤
                    │                              │
                    │  Port 1-12: PoE Cameras      │
                    │  Port 13-14: Uplink (NVR)    │
                    │  Port 15-16: Management      │
                    │                              │
                    │  Power: 120W-240W total      │
                    └──────────┬───────────────────┘
                               │
                    ┌──────────┴───────────────────┐
                    │   Network Video Recorder     │
                    │   nebo Backend Server        │
                    │   IP: 192.168.1.10           │
                    └──────────────────────────────┘
```

**Výhody:**
- ✅ Nejjednodušší instalace
- ✅ Jeden kabel na kameru
- ✅ Centralizované napájení
- ✅ UPS backup pro celý systém
- ✅ Vzdálenost až 100m
- ✅ Manageable switch monitoring

**Nevýhody:**
- ⚠️ Vyšší pořizovací náklady
- ⚠️ Single point of failure (switch)

**Použití:**
- Nová instalace
- Více kamer (6-16)
- Profesionální systémy

---

### Varianta 2: PoE Injector

```
┌────────────────────────────────┐
│  EVOLVEO Detective POE8 SMART  │
│  IP: 192.168.1.211             │
└────────────┬───────────────────┘
             │ Cat5e/6
             │ (max 100m)
             ▼
┌────────────────────────────────┐
│    PoE Injector                │
│    IEEE 802.3af                │
├────────────────────────────────┤
│  Data In ◄───┐                 │
│  PoE Out ────┘                 │
│  Power: 48V DC                 │
└────────────┬───────────────────┘
             │
             │ Non-PoE Ethernet
             ▼
┌────────────────────────────────┐
│    Standard Network Switch     │
│    nebo Router                 │
└────────────────────────────────┘
```

**Výhody:**
- ✅ Levnější než PoE switch
- ✅ Flexibilní (přidání do existující sítě)
- ✅ Jednoduchá instalace

**Nevýhody:**
- ⚠️ Extra zařízení na kameru
- ⚠️ Více power adaptérů

**Použití:**
- Retrofit (upgrade existující kamery)
- 1-3 kamery
- Dočasná instalace

---

### Varianta 3: DC Power + Ethernet

```
┌────────────────────────────────┐
│  EVOLVEO Detective POE8 SMART  │
│  IP: 192.168.1.211             │
├────────────────────────────────┤
│  RJ45 ─────────┐               │
│  DC 12V ───────┼───┐           │
└────────────────┼───┼───────────┘
                 │   │
      Cat5e/6 ───┘   └─── DC 12V 2A
                 │        Power Supply
                 │
      ┌──────────┴─────────────┐
      │   Network Switch       │
      │   (non-PoE)            │
      └────────────────────────┘
```

**Výhody:**
- ✅ Nejlevnější
- ✅ Standard Ethernet switch
- ✅ Žádné PoE požadavky

**Nevýhody:**
- ⚠️ 2 kabely na kameru
- ⚠️ Komplikovaná instalace
- ⚠️ Náchylnost na výpadky napájení

**Použití:**
- Budget instalace
- Krátké vzdálenosti (<30m)
- Existující DC zdroje

---

## 📡 Síťová architektura

### Topologie sítě (12 kamer)

```
                    ┌──────────────────────┐
                    │   Backend Server     │
                    │   192.168.1.10       │
                    │   HTTP REST + WS     │
                    └──────────┬───────────┘
                               │
                    ┌──────────┴───────────┐
                    │   Core Switch        │
                    │   VLAN: Cameras (10) │
                    │   VLAN: Access (20)  │
                    └──────────┬───────────┘
                               │
            ┌──────────────────┴──────────────────┐
            │                                     │
   ┌────────┴─────────┐              ┌───────────┴────────┐
   │  PoE Switch 1    │              │  PoE Switch 2      │
   │  Lab 01-06       │              │  Lab 07-12         │
   │  8-port 802.3af  │              │  8-port 802.3af    │
   └────────┬─────────┘              └───────────┬────────┘
            │                                     │
  ┌─────────┴─────────┐                 ┌────────┴─────────┐
  │                   │                 │                  │
Camera-01      Camera-02        Camera-07         Camera-08
.211           .212             .217              .218
```

### VLAN Design

**VLAN 10 - Cameras:**
- Subnet: 192.168.1.0/24
- Gateway: 192.168.1.1
- IP Range: 192.168.1.201-240 (cameras)
- Účel: Izolace kamer od hlavní sítě

**VLAN 20 - Access Control:**
- Subnet: 192.168.2.0/24
- Gateway: 192.168.2.1
- IP Range: 192.168.2.101-150
- Účel: QR readers, keypads

**VLAN 30 - Management:**
- Subnet: 192.168.3.0/24
- Gateway: 192.168.3.1
- IP Range: 192.168.3.10-50
- Účel: Backend, NVR, switches

---

## 🔧 Konfigurace kamery

### Základní nastavení

#### Síťové nastavení:
```bash
# IP Configuration
IP Address:     192.168.1.211
Subnet Mask:    255.255.255.0
Gateway:        192.168.1.1
DNS Primary:    8.8.8.8
DNS Secondary:  8.8.4.4

# Port Configuration
HTTP Port:      80
HTTPS Port:     443
RTSP Port:      554
ONVIF Port:     8080
```

#### Video Stream:
```bash
# Main Stream (pro záznam)
Resolution:     3840×2160 (8MP)
Framerate:      20 fps
Bitrate:        8192 kbps (8 Mbps)
GOP:            50
Codec:          H.265

# Sub Stream (pro monitoring)
Resolution:     1280×720 (720p)
Framerate:      25 fps
Bitrate:        1024 kbps (1 Mbps)
GOP:            25
Codec:          H.264
```

#### Image Enhancement:
```bash
WDR:            Enabled (120dB)
3D DNR:         Level 50/100
BLC:            Disabled
HLC:            Disabled
Smart IR:       Enabled
IR Cut Filter:  Auto
Defog:          Auto
```

### Advanced nastavení

#### Motion Detection:
```json
{
  "enabled": true,
  "sensitivity": 80,
  "regions": [
    {
      "name": "Entry Door",
      "coordinates": [[100,100], [500,100], [500,400], [100,400]],
      "threshold": 80
    },
    {
      "name": "Window Area",
      "coordinates": [[600,100], [1000,100], [1000,400], [600,400]],
      "threshold": 70
    }
  ],
  "schedule": {
    "type": "24/7",
    "days": ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
    "timeRanges": [["00:00", "23:59"]]
  },
  "actions": {
    "email": true,
    "ftp": false,
    "relay": false,
    "record": true
  }
}
```

#### Line Crossing:
```json
{
  "enabled": true,
  "lines": [
    {
      "name": "Entry Line",
      "points": [[200, 500], [800, 500]],
      "direction": "both",
      "sensitivity": 80
    }
  ]
}
```

---

## 🌐 API Integrace

### ONVIF Discovery

```bash
# Discover kamery v síti
onvif-device-tool discover
```

### RTSP Stream URL

```bash
# Main Stream (8MP)
rtsp://admin:password@192.168.1.211:554/stream1

# Sub Stream (720p)
rtsp://admin:password@192.168.1.211:554/stream2

# RTSP over HTTP (firewall friendly)
rtsp://admin:password@192.168.1.211:80/stream1
```

### HTTP API (ONVIF)

```bash
# Get Device Information
curl -u admin:password \
  http://192.168.1.211:80/onvif/device_service

# Get Stream URI
curl -u admin:password \
  -d "profile=MainStream" \
  http://192.168.1.211:80/onvif/media_service/GetStreamUri

# Snapshot
curl -u admin:password \
  http://192.168.1.211:80/onvif/snapshot.jpg
```

---

## 💻 Návrh simulátoru EVOLVEO Detective POE8 SMART

### Upgrade ip-camera/server.js

```javascript
const deviceConfig = {
    // Hardware info
    model: "EVOLVEO Detective POE8 SMART",
    firmware: "v2.8.5",
    serialNumber: process.env.DEVICE_ID || "CAMERA-01",
    macAddress: generateMAC(),
    hardwareVersion: "Rev 1.0",
    manufacturer: "EVOLVEO",
    
    // Image sensor
    sensor: {
        type: "1/2.5\" Progressive Scan CMOS",
        resolution: "8MP (3840×2160)",
        minIllumination: "0.01 Lux (Color) / 0 Lux with IR",
        wdr: "120 dB",
        snRatio: 52  // dB
    },
    
    // Lens
    lens: {
        type: "Fixed focal",
        focalLength: 2.8,  // mm
        aperture: "F1.6",
        fov: {
            horizontal: 110,  // degrees
            vertical: 58
        },
        irCut: "Auto mechanical ICR"
    },
    
    // Video
    video: {
        compression: ["H.265+", "H.265", "H.264+", "H.264", "MJPEG"],
        mainStream: {
            resolution: "3840x2160",
            framerate: 20,
            bitrate: 8192,  // kbps
            codec: "H.265"
        },
        subStream: {
            resolution: "1280x720",
            framerate: 25,
            bitrate: 1024,
            codec: "H.264"
        }
    },
    
    // IR Illumination
    ir: {
        enabled: true,
        ledCount: 6,
        distance: 30,  // meters
        wavelength: 850,  // nm
        smartIR: true,
        currentIntensity: 0  // 0-100%
    },
    
    // Construction (Antivandal)
    construction: {
        type: "Dome",
        material: "Metal housing + PC dome",
        vandal: "IK10 (20 Joules)",
        weatherproof: "IP67",
        dimensions: {
            diameter: 140,  // mm
            height: 95
        },
        weight: 700  // grams
    },
    
    // Network
    network: {
        interface: "10/100 Mbps Ethernet",
        protocols: ["ONVIF Profile S/G/T", "RTSP", "HTTP", "HTTPS"],
        poe: "IEEE 802.3af (max 12.95W)",
        ip: process.env.DEVICE_IP || "192.168.1.211",
        mac: generateMAC(),
        rtsp: {
            port: 554,
            mainStreamPath: "/stream1",
            subStreamPath: "/stream2"
        },
        onvif: {
            enabled: true,
            port: 8080,
            profile: "S/G/T"
        }
    },
    
    // AI Analytics
    analytics: {
        motionDetection: {
            enabled: true,
            sensitivity: 80,
            regions: []
        },
        lineCrossing: {
            enabled: false,
            lines: []
        },
        intrusion: {
            enabled: false,
            regions: []
        },
        faceDetection: {
            enabled: false
        },
        tampering: {
            enabled: true,
            sensitivity: 70
        }
    },
    
    // Storage
    storage: {
        type: "MicroSD",
        capacity: 128,  // GB
        used: 0,
        available: 128,
        recording: false
    },
    
    // Power
    power: {
        poe: {
            enabled: true,
            standard: "IEEE 802.3af",
            voltage: 48,  // V DC
            current: 0.27,  // A
            power: 12.95  // W
        },
        dc: {
            enabled: false,
            voltage: 12,
            current: 0.83,
            power: 10
        }
    },
    
    // Diagnostics
    diagnostics: {
        temperature: 45.0 + Math.random() * 5,  // °C
        uptime: 0,
        bitrate: 0,  // Current bitrate
        framerate: 0,  // Current FPS
        errors: 0,
        lastSnapshot: null
    }
};
```

---

## 📊 Srovnání s existujícími zařízeními

| Funkce | Entry E QR R1 | RFID Keypad 7612 | EVOLVEO POE8 | Shelly Pro EM |
|--------|---------------|------------------|--------------|---------------|
| **Typ** | QR/Barcode čtečka | RFID keypad | IP kamera 8MP | Energy meter |
| **Komunikace** | TCP/IP (HTTP+WS) | TCP/IP (HTTP+WS) | TCP/IP (RTSP+ONVIF) | TCP/IP (HTTP+WS) |
| **Napájení** | 12V DC | 12V DC | **PoE 802.3af** | 230V AC |
| **Antivandal** | ❌ | IP65 | **IK10 + IP67** | ❌ |
| **Venkovní** | ❌ | ✅ | **✅** | ✅ (DIN rail) |
| **AI funkce** | ❌ | ❌ | **✅ Motion/Line** | ❌ |
| **Záznam** | ❌ | ❌ | **✅ MicroSD** | ❌ |
| **Noční režim** | ❌ | ❌ | **✅ IR 30m** | ❌ |
| **ONVIF** | ❌ | ❌ | **✅ Profile S/G/T** | ❌ |
| **MAC adresa** | AA:BB:CC:DD:EE:xx | AA:BB:CC:DD:FF:xx | **AA:BB:CC:DD:CC:xx** | AA:BB:CC:DD:AA:xx |

---

## 🎯 Doporučení pro integraci do simulátoru

### Priorita 1: PoE napájení ✅
- Simulace PoE switch připojení
- Monitoring power consumption (12.95W)
- UPS backup simulace

### Priorita 2: RTSP streaming ✅
- HTTP REST pro snapshot
- RTSP URL pro video stream
- Simulace různých rozlišení (8MP/720p)

### Priorita 3: ONVIF kompatibilita ✅
- Device discovery
- Stream URI poskytnutí
- Event notifikace

### Priorita 4: AI analytics ✅
- Motion detection zóny
- Line crossing events
- Tampering detection

### Priorita 5: Storage management ✅
- MicroSD status monitoring
- Recording start/stop
- Circular recording (ANR)

---

*Dokument vytvořen: 20. listopadu 2025*  
*Verze: 1.0*  
*Autor: GitHub Copilot (Claude Sonnet 4.5)*
