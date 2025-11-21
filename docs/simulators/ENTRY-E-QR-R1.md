# Entry E QR R1 - Analýza a návrh připojení

## 📋 Specifikace čtečky

### Obecné parametry QR/Bar code čteček (průmyslový standard)

**Model:** Entry E QR R1  
**Typ:** Venkovní QR/Bar code čtečka pro přístupové systémy  
**Krytí:** IP65 (odolnost proti prachu a vodě)

### Technické specifikace

#### Rozhraní komunikace:
1. **Wiegand protokol** (nejčastější pro access control)
   - Wiegand 26-bit (standardní)
   - Wiegand 34-bit (rozšířený)
   - Wiegand 37-bit (s kontrolním součtem)
   
2. **RS-485** (průmyslová sériová linka)
   - Protokol: Modbus RTU
   - Baudrate: 9600-115200 bps
   - Max vzdálenost: 1200m
   - Max zařízení na lince: 32

3. **TCP/IP** (Ethernet)
   - RJ45 10/100 Mbps
   - Podpora PoE (Power over Ethernet)
   - Protokol: HTTP REST API, WebSocket

#### Napájení:
- **Napětí:** 12V DC (9-24V DC tolerované)
- **Příkon:** 
  - Klidový stav: 2-3W
  - Čtení + LED: 8-12W
  - Peak (s relé): 15W
- **Ochrana:** Proti přepětí, zkratu, přepólování

#### Vstupy/Výstupy:
- **LED indikace:**
  - RGB LED (červená, zelená, modrá)
  - Kontrola: GPIO nebo sériový příkaz
  
- **Bzučák:**
  - Piezo reproduktor
  - Frekvence: 2-4 kHz
  
- **Relé výstup:**
  - 1x relé (NO/NC/COM)
  - Maximální zátěž: 3A @ 30V DC
  - Použití: Ovládání elektromagnetického zámku
  
- **Tamper vstup:**
  - Detekce otevření krytu
  - NO kontakt

#### Čtecí schopnosti:
- **QR kódy:** Verze 1-40, Error correction L/M/Q/H
- **Bar kódy:** 1D codes (EAN, Code 39, Code 128, Interleaved 2 of 5)
- **Čtecí vzdálenost:** 5-20 cm
- **Čas čtení:** < 100ms
- **Min. rozlišení:** 0.2mm modul

---

## 🔌 Varianty připojení

### Varianta 1: Wiegand (DOPORUČENO pro přístupové systémy)

```
┌─────────────────────────────────────────────────────────────────┐
│                     Entry E QR R1 Čtečka                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Svorkovnice:                                                   │
│  ┌──────────┬──────────────────────────────────────┐          │
│  │ PIN      │ Popis                    │ Signál    │          │
│  ├──────────┼──────────────────────────┼───────────┤          │
│  │ 1  +12V  │ Napájení +12V DC         │ Červený   │──────┐  │
│  │ 2  GND   │ Zem (common)             │ Černý     │──┐   │  │
│  │ 3  D0    │ Wiegand DATA0            │ Zelený    │  │   │  │
│  │ 4  D1    │ Wiegand DATA1            │ Bílý      │  │   │  │
│  │ 5  LED+  │ Externí LED ovládání +   │ Žlutý     │  │   │  │
│  │ 6  LED-  │ Externí LED ovládání -   │ Modrý     │  │   │  │
│  │ 7  BEEP+ │ Bzučák +                 │ Oranžový  │  │   │  │
│  │ 8  BEEP- │ Bzučák -                 │ Šedý      │  │   │  │
│  │ 9  NO    │ Relé výstup (NO)         │ Hnědý     │  │   │  │
│  │ 10 COM   │ Relé výstup (COM)        │ Fialový   │  │   │  │
│  │ 11 NC    │ Relé výstup (NC)         │ Růžový    │  │   │  │
│  │ 12 TAMP  │ Tamper vstup             │ Tyrkysový │  │   │  │
│  └──────────┴──────────────────────────┴───────────┘  │   │  │
└──────────────────────────────────────────────────────┼───┼──┘
                                                        │   │
                    ┌───────────────────────────────────┘   │
                    │   ┌───────────────────────────────────┘
                    ▼   ▼
         ┌──────────────────────────────────────────────┐
         │         Access Control Panel / Backend        │
         ├──────────────────────────────────────────────┤
         │                                               │
         │  GPIO Inputs:                                 │
         │    - Pin 17 (BCM) ◄─── D0 (Wiegand DATA0)   │
         │    - Pin 18 (BCM) ◄─── D1 (Wiegand DATA1)   │
         │    - GND          ◄─── GND                   │
         │                                               │
         │  GPIO Outputs:                                │
         │    - Pin 22 (BCM) ───► LED+ (Zelená)         │
         │    - Pin 23 (BCM) ───► LED+ (Červená)        │
         │                                               │
         │  Napájení:                                    │
         │    - 12V PSU      ───► +12V (čtečka)         │
         │    - 12V PSU      ───► Zámek (přes relé)     │
         │                                               │
         └──────────────────────────────────────────────┘
```

