# Soundcraft Ui24R Mixer Simulator

Simulátor digitálního mixážního pultu Soundcraft Ui24R s REST API pro dálkové ovládání a ukládání nastavení.

## Vlastnosti

### Hardware simulace
- **Model**: Soundcraft Ui24R
- **Firmware**: 3.5.8328
- **Vstupy**: 24 kanálů
  - 10x XLR/TRS combo (mic/line)
  - 10x XLR (mic)
  - 2x line vstup
  - 2x USB digitální vstup
- **Aux mixy**: 10 pomocných mixů
- **FX procesory**: 4 efektové jednotky
  - 2x Lexicon reverb (Hall, Plate)
  - 1x Stereo delay
  - 1x Chorus
- **Main LR**: Stereo hlavní mix
- **Recording**: 24-track USB recording

### Funkce kanálového stripu
- **Preamp**: Gain (-12 až +60 dB), phantom +48V, phase
- **HPF**: High-pass filter (80-500 Hz)
- **Gate**: Threshold, range, attack, release
- **Compressor**: Threshold, ratio, attack, release, makeup gain (DBX typ)
- **EQ**: 4-band parametrický (Low, Low-Mid, High-Mid, High)
- **Fader**: 0-100% (-inf až +10 dB)
- **Pan**: L-C-R
- **Sends**: 10x Aux + 4x FX
- **Mute/Solo**: Samostatné ovládání

### Scene Management (Cue Recall)
- Ukládání kompletního nastavení mixu jako "scene"
- Rychlé přepínání mezi scénami
- Popis a metadata pro každou scénu
- Export/import scén

### Show Files
- Kompletní show file obsahující všechny scény
- JSON formát pro snadnou editaci
- Upload z backend systému
- Download pro zálohu

### Bezpečnost
- **Webové rozhraní**: Možnost povolit/zakázat přístup přes web
- **Backend-only režim**: Pouze backend server může ovládat mixer
- **Heslo**: Ochrana heslem (Bearer token)
- **IP whitelist**: Povolené IP adresy
- **Izolace sítě**: Připraveno pro VLAN/firewall

### Real-time komunikace
- **WebSocket**: Živé aktualizace stavu mixéru
- **Heartbeat**: Pravidelné zprávy o stavu (každých 10s)
- **Events**: Notifikace o změnách (scene loaded, channel updated, atd.)

## API Endpointy

### Informace o zařízení

#### GET /
- Základní info a seznam dostupných endpointů

#### GET /api/info
- Detailní informace o mixéru, firmwaru, síti

#### GET /api/state
- Kompletní stav mixéru (všechny kanály, busses, efekty)

### Kanály

#### GET /api/channels
- Seznam všech 24 kanálů s nastavením

#### GET /api/channel/:id
- Detail konkrétního kanálu (id = 1-24)

#### POST /api/channel/:id
- Nastavení kanálu
```json
{
  "name": "Vocal 1",
  "mute": false,
  "gain": 12.0,
  "fader": 0.8,
  "pan": 0.5,
  "eq": {
    "enabled": true,
    "low": { "freq": 100, "gain": 2, "q": 1.0 },
    "lowMid": { "freq": 500, "gain": -1, "q": 1.2 }
  },
  "compressor": {
    "enabled": true,
    "threshold": -15,
    "ratio": 3.0,
    "attack": 10,
    "release": 100
  }
}
```

### Scény (Cue Recall)

#### GET /api/scenes
- Seznam všech uložených scén

#### POST /api/scenes/save
- Uložit aktuální stav jako scénu
```json
{
  "name": "Band - Rock Setup",
  "description": "Rock band: 2 vocals, drums, bass, 2 guitars"
}
```

#### POST /api/scenes/load/:name
- Načíst scénu (name = název scény)

#### DELETE /api/scenes/delete/:name
- Smazat scénu

### Show Files

#### GET /api/shows
- Seznam všech show files

#### POST /api/shows/upload
- Upload show file z backendu
- Content-Type: multipart/form-data
- Field: file (JSON soubor)

Formát show file:
```json
{
  "name": "Festival 2024",
  "description": "Main stage setup",
  "scenes": [
    {
      "name": "Band 1",
      "description": "Opening band",
      "data": {
        "channels": [...],
        "auxBusses": [...],
        "fxProcessors": [...],
        "mainLR": {...}
      }
    }
  ],
  "globalSettings": {
    "sampleRate": 48000,
    "recordingEnabled": true
  }
}
```

#### GET /api/shows/download/:name
- Stáhnout show file (JSON)

#### POST /api/shows/load/:name
- Načíst show file (nahradí všechny scény)
```json
{
  "loadFirstScene": true
}
```

### Bezpečnost

#### GET /api/security
- Stav zabezpečení

#### POST /api/security/web/enable
- Povolit webový přístup
```json
{
  "password": "admin123"
}
```

#### POST /api/security/web/disable
- Zakázat webový přístup (pouze backend může ovládat)

#### POST /api/security/password
- Nastavit heslo
```json
{
  "password": "admin123"
}
```

#### POST /api/security/backend-only
- Backend-only režim
```json
{
  "enabled": true
}
```

### Ovládání

#### POST /api/mute
- Ztlumit/obnovit všechny kanály
```json
{
  "muted": true
}
```

#### POST /api/reset
- Reset na výchozí nastavení

## WebSocket API

### Připojení
```
ws://[host]/
```

### Zprávy od serveru

#### connected
```json
{
  "type": "connected",
  "deviceId": "mixer-1",
  "message": "Connected to Soundcraft Ui24R",
  "timestamp": "2024-01-15T10:30:00Z"
}
```

