# Klávesnice RFID 7612 - Analýza a návrh připojení

## 📋 Specifikace zařízení

### Obecné parametry RFID klávesnice 7612

**Model:** RFID Keypad 7612  
**Typ:** Venkovní voděodolná klávesnice s RFID čtečkou  
**Krytí:** IP65-IP68 (odolnost proti prachu, vodě, nárazu)  
**Materiál:** Kovové tělo (zinek/nerez), epoxidové klávesy

---

## 🔧 Technické specifikace

### Hardware

#### RFID čtečka:
- **Frekvence:** 125 kHz (EM4100/TK4100) nebo 13.56 MHz (Mifare)
- **Čtecí vzdálenost:** 3-8 cm
- **Podporované karty:**
  - EM4100, EM4102 (125 kHz)
  - TK4100, TK4102 (125 kHz)
  - Mifare Classic, Mifare DESFire (13.56 MHz)
- **Formát dat:** Wiegand 26/34/37-bit, RS-485, RS-232

#### Klávesnice:
- **Typ:** 12 tlačítek (0-9, *, #) nebo 16 tlačítek (0-9, A-F, *, #)
- **Podsvícení:** LED (modré/zelené/červené)
- **Zpětná vazba:** Bzučák, LED indikace
- **Životnost:** > 500,000 stiků na klávesu

#### Elektronika:
- **Mikrokontroler:** ARM Cortex-M nebo STM32
- **Paměť:** 
  - 1000-3000 uživatelských karet/PIN kódů
  - 10,000+ záznamů v logu
- **Režimy:** Standalone, Wiegand output, RS-485 networked

#### Napájení:
- **Napětí:** 12V DC (9-15V tolerované)
- **Příkon:**
  - Standby: 50-80 mA
  - Aktivní: 150-250 mA
  - Peak (relé + LED): 350 mA
- **Bateriové zálohování:** Volitelné (3.7V Li-ion)

#### Vstupy/Výstupy:
- **Relé výstup:** 1-2x (NO/NC/COM)
  - Max zátěž: 3A @ 30V DC / 2A @ 125V AC
  - Použití: Elektromagnetický zámek, dveřní spínač
- **Wiegand výstup:** D0, D1 (pro připojení k centrální jednotce)
- **Wiegand vstup:** D0, D1 (pro řetězení s RFID čtečkami)
- **Tamper:** Detekce odstranění z montáže
- **Door sensor:** Detekce stavu dveří (otevřeno/zavřeno)
- **Exit button:** Vstup pro tlačítko REX (request to exit)

#### Komunikační rozhraní:
1. **Wiegand 26/34-bit** (nejčastější)
2. **RS-485** (Modbus RTU, OSDP)
3. **RS-232** (konfigurace, diagnostika)
4. **TCP/IP** (pokročilé modely s Ethernet/WiFi)

---

## 🔌 Zapojení a pinout

### Standardní svorkovnice (12-pin)

```
┌────────────────────────────────────────────────────┐
│         RFID Keypad 7612 - Svorkovnice            │
├────────────────────────────────────────────────────┤
│                                                    │
│  Svorka   │ Signál          │ Popis              │
│  ────────┼─────────────────┼──────────────────── │
│  1  +12V  │ Power +12V DC   │ Červený kabel      │
│  2  GND   │ Ground/Common   │ Černý kabel        │
│  3  D0    │ Wiegand DATA0   │ Zelený kabel       │
│  4  D1    │ Wiegand DATA1   │ Bílý kabel         │
│  5  LED   │ LED Control     │ Žlutý kabel        │
│  6  BEEP  │ Buzzer Control  │ Oranžový kabel     │
│  7  BELL  │ Doorbell/Alarm  │ Modrý kabel        │
│  8  NO    │ Relay NO        │ Hnědý kabel        │
│  9  COM   │ Relay COM       │ Fialový kabel      │
│  10 NC    │ Relay NC        │ Růžový kabel       │
│  11 TAMP  │ Tamper Switch   │ Šedý kabel         │
│  12 SENS  │ Door Sensor     │ Bílý/černý kabel   │
│                                                    │
└────────────────────────────────────────────────────┘
```

### Rozšířená svorkovnice (16-pin) s RS-485

```
┌────────────────────────────────────────────────────┐
│    RFID Keypad 7612 - Extended Terminals          │
├────────────────────────────────────────────────────┤
│                                                    │
│  Svorka   │ Signál          │ Popis              │
│  ────────┼─────────────────┼──────────────────── │
│  1  +12V  │ Power +12V DC   │ Červený            │
│  2  GND   │ Ground          │ Černý              │
│  3  D0    │ Wiegand DATA0   │ Zelený             │
│  4  D1    │ Wiegand DATA1   │ Bílý               │
│  5  A     │ RS-485 A (TX+)  │ Žlutý              │
│  6  B     │ RS-485 B (TX-)  │ Modrý              │
│  7  LED   │ LED Control     │ Oranžový           │
│  8  BEEP  │ Buzzer Control  │ Růžový             │
│  9  BELL  │ Doorbell        │ Hnědý              │
│  10 NO1   │ Relay 1 NO      │ Šedý               │
│  11 COM1  │ Relay 1 COM     │ Fialový            │
│  12 NC1   │ Relay 1 NC      │ Tyrkysový          │
│  13 NO2   │ Relay 2 NO      │ Limetkový          │
│  14 COM2  │ Relay 2 COM     │ Oranžovočervený    │
│  15 TAMP  │ Tamper          │ Bílošedý           │
│  16 SENS  │ Door Sensor     │ Žlutozelený        │
│                                                    │
└────────────────────────────────────────────────────┘
```

---

## 🔄 Varianty připojení

### Varianta 1: Wiegand Standalone (DOPORUČENO)

```
┌──────────────────────────────────────────────────────────┐
│              RFID Keypad 7612                            │
│              IP65 Waterproof                             │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  +12V ─────────────┬─────────────────────┐              │
│  GND ──────────────┼──────┐              │              │
│  D0 ───────────────┼──────┼──────┐       │              │
│  D1 ───────────────┼──────┼──────┼───┐   │              │
│  NO ───────────────┼──────┼──────┼───┼───┤              │
│  COM ──────────────┼──────┼──────┼───┼───┼──┐           │
└────────────────────┼──────┼──────┼───┼───┼──┼───────────┘
                     │      │      │   │   │  │
                     │      │      │   │   │  │
              ┌──────┴──────┴──────┴───┴───┴──┴──────────┐
              │    Access Control Panel / Controller     │
              ├──────────────────────────────────────────┤
              │                                          │
              │  Power Supply:                           │
              │    12V DC ──────► +12V (keypad)         │
              │    GND ──────────► GND                   │
              │                                          │
              │  Wiegand Inputs:                         │
              │    GPIO 17 ◄───── D0 (DATA0)            │
              │    GPIO 18 ◄───── D1 (DATA1)            │
              │                                          │
              │  Relay Connection:                       │
              │    Elektromagnet. zámek 12V ◄─── NO      │
              │    12V PSU ──────────────────► COM       │
              │                                          │
              └──────────────────────────────────────────┘
```

**Vlastnosti:**
- ✅ Nejjednodušší instalace
- ✅ Kompatibilní s většinou access control panelů
- ✅ Nízká latence (~50ms)
- ✅ Spolehlivost
- ⚠️ Omezená vzdálenost (max 150m)
- ⚠️ Žádná zpětná vazba z panelu

**Použití:**
- Standalone přístupový systém
- Integrace s existujícími access control panely
- Jednodveřové instalace

---

### Varianta 2: RS-485 Network (Multi-device)

```
                    ┌──────────────────────┐
                    │   Backend Server     │
                    │   RS-485 Master      │
                    └──────────┬───────────┘
                               │
                    ┌──────────┴───────────┐
                    │   RS-485 sběrnice    │
                    │   A ─────────────────┤
                    │   B ─────────────────┤
                    │   GND ───────────────┤
                    └─┬────┬────┬────┬─────┘
                      │    │    │    │
        ┌─────────────┴┐ ┌─┴──┐ ┌──┴─┐ ┌──┴─────────┐
        │ Keypad 1     │ │ K2 │ │ K3 │ │ Keypad N   │
        │ ID: 1        │ │ID:2│ │ID:3│ │ ID: N      │
        │ Lab-01       │ │L-02│ │L-03│ │ Lab-N      │
        └──────────────┘ └────┘ └────┘ └────────────┘
```

**Vlastnosti:**
- ✅ Až 32 klávesnic na jedné lince
- ✅ Vzdálenost až 1200m
- ✅ Centralizovaná správa
- ✅ Real-time monitoring
- ✅ Obousměrná komunikace
- ⚠️ Vyžaduje RS-485 převodník
- ⚠️ Složitější konfigurace

**Konfigurace RS-485:**
```
Baudrate:     9600-115200 bps
Data bits:    8
Stop bits:    1
Parity:       None nebo Even
Slave ID:     1-247 (DIP switch na zařízení)
Protocol:     Modbus RTU nebo OSDP
```

**Modbus Registry (typické adresy):**
```
┌──────────┬───────────────────────────────────┬──────┐
│ Adresa   │ Popis                             │ R/W  │
├──────────┼───────────────────────────────────┼──────┤
│ 0x0000   │ Device ID / Slave Address         │  R   │
│ 0x0001   │ Firmware Version                  │  R   │
│ 0x0010   │ Last Card UID (32-bit, 2 reg)     │  R   │
│ 0x0012   │ Last PIN Code                     │  R   │
│ 0x0020   │ Relay 1 Control                   │ R/W  │
│ 0x0021   │ Relay 2 Control                   │ R/W  │
│ 0x0022   │ LED Control (RGB)                 │ R/W  │
│ 0x0023   │ Buzzer Control                    │ R/W  │
│ 0x0030   │ Access Log Counter                │  R   │
│ 0x0031   │ Door Status                       │  R   │
│ 0x0032   │ Tamper Status                     │  R   │
│ 0x0040   │ User Count                        │  R   │
│ 0x0041   │ Error Status                      │  R   │
└──────────┴───────────────────────────────────┴──────┘
```

---

### Varianta 3: TCP/IP Network (Modern)

```
┌───────────────────────────────────────────────────┐
│         RFID Keypad 7612 (TCP/IP)                 │
│         RJ45 Ethernet / WiFi Module               │
├───────────────────────────────────────────────────┤
│  [RJ45 Connector] nebo [WiFi Antenna]            │
│  IP: 192.168.1.201                                │
│  MAC: AA:BB:CC:DD:EE:21                           │
└─────────────────────┬─────────────────────────────┘
                      │ Cat5e/6 nebo WiFi
                      ▼
            ┌──────────────────────┐
            │   PoE Switch/Router  │
            │   VLAN: Access       │
            └──────────┬───────────┘
                       │
                       ▼
            ┌──────────────────────┐
            │   Backend Server     │
            │   HTTP REST + WS     │
            │   Port: 80/443       │
            └──────────────────────┘
```

**Vlastnosti:**
- ✅ Neomezený počet zařízení
- ✅ Neomezená vzdálenost (přes síť)
- ✅ Podpora PoE napájení
- ✅ Vzdálená konfigurace
- ✅ Real-time WebSocket události
- ✅ Cloud integrace
- ⚠️ Vyšší cena
- ⚠️ Závislost na síťové infrastruktuře

---

## 📡 Komunikační protokoly

### 1. Wiegand Protocol

#### Wiegand 26-bit formát:
```
┌─────┬─────────────────┬─────────────────┬─────┐
│  P  │   Facility Code │   Card Number   │  P  │
│  E  │    (8 bitů)     │   (16 bitů)     │  O  │
└─────┴─────────────────┴─────────────────┴─────┘
  1b        8b                 16b            1b
```

**Časování:**
```
D0:  ───┐     ┌─────┐     ┌─────
        └─────┘     └─────┘
        
D1:  ─────────┐     ┌─────┐
              └─────┘     └─────

     │◄─50μs─►│◄─2ms─►│
     
     Bit 0 = puls na D0
     Bit 1 = puls na D1
```

**Příklad dekódování:**
- RFID karta: 0x01234567
- Facility: 18
- Card: 13415
- Wiegand: 0 00010010 0011010001110111 1

---

### 2. Keyboard Input

#### PIN kód vstup:

**Formát zprávy (Wiegand):**
```
Počet cifer: 4-8 (konfigurovatelné)
Timeout: 5-10s mezi stisky
Potvrzení: Tlačítko # nebo *
Anulace: Tlačítko * (dlouhý stisk)
```

**Příklad sekvence:**
```
Uživatel stiskne: 1 → 2 → 3 → 4 → #

Klávesnice:
1. Sbírá cifry do bufferu
2. Validuje PIN lokálně (standalone režim)
   NEBO
3. Posílá PIN přes Wiegand/RS-485 do panelu
4. Čeká na autorizaci
5. Aktivuje relé při úspěchu
```

**Wiegand Keypad formát (35-bit):**
```
┌──────┬──────────────────────────────┬──────┐
│  PE  │   Keypad Data (32 bitů)      │  PO  │
└──────┴──────────────────────────────┴──────┘
  1b            32b                      1b

PIN 1234 → 0x31323334 (ASCII)
```

---

### 3. RS-485 Modbus RTU

#### Read Card Event (Function Code 0x03):
```bash
Request:  [Slave ID][0x03][Start Addr][Num Regs][CRC]
          01 03 00 10 00 02 C5 CE

Response: [Slave ID][0x03][Byte Count][Data...][CRC]
          01 03 04 12 34 56 78 XX XX
          
Card UID: 0x12345678
```

#### Control Relay (Function Code 0x06):
```bash
Request:  [Slave ID][0x06][Reg Addr][Value][CRC]
          01 06 00 20 00 01 XX XX
          
          Relay 1 ON = 0x0001
          Relay 1 OFF = 0x0000
```

---

### 4. TCP/IP REST API

#### Card Scan Event (Keypad → Backend):
```json
POST /api/scan
{
  "device_id": "keypad-7612-001",
  "timestamp": "2025-11-20T22:30:00Z",
  "type": "rfid_card",
  "card_uid": "12345678",
  "facility_code": 1,
  "card_number": 13415
}
```

#### PIN Entry Event:
```json
POST /api/verify-pin
{
  "device_id": "keypad-7612-001",
  "timestamp": "2025-11-20T22:30:05Z",
  "type": "pin_code",
  "pin": "1234",
  "hashed": false
}
```

#### Authorization Response (Backend → Keypad):
```json
POST http://192.168.1.201/control
{
  "action": "authorize",
  "access_granted": true,
  "unlock_duration": 5,
  "led_color": "green",
  "buzzer_pattern": "success"
}
```

---

## 🎯 Návrh integrace do simulátoru

### Aktuální stav simulátoru klávesnice

```javascript
// keypad/server.js - současná implementace
deviceStatus = {
    online: true,
    model: "Standalone Access Keypad",
    lastPinEntry: null,
    pinCount: 0
}
```

### Upgrade na RFID Keypad 7612

```javascript
// Konfigurace RFID Keypad 7612
const deviceConfig = {
    // Hardware info
    model: "RFID Keypad 7612",
    firmware: "v4.1.2",
    serialNumber: process.env.DEVICE_ID || "KEYPAD-7612-001",
    macAddress: generateMAC(),
    hardwareVersion: "Rev 3.0",
    
    // RFID čtečka
    rfid: {
        enabled: true,
        frequency: "125kHz",  // nebo "13.56MHz"
        supportedCards: ["EM4100", "EM4102", "TK4100", "Mifare"],
        readRange: 5,  // cm
        lastCardUID: null,
        cardCount: 0
    },
    
    // Klávesnice
    keypad: {
        type: "12-key",  // nebo "16-key"
        layout: "numeric",  // 0-9, *, #
        backlight: true,
        pinLength: {
            min: 4,
            max: 8
        },
        timeout: 10,  // sekund mezi stisky
        lastPIN: null,
        pinCount: 0
    },
    
    // Komunikační rozhraní
    interfaces: {
        wiegand: {
            enabled: true,
            format: 26,
            facilityCode: 1,
            gpioD0: 17,
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
            ip: process.env.DEVICE_IP || '192.168.1.201',
            mac: generateMAC(),
            dhcp: false,
            port: 3001
        }
    },
    
    // I/O stavy
    io: {
        // LED indikace
        led: {
            red: false,
            green: false,
            blue: false,
            backlight: true,
            brightness: 80
        },
        
        // Relé výstupy
        relay1: {
            state: false,
            no: false,
            nc: true,
            maxCurrent: 3.0,
            purpose: "door_lock"
        },
        
        relay2: {
            state: false,
            no: false,
            nc: true,
            maxCurrent: 3.0,
            purpose: "alarm"
        },
        
        // Bzučák
        buzzer: {
            enabled: false,
            frequency: 2800,
            pattern: 'off'
        },
        
        // Vstupy
        tamper: {
            triggered: false,
            lastEvent: null
        },
        
        doorSensor: {
            open: false,
            lastChange: null
        },
        
        exitButton: {
            pressed: false
        }
    },
    
    // Uživatelská databáze (standalone mode)
    users: {
        maxUsers: 3000,
        cards: new Map(),  // UID → user data
        pins: new Map(),   // PIN → user data
        count: 0
    },
    
    // Diagnostika
    diagnostics: {
        temperature: 42.5,
        voltage: 12.1,
        uptime: 0,
        errorCount: 0,
        lastError: null,
        totalScans: 0,
        successfulScans: 0,
        failedScans: 0,
        totalPINs: 0,
        successfulPINs: 0,
        failedPINs: 0
    }
};

// Simulace čtení RFID karty
function simulateRFIDScan(cardUID) {
    deviceConfig.rfid.lastCardUID = cardUID;
    deviceConfig.rfid.cardCount++;
    deviceConfig.diagnostics.totalScans++;
    
    const cardData = {
        uid: cardUID,
        uidHex: cardUID.toString(16).padStart(8, '0'),
        timestamp: new Date().toISOString(),
        scanId: generateScanId()
    };
    
    // Simulace Wiegand výstupu
    const wiegandData = cardUIDToWiegand(cardUID);
    
    logState('rfid_scan', `RFID karta načtena: ${cardData.uidHex}`);
    
    broadcastToClients({
        type: 'rfid_scan',
        deviceId: deviceConfig.serialNumber,
        card: cardData,
        wiegand: wiegandData,
        waitingForAuthorization: true
    });
    
    return cardData;
}

// Simulace zadání PIN kódu
function simulatePINEntry(pin) {
    deviceConfig.keypad.lastPIN = pin;
    deviceConfig.keypad.pinCount++;
    deviceConfig.diagnostics.totalPINs++;
    
    const pinData = {
        pin: pin,
        length: pin.length,
        timestamp: new Date().toISOString(),
        entryId: generateScanId()
    };
    
    // Simulace Wiegand keypad formátu (35-bit)
    const wiegandData = pinToWiegand(pin);
    
    logState('pin_entry', `PIN kód zadán: ${pin.replace(/./g, '*')}`);
    
    broadcastToClients({
        type: 'pin_entry',
        deviceId: deviceConfig.serialNumber,
        pin: pinData,
        wiegand: wiegandData,
        waitingForAuthorization: true
    });
    
    return pinData;
}

// Konverze RFID UID na Wiegand 26-bit
function cardUIDToWiegand(uid) {
    const facilityCode = deviceConfig.interfaces.wiegand.facilityCode;
    const cardNumber = uid % 65536;  // 16-bit card number
    
    return {
        format: 26,
        facilityCode: facilityCode,
        cardNumber: cardNumber,
        rawBits: generateWiegand26(facilityCode, cardNumber),
        timestamp: new Date().toISOString()
    };
}

// Konverze PIN na Wiegand keypad formát
function pinToWiegand(pin) {
    // 35-bit Wiegand keypad format
    const pinBytes = Buffer.from(pin, 'ascii');
    const pinHex = pinBytes.toString('hex');
    
    return {
        format: 35,
        pinLength: pin.length,
        pinData: pinHex,
        timestamp: new Date().toISOString()
    };
}

// Ovládání relé s duálním výstupem
function setRelays(relay1State, relay2State, duration = 0) {
    if (relay1State !== undefined) {
        deviceConfig.io.relay1.state = relay1State;
        deviceConfig.io.relay1.no = relay1State;
        deviceConfig.io.relay1.nc = !relay1State;
    }
    
    if (relay2State !== undefined) {
        deviceConfig.io.relay2.state = relay2State;
        deviceConfig.io.relay2.no = relay2State;
        deviceConfig.io.relay2.nc = !relay2State;
    }
    
    logState('relay_control', `Relé 1: ${relay1State}, Relé 2: ${relay2State}`);
    
    broadcastToClients({
        type: 'relay_change',
        relay1: deviceConfig.io.relay1,
        relay2: deviceConfig.io.relay2,
        timestamp: new Date().toISOString()
    });
    
    if (duration > 0) {
        setTimeout(() => {
            if (relay1State) {
                deviceConfig.io.relay1.state = false;
                deviceConfig.io.relay1.no = false;
                deviceConfig.io.relay1.nc = true;
            }
            if (relay2State) {
                deviceConfig.io.relay2.state = false;
                deviceConfig.io.relay2.no = false;
                deviceConfig.io.relay2.nc = true;
            }
        }, duration);
    }
}
```

---

## 📊 Srovnání variant připojení

| Kritérium | Wiegand | RS-485 | TCP/IP |
|-----------|---------|--------|--------|
| **Instalace** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐ |
| **Vzdálenost** | 150m | 1200m | Neomezená |
| **Počet zařízení** | 1:1 | 32 | Neomezeno |
| **Rychlost** | 50ms | 100-200ms | 20-100ms |
| **Konfigurace** | Jednoduchá | Střední | Složitá |
| **Diagnostika** | ⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **Náklady** | Nízké | Střední | Vyšší |
| **RFID + PIN** | ✅ | ✅ | ✅ |
| **Standalone mode** | ✅ | ✅ | ✅ |

---

## 🔧 Doporučení pro 2 klávesnice v simulaci

### Současný stav:
- 2x klávesnice na portech 9401-9402
- WebSocket + HTTP REST API
- PIN entry s backend autorizací

### Upgrade na RFID Keypad 7612:

**Doporučení: TCP/IP s WebSocket** ✅

**Důvody:**
1. ✅ Již implementováno v současném simulátoru
2. ✅ Snadná integrace s backendem
3. ✅ Podpora RFID + PIN ve stejném API
4. ✅ Real-time monitoring
5. ✅ Rozšiřitelnost na více zařízení

---

## 📝 Instalační schéma

### Zapojení s kontrolerem:

```
┌────────────────────────────────────────────────────┐
│          RFID Keypad 7612                          │
│          IP65 Waterproof                           │
├────────────────────────────────────────────────────┤
│                                                    │
│  Napájení:        +12V ◄───┐                      │
│                   GND ◄────┼─┐                    │
│                             │ │                    │
│  Wiegand:         D0 ──────┼─┼──►                 │
│                   D1 ──────┼─┼──►                 │
│                             │ │                    │
│  Relé 1 (zámek):  NO ──────┼─┼──►                 │
│                   COM ◄────┼─┘                    │
│                             │                      │
│  Relé 2 (alarm):  NO ──────┼───►                  │
│                   COM ◄────┘                      │
│                                                    │
└────────────────────────────────────────────────────┘
                    │
         ┌──────────┴──────────┐
         │  Access Controller  │
         │  (Raspberry Pi)     │
         ├─────────────────────┤
         │  GPIO 17 ◄── D0     │
         │  GPIO 18 ◄── D1     │
         │  +12V ───► Power    │
         │  GND ────► Ground   │
         └─────────────────────┘
```

---

## 🧪 Testovací scénáře

### Scénář 1: RFID karta
```bash
# 1. Přiložení karty
curl -X POST http://localhost:9401/rfid-scan \
  -d '{"card_uid":"12345678"}'

# 2. Backend autorizace
curl -X POST http://localhost:9401/authorize \
  -d '{"scanId":"abc","authorized":true,"duration":5}'
```

### Scénář 2: PIN kód
```bash
# 1. Zadání PIN
curl -X POST http://localhost:9401/pin-entry \
  -d '{"pin":"1234"}'

# 2. Backend autorizace
curl -X POST http://localhost:9401/authorize \
  -d '{"entryId":"xyz","authorized":true,"duration":5}'
```

### Scénář 3: RFID + PIN (dual authentication)
```bash
# 1. Přiložení karty
curl -X POST http://localhost:9401/rfid-scan \
  -d '{"card_uid":"12345678"}'

# 2. Zadání PIN
curl -X POST http://localhost:9401/pin-entry \
  -d '{"pin":"1234"}'

# 3. Backend ověří obojí
curl -X POST http://localhost:9401/authorize \
  -d '{"dual_auth":true,"authorized":true,"duration":5}'
```

---

*Dokument vytvořen: 20. listopadu 2025*  
*Verze: 1.0*  
*Autor: GitHub Copilot (Claude Sonnet 4.5)*