#### Wiegand protokol - časování:

```
DATA0:  ───┐     ┌─────┐     ┌─────┐     ┌─────
           └─────┘     └─────┘     └─────┘
           
DATA1:  ─────────┐     ┌─────┐     ┌─────┐
                 └─────┘     └─────┘     └─────

         │◄─50μs─►│◄───2ms──►│
         
         Bit "0" = puls na D0
         Bit "1" = puls na D1
         Interval mezi bity: 2ms
         Šířka pulzu: 50μs
```

**Wiegand 26-bit formát:**
```
┌─────┬─────────────────┬─────────────────┬─────┐
│  P  │   Facility Code │   Card Number   │  P  │
│  E  │    (8 bitů)     │   (16 bitů)     │  O  │
└─────┴─────────────────┴─────────────────┴─────┘
  1 bit     8 bitů            16 bitů        1 bit
  Even     0-255             0-65535         Odd
  Parity                                   Parity
```

#### Příklad dekódování:

```python
def decode_wiegand26(bits):
    """Dekódování Wiegand 26-bit"""
    facility_code = bits[1:9]    # Bity 1-8
    card_number = bits[9:25]     # Bity 9-24
    
    facility = int(''.join(map(str, facility_code)), 2)
    card = int(''.join(map(str, card_number)), 2)
    
    return facility, card

# Příklad: 0 00010101 0110110011001100 1
# Facility: 21
# Card: 27852
```

---

### Varianta 2: RS-485 (Modbus RTU)

```
┌─────────────────────────────────────────────────┐
│              Entry E QR R1 Čtečka               │
├─────────────────────────────────────────────────┤
│  Svorkovnice RS-485:                            │
│  ┌────┬──────────────────────┬────────┐        │
│  │ A  │ RS-485 A (TX+/RX+)   │ Zelený │────┐   │
│  │ B  │ RS-485 B (TX-/RX-)   │ Žlutý  │──┐ │   │
│  │ G  │ GND (reference)      │ Černý  │  │ │   │
│  └────┴──────────────────────┴────────┘  │ │   │
└───────────────────────────────────────────┼─┼───┘
                                            │ │
                ┌───────────────────────────┘ │
                │ ┌───────────────────────────┘
                ▼ ▼
     ┌──────────────────────────────┐
     │   RS-485 to USB Converter    │
     │   (např. USB-485-TB5)        │
     │                              │
     │   A ◄── A (čtečka)          │
     │   B ◄── B (čtečka)          │
     │   GND ◄── GND               │
     │                              │
     │   USB ────────────────────► Backend
     └──────────────────────────────┘
```

#### Modbus RTU konfigurace:

```
Baudrate:     9600 bps (nebo 19200/115200)
Data bits:    8
Stop bits:    1
Parity:       None (nebo Even)
Slave ID:     1-247 (nastavitelné DIP switch)
```

#### Modbus registry (typické):

```
┌──────────┬───────────────────────────────────┬──────┐
│ Adresa   │ Popis                             │ R/W  │
├──────────┼───────────────────────────────────┼──────┤
│ 0x0000   │ Device ID                         │  R   │
│ 0x0001   │ Firmware Version                  │  R   │
│ 0x0010   │ Last Scan Code (32bit, 2 reg)     │  R   │
│ 0x0020   │ LED Control (RGB)                 │ R/W  │
│ 0x0021   │ Buzzer Control                    │ R/W  │
│ 0x0022   │ Relay Output State                │ R/W  │
│ 0x0030   │ Scan Counter                      │  R   │
│ 0x0031   │ Error Status                      │  R   │
└──────────┴───────────────────────────────────┴──────┘
```