#### heartbeat
```json
{
  "type": "heartbeat",
  "deviceId": "mixer-1",
  "uptime": 3600,
  "temperature": 48.5,
  "currentScene": "Band - Rock Setup",
  "timestamp": "2024-01-15T10:30:00Z"
}
```

#### scene_loaded
```json
{
  "type": "scene_loaded",
  "deviceId": "mixer-1",
  "scene": "Band - Rock Setup",
  "timestamp": "2024-01-15T10:30:00Z"
}
```

#### channel_updated
```json
{
  "type": "channel_updated",
  "deviceId": "mixer-1",
  "channelId": 1,
  "channel": { ... },
  "timestamp": "2024-01-15T10:30:00Z"
}
```

## Docker

### Environment Variables

- `PORT` - HTTP port (default: 80)
- `DEVICE_ID` - Jedinečné ID mixéru (default: mixer-1)
- `DEVICE_IP` - IP adresa simulovaného zařízení (default: 192.168.1.100)
- `BACKEND_IP` - IP adresa backend serveru (pro backend-only režim)

### docker-compose.yml

```yaml
soundcraft-mixer:
  build: ./soundcraft-mixer
  container_name: soundcraft-mixer
  environment:
    - PORT=80
    - DEVICE_ID=mixer-1
    - DEVICE_IP=192.168.1.100
    - BACKEND_IP=backend
  ports:
    - "9301:80"
  networks:
    - simulator-network
  restart: unless-stopped
```

### Spuštění

```bash
# Build
cd soundcraft-mixer
docker build -t soundcraft-mixer .

# Nebo přes docker-compose
cd ..
docker-compose up -d soundcraft-mixer
```

## Příklady použití

### 1. Upload show file z backendu

```bash
curl -X POST http://localhost:9301/api/shows/upload \
  -F "file=@festival-2024.show"
```

### 2. Načtení show (všechny scény kapely)

```bash
curl -X POST http://localhost:9301/api/shows/load/Festival%202024 \
  -H "Content-Type: application/json" \
  -d '{"loadFirstScene": true}'
```

### 3. Načtení konkrétní scény

```bash
curl -X POST http://localhost:9301/api/scenes/load/Band%20-%20Rock%20Setup
```

### 4. Zakázání webového přístupu (security)

```bash
# Povolit pouze backend
curl -X POST http://localhost:9301/api/security/backend-only \
  -H "Content-Type: application/json" \
  -d '{"enabled": true}'

# Zakázat web rozhraní
curl -X POST http://localhost:9301/api/security/web/disable
```

### 5. Nastavení kanálu

```bash
curl -X POST http://localhost:9301/api/channel/1 \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Lead Vocal",
    "gain": 15,
    "fader": 0.85,
    "compressor": {
      "enabled": true,
      "threshold": -18,
      "ratio": 4.0
    }
  }'
```

### 6. Uložení aktuálního nastavení jako scény

```bash
curl -X POST http://localhost:9301/api/scenes/save \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Soundcheck",
    "description": "Initial soundcheck setup"
  }'
```

## Integrace s backendem

### Workflow pro kapely

1. **Backend ukládá show file** pro každou kapelu
   - JSON soubor s nastavením všech kanálů
   - Scény pro různé skladby
   - Efekty a routing

2. **Před vystoupením**
   - Backend uploaduje show file do mixéru: `POST /api/shows/upload`
   - Mixer načte show: `POST /api/shows/load/[BandName]`
   - První scéna se automaticky načte

3. **Během vystoupení**
   - Zvukař přepína mezi scénami (cue recall)
   - Backend monitoruje změny přes WebSocket
   - Live úpravy se ukládají zpět do backendu

4. **Bezpečnost**
   - Webové rozhraní zakázáno: `POST /api/security/web/disable`
   - Pouze backend může měnit nastavení
   - Musicians nemají přímý přístup

### Show file struktura

```json
{
  "name": "Rock Band XYZ",
  "description": "Spring tour 2024",
  "scenes": [
    {
      "name": "Song 1 - Intro",
      "description": "Ambient intro, keys heavy",
      "data": {
        "channels": [
          {
            "id": 1,
            "name": "Lead Vocal",
            "gain": 18,
            "fader": 0.82,
            "eq": {
              "low": { "freq": 100, "gain": -2 },
              "lowMid": { "freq": 300, "gain": 3 },
              "highMid": { "freq": 3000, "gain": 4 },
              "high": { "freq": 10000, "gain": 2 }
            },
            "compressor": {
              "enabled": true,
              "threshold": -15,
              "ratio": 3.5,
              "attack": 8,
              "release": 120
            }
          }
        ]
      }
    }
  ]
}
```

## Technické detaily

### Simulované komponenty

1. **Studer preamps** - Gain staging simulace
2. **DBX compressors** - 160A typ komprese
3. **Lexicon reverbs** - Hall, Plate algoritmy
4. **DigiTech effects** - Chorus, delay
5. **USB recording** - 24-track metadata

### Limity

- **Max. scenes**: Neomezeno (RAM dependent)
- **Max. shows**: Neomezeno
- **Max. WebSocket clients**: 100 (configurable)
- **Show file size**: Do 10 MB

## Testing

```bash
# Kontrola stavu
curl http://localhost:9301/api/info

# WebSocket test (websocat)
websocat ws://localhost:9301

# Load test
ab -n 1000 -c 10 http://localhost:9301/api/state
```

## Troubleshooting

### Web access disabled
- Použít `/api/security/web/enable` s heslem
- Restartovat kontejner

### WebSocket nepřipojí
- Zkontrolovat firewall
- Zkontrolovat proxy nastavení (Nginx)

### Show file se nenačte
- Ověřit JSON formát
- Zkontrolovat strukturu (must have: name, scenes)

## License

MIT - Simulátor pro testování a development