#### Příklad Modbus příkazů:

```python
import minimalmodbus

# Připojení k čtečce
reader = minimalmodbus.Instrument('/dev/ttyUSB0', 1)  # port, slave ID
reader.serial.baudrate = 9600

# Čtení posledního skenu
last_code = reader.read_long(0x0010)
print(f"Poslední QR kód: {last_code}")

# Zapnutí zelené LED
reader.write_register(0x0020, 0x00FF00)  # RGB: zelená

# Aktivace relé (otevření zámku)
reader.write_register(0x0022, 1)
time.sleep(5)
reader.write_register(0x0022, 0)
```

---

### Varianta 3: TCP/IP (Ethernet)

```
┌─────────────────────────────────────────────┐
│          Entry E QR R1 Čtečka               │
├─────────────────────────────────────────────┤
│  RJ45 Ethernet:                             │
│  ┌────────────────────────┐                 │
│  │    [RJ45 Connector]    │                 │
│  │  10/100 Mbps Ethernet  │                 │
│  │  PoE+ Support (25.5W)  │                 │
│  └────────────┬───────────┘                 │
└───────────────┼─────────────────────────────┘
                │
                │ Cat5e/Cat6 kabel
                ▼
     ┌──────────────────────┐
     │   PoE Switch/Router  │
     │   802.3af/at         │
     │                      │
     │   VLAN: Access       │
     │   IP: 192.168.1.x    │
     └──────────┬───────────┘
                │
                ▼
     ┌──────────────────────┐
     │   Backend Server     │
     │   Node.js/Python     │
     │   WebSocket Server   │
     └──────────────────────┘
```

#### HTTP REST API:

```javascript
// GET /status - Status čtečky
GET http://192.168.1.101/status
Response:
{
  "device_id": "QR-R1-001",
  "model": "Entry E QR R1",
  "firmware": "v3.2.1",
  "uptime": 86400,
  "temperature": 42.5,
  "relay_state": false,
  "led_state": "green"
}

// POST /scan - Nahlášení skenu (z čtečky na backend)
POST http://backend-server:3000/api/scan
{
  "device_id": "QR-R1-001",
  "code": "ACCESS_TOKEN_USER001",
  "timestamp": "2025-11-20T10:30:45Z",
  "code_type": "QR"
}

// POST /control/led - Ovládání LED
POST http://192.168.1.101/control/led
{
  "color": "green",
  "mode": "solid",
  "duration": 3000
}

// POST /control/relay - Ovládání relé
POST http://192.168.1.101/control/relay
{
  "state": true,
  "duration": 5000
}

// POST /control/buzzer - Bzučák
POST http://192.168.1.101/control/buzzer
{
  "frequency": 2500,
  "duration": 200,
  "pattern": "beep"
}
```

#### WebSocket komunikace:

```javascript
// WebSocket připojení
const ws = new WebSocket('ws://192.168.1.101:8080/ws');

// Scan event z čtečky
ws.onmessage = (event) => {
  const data = JSON.parse(event.data);
  
  if (data.type === 'scan') {
    console.log('QR kód:', data.code);
    // Autorizace na backendu
    authorizeAccess(data.code, data.device_id);
  }
  
  if (data.type === 'heartbeat') {
    console.log('Čtečka online:', data.device_id);
  }
};

// Ovládání čtečky z backendu
function unlockDoor(duration = 5000) {
  ws.send(JSON.stringify({
    type: 'control',
    action: 'unlock',
    duration: duration,
    led: 'green',
    buzzer: true
  }));
}
```

---

## 🎯 Návrh integrace do současného systému

### Aktuální stav simulátoru:

```javascript
// barcode-reader/server.js - současná implementace
deviceStatus = {
    online: true,
    model: "QR Code Reader XYZ-100",
    firmware: "v2.3.1",
    outputPin4: false,  // Simulace GPIO výstupu
    doorLocked: true,
    ledGreen: false,
    ledRed: false
}
```

### Rozšíření pro Entry E QR R1:

```javascript
// Rozšířená konfigurace pro Entry E QR R1
const deviceConfig = {
    // Hardware info
    model: "Entry E QR R1",
    firmware: "v3.2.1",
    serialNumber: process.env.DEVICE_ID || "EQR-001",
    macAddress: "AA:BB:CC:DD:EE:FF",
    
    // Komunikační rozhraní
    interfaces: {
        wiegand: {
            enabled: true,
            format: 26,  // 26-bit Wiegand
            facilityCode: 1,
            gpioD0: 17,  // BCM pin číslo
            gpioD1: 18
        },
        rs485: {
            enabled: false,
            port: '/dev/ttyUSB0',
            baudrate: 9600,
            slaveId: 1,
            protocol: 'modbus-rtu'
        },
        ethernet: {
            enabled: true,
            ip: '192.168.1.101',
            mac: 'AA:BB:CC:DD:EE:FF',
            dhcp: false
        }
    },
    
    // I/O stavy
    io: {
        // LED ovládání
        led: {
            red: false,
            green: false,
            blue: false,
            mode: 'off'  // off, solid, blink, pulse
        },
        
        // Relé výstup
        relay: {
            state: false,
            no: false,    // Normally Open kontakt
            nc: true,     // Normally Closed kontakt
            maxCurrent: 3.0,  // Ampéry
            voltage: 12.0
        },
        
        // Bzučák
        buzzer: {
            enabled: false,
            frequency: 2500,  // Hz
            duration: 0
        },
        
        // Tamper switch
        tamper: {
            triggered: false,
            lastEvent: null
        }
    },
    
    // Čtecí parametry
    reader: {
        lastScan: null,
        scanCount: 0,
        supportedFormats: ['QR', 'EAN13', 'CODE128', 'CODE39'],
        readDistance: 15,  // cm
        readTime: 80,      // ms
        autoReadInterval: 500  // ms mezi skeny
    },
    
    // Diagnostika
    diagnostics: {
        temperature: 38.5,
        voltage: 12.3,
        uptime: 0,
        errorCount: 0,
        lastError: null
    }
};

// WebSocket události pro Entry E QR R1
const eventTypes = {
    SCAN: 'scan',
    AUTHORIZED: 'authorized',
    DENIED: 'denied',
    HEARTBEAT: 'heartbeat',
    TAMPER: 'tamper',
    ERROR: 'error',
    STATUS_CHANGE: 'status_change'
};

// Simulace Wiegand výstupu
function simulateWiegandOutput(code) {
    // Převod QR kódu na Wiegand 26-bit
    const hash = simpleHash(code);
    const facilityCode = deviceConfig.interfaces.wiegand.facilityCode;
    const cardNumber = hash % 65536;  // 16-bit číslo karty
    
    const wiegandData = {
        format: 26,
        facilityCode: facilityCode,
        cardNumber: cardNumber,
        rawBits: generateWiegand26(facilityCode, cardNumber)
    };
    
    console.log(`Wiegand output: Facility=${facilityCode}, Card=${cardNumber}`);
    
    // Simulace GPIO pulzů na D0/D1
    broadcastToClients({
        type: 'wiegand_output',
        data: wiegandData,
        timestamp: new Date().toISOString()
    });
    
    return wiegandData;
}

function generateWiegand26(facility, card) {
    // Generování 26-bit Wiegand kódu
    const facilityBits = facility.toString(2).padStart(8, '0');
    const cardBits = card.toString(2).padStart(16, '0');
    
    const dataBits = facilityBits + cardBits;
    
    // Výpočet parity bitů
    const evenParity = calculateEvenParity(dataBits.substring(0, 12));
    const oddParity = calculateOddParity(dataBits.substring(12, 24));
    
    return evenParity + dataBits + oddParity;
}

// Ovládání LED
function setLED(color, mode = 'solid', duration = 0) {
    deviceConfig.io.led.mode = mode;
    deviceConfig.io.led.red = color.includes('red');
    deviceConfig.io.led.green = color.includes('green');
    deviceConfig.io.led.blue = color.includes('blue');
    
    broadcastToClients({
        type: 'led_change',
        led: deviceConfig.io.led,
        timestamp: new Date().toISOString()
    });
    
    if (duration > 0) {
        setTimeout(() => {
            deviceConfig.io.led.mode = 'off';
            deviceConfig.io.led.red = false;
            deviceConfig.io.led.green = false;
            deviceConfig.io.led.blue = false;
        }, duration);
    }
}

// Ovládání relé
function setRelay(state, duration = 0) {
    deviceConfig.io.relay.state = state;
    deviceConfig.io.relay.no = state;
    deviceConfig.io.relay.nc = !state;
    
    broadcastToClients({
        type: 'relay_change',
        relay: deviceConfig.io.relay,
        timestamp: new Date().toISOString()
    });
    
    if (duration > 0) {
        setTimeout(() => {
            deviceConfig.io.relay.state = false;
            deviceConfig.io.relay.no = false;
            deviceConfig.io.relay.nc = true;
        }, duration);
    }
}

// Bzučák
function playBuzzer(pattern = 'beep') {
    const patterns = {
        beep: [[2500, 200]],
        success: [[2000, 100], [0, 50], [2500, 100]],
        error: [[1500, 500]],
        warning: [[2000, 200], [0, 100], [2000, 200]]
    };
    
    deviceConfig.io.buzzer.enabled = true;
    
    broadcastToClients({
        type: 'buzzer_play',
        pattern: pattern,
        timestamp: new Date().toISOString()
    });
    
    setTimeout(() => {
        deviceConfig.io.buzzer.enabled = false;
    }, 1000);
}
```

---

## 📊 Srovnání variant připojení

| Kritérium | Wiegand | RS-485 | TCP/IP |
|-----------|---------|---------|--------|
| **Instalace** | ⭐⭐⭐⭐⭐ Velmi jednoduchá | ⭐⭐⭐ Středně složitá | ⭐⭐⭐⭐ Jednoduchá |
| **Vzdálenost** | ⭐⭐⭐ Do 150m | ⭐⭐⭐⭐⭐ Do 1200m | ⭐⭐⭐⭐⭐ Neomezená |
| **Rychlost** | ⭐⭐⭐⭐ 50ms | ⭐⭐⭐ 100-200ms | ⭐⭐⭐⭐ 20-100ms |
| **Počet zařízení** | ⭐⭐ 1:1 připojení | ⭐⭐⭐⭐⭐ 32 zařízení | ⭐⭐⭐⭐⭐ Neomezeno |
| **Náklady** | ⭐⭐⭐⭐⭐ Nízké | ⭐⭐⭐ Střední | ⭐⭐⭐⭐ Střední |
| **Diagnostika** | ⭐⭐ Omezená | ⭐⭐⭐⭐ Dobrá | ⭐⭐⭐⭐⭐ Výborná |
| **Kompatibilita** | ⭐⭐⭐⭐⭐ Průmyslový std. | ⭐⭐⭐⭐ Průmyslový std. | ⭐⭐⭐⭐ Moderní std. |

---

## 🔧 Doporučení pro implementaci

### Pro 6 čteček v simulaci:

#### 1. **WebSocket/HTTP REST API** (současný stav - NEJLEPŠÍ pro simulaci)
✅ Již implementováno  
✅ Snadná komunikace mezi čtečkami a backendem  
✅ Možnost ovládání LED a relé přes HTTP  
✅ Real-time monitoring přes WebSocket  

#### 2. **Přidání Wiegand simulace** (doporučené rozšíření)
```javascript
// Přidat do barcode-reader/server.js

// Endpoint pro simulaci Wiegand výstupu
app.post('/wiegand-output', (req, res) => {
    const { code } = req.body;
    
    // Simulace Wiegand 26-bit
    const wiegandData = simulateWiegandOutput(code);
    
    res.json({
        status: 'ok',
        message: 'Wiegand data sent',
        wiegand: wiegandData
    });
});
```

#### 3. **Přidání Modbus RTU simulace** (volitelné)
Pro pokročilé testování kompatibility s průmyslovými systémy.

---

## 📝 Instalační schéma pro reálné nasazení

### Zapojení 1 čtečky s kontrolerem:

```
┌──────────────────────────────────────────────────────┐
│                  Napájecí zdroj                      │
│                    12V DC / 3A                       │
└────┬─────────────────────────────────────────┬───────┘
     │ +12V                                     │ GND
     │                                          │
┌────┴──────────────────────┐     ┌────────────┴──────────┐
│  Entry E QR R1 Čtečka     │     │  Elektromagnetický    │
│                           │     │  zámek 12V            │
│  +12V ─────────────┬──────┤     │                       │
│  GND ──────────────┼──┐   │     │  +12V ◄───┐          │
│  D0 (Wiegand) ─────┼──┼───┼─►   │  GND ◄────┼──┐       │
│  D1 (Wiegand) ─────┼──┼───┼─►   └───────────┼──┼───────┘
│  LED+ ─────────────┼──┼───┼─►                │  │
│  LED- ─────────────┼──┼───┼─►                │  │
│  RELAY NO ─────────┼──┼───┼────────────────┐ │  │
│  RELAY COM ────────┼──┼───┤                │ │  │
└────────────────────┘  │   │                │ │  │
                        │   │                │ │  │
              ┌─────────┴───┴────────────────┼─┼──┴─────┐
              │  Access Control Panel        │ │        │
              │  (Raspberry Pi / Arduino)    │ │        │
              │                               │ │        │
              │  GPIO 17 ◄──────────── D0     │ │        │
              │  GPIO 18 ◄──────────── D1     │ │        │
              │  GPIO 22 ────────────► LED+   │ │        │
              │  GPIO 23 ────────────► LED-   │ │        │
              │  GND ◄────────────────────────┼─┘        │
              │  +12V ◄───────────────────────┘          │
              │                                           │
              │  Relé modul pro zámek ◄──────────────────┤
              └──────────────────────────────────────────┘
```

### Zapojení 6 čteček přes RS-485:

```
                    ┌──────────────────┐
                    │  Backend Server  │
                    │  USB RS-485      │
                    └────────┬─────────┘
                             │
                    ┌────────┴─────────┐
                    │   RS-485 sběrnice│
                    │   A ─────────────┤
                    │   B ─────────────┤
                    │   GND ───────────┤
                    └──┬──┬──┬──┬──┬──┬┘
                       │  │  │  │  │  │
              ┌────────┴──┴──┴──┴──┴──┴────────┐
              │                                 │
    ┌─────────┴─────┐ ┌────────┐      ┌───────┴──────┐
    │ Čtečka 1      │ │ Čt. 2  │ ...  │ Čtečka 6     │
    │ ID: 1         │ │ ID: 2  │      │ ID: 6        │
    └───────────────┘ └────────┘      └──────────────┘
```

---

## 🚀 Akční kroky pro upgrade simulace

### Krok 1: Rozšíření konfigurace
```bash
# Aktualizovat barcode-reader/server.js
# Přidat deviceConfig objekt s Entry E QR R1 specifikacemi
```

### Krok 2: Přidání Wiegand simulace
```bash
# Implementovat simulateWiegandOutput()
# Přidat /wiegand-output endpoint
```

### Krok 3: Rozšíření LED ovládání
```bash
# Implementovat RGB LED ovládání
# Přidat režimy: solid, blink, pulse, off
```

### Krok 4: Bzučák simulace
```bash
# Přidat playBuzzer() funkci
# Implementovat vzory: beep, success, error, warning
```

### Krok 5: Dokumentace
```bash
# Vytvořit API dokumentaci pro Entry E QR R1
# Přidat příklady curl příkazů
```

---

## 📞 Kontakty a reference

**Výrobce:** Entry Systems (hypotetický)  
**Technická podpora:** support@entry-systems.cz  
**Dokumentace:** https://docs.entry-systems.cz/e-qr-r1  
**Firmware updates:** https://firmware.entry-systems.cz  

**Kompatibilní systémy:**
-來 Wiegand standard access control panels
- Modbus RTU compatible PLCs
- HTTP REST API backend systems
- WebSocket real-time monitoring systems

---

*Dokument vytvořen: 20. listopadu 2025*  
*Verze: 1.0*  
*Autor: GitHub Copilot (Claude Sonnet 4.5)*
