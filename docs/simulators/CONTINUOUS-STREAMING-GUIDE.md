# Průvodce kontinuálním streamováním - EVOLVEO Detective POE8 SMART

## Přehled

Kamery EVOLVEO Detective POE8 SMART nyní podporují režim **kontinuálního streamování** s automatickým generováním snímků a video segmentů pro backend systémy.

## Hlavní funkce

### 1. Kontinuální streaming
- Kamera nepřetržitě streamuje video a generuje snímky
- Automatické ukládání snímků každých 5 sekund (konfigurovatelné)
- Automatické vytváření video segmentů každých 60 sekund (konfigurovatelné)
- Backend může kdykoliv stahovat vygenerované snímky a videa

### 2. Automatické generování snímků
- **Frekvence**: Každých 5 sekund (výchozí)
- **Rozlišení**: 3840×2160 (8MP)
- **Formát**: JPEG (kvalita 90%)
- **Velikost**: ~100-300 KB na snímek
- **Uložení**: Poslední 1000 snímků v paměti

### 3. Automatické generování video segmentů
- **Frekvence**: Každých 60 sekund (výchozí)
- **Rozlišení**: 3840×2160 (8MP)
- **Formát**: MP4 (H.265/HEVC)
- **Délka**: 60 sekund (1200 snímků @ 20fps)
- **Velikost**: ~5-15 MB na segment
- **Uložení**: Posledních 500 segmentů v paměti

## API Endpointy

### Spuštění kontinuálního streamování

```bash
POST /streaming/start
```

**Tělo požadavku** (volitelné):
```json
{
  "snapshotInterval": 5000,      // Interval snímků v ms (minimálně 1000)
  "videoSegmentInterval": 60000   // Interval video segmentů v ms (minimálně 10000)
}
```

**Odpověď**:
```json
{
  "status": "ok",
  "message": "Kontinuální streaming spuštěn",
  "config": {
    "snapshotInterval": 5000,
    "videoSegmentInterval": 60000
  },
  "timestamp": "2025-11-20T23:14:40.806Z"
}
```

**Příklad**:
```bash
# Spustit s výchozími intervaly
curl -X POST http://localhost:9201/streaming/start

# Spustit s vlastními intervaly (snímky každé 3s, videa každých 30s)
curl -X POST http://localhost:9201/streaming/start \
  -H "Content-Type: application/json" \
  -d '{"snapshotInterval": 3000, "videoSegmentInterval": 30000}'
```

### Zastavení kontinuálního streamování

```bash
POST /streaming/stop
```

**Odpověď**:
```json
{
  "status": "ok",
  "message": "Kontinuální streaming zastaven",
  "stats": {
    "snapshotsCaptured": 17,
    "videoSegmentsRecorded": 1,
    "storedSnapshots": 17,
    "storedVideoSegments": 1
  },
  "timestamp": "2025-11-20T23:16:38.575Z"
}
```

**Příklad**:
```bash
curl -X POST http://localhost:9201/streaming/stop
```

### Stav kontinuálního streamování

```bash
GET /streaming/status
```

**Odpověď**:
```json
{
  "status": "ok",
  "streaming": {
    "active": true,
    "config": {
      "snapshotInterval": 5000,
      "videoSegmentInterval": 60000
    },
    "lastSnapshot": "2025-11-20T23:14:51.798Z",
    "lastVideoSegment": "2025-11-20T23:16:21.794Z",
    "stats": {
      "snapshotsCaptured": 17,
      "videoSegmentsRecorded": 1,
      storedSnapshots": 17,
      "storedVideoSegments": 1
    }
  },
  "timestamp": "2025-11-20T23:16:38.575Z"
}
```

**Příklad**:
```bash
curl http://localhost:9201/streaming/status | jq
```

### Seznam uložených snímků

```bash
GET /streaming/snapshots?limit=100&offset=0
```

**Parametry**:
- `limit` (volitelné): Maximální počet snímků (výchozí 100, max 500)
- `offset` (volitelné): Offset pro stránkování (výchozí 0)

**Odpověď**:
```json
{
  "status": "ok",
  "count": 17,
  "limit": 100,
  "offset": 0,
  "snapshots": [
    {
      "id": "snap_1763680491798_aoeeqap4r",
      "timestamp": "2025-11-20T23:14:51.798Z",
      "resolution": "3840x2160",
      "size": 203455,
      "format": "JPEG",
      "url": "/streaming/snapshot/snap_1763680491798_aoeeqap4r"
    }
  ],
  "timestamp": "2025-11-20T23:16:38.575Z"
}
```

**Příklad**:
```bash
# První 10 snímků
curl "http://localhost:9201/streaming/snapshots?limit=10" | jq

# Další stránka (snímky 11-20)
curl "http://localhost:9201/streaming/snapshots?limit=10&offset=10" | jq
```

### Stažení konkrétního snímku

```bash
GET /streaming/snapshot/:id
```

**Odpověď**: JPEG obrázek (Content-Type: image/jpeg)

**Příklad**:
```bash
# Stažení snímku
curl http://localhost:9201/streaming/snapshot/snap_1763680491798_aoeeqap4r \
  -o snapshot.jpg

# Zobrazení v prohlížeči
xdg-open snapshot.jpg
```

### Seznam uložených video segmentů

```bash
GET /streaming/videos?limit=100&offset=0
```

**Parametry**:
- `limit` (volitelné): Maximální počet segmentů (výchozí 100, max 500)
- `offset` (volitelné): Offset pro stránkování (výchozí 0)

**Odpověď**:
```json
{
  "status": "ok",
  "count": 1,
  "limit": 100,
  "offset": 0,
  "videoSegments": [
    {
      "id": "video_1763680581794_xysuleco7",
      "timestamp": "2025-11-20T23:16:21.794Z",
      "duration": 60,
      "resolution": "3840x2160",
      "size": 12263516,
      "format": "MP4",
      "codec": "H.265+",
      "url": "/streaming/video/video_1763680581794_xysuleco7"
    }
  ],
  "timestamp": "2025-11-20T23:16:38.575Z"
}
```

**Příklad**:
```bash
# První 5 video segmentů
curl "http://localhost:9201/streaming/videos?limit=5" | jq
```

### Detail konkrétního video segmentu

```bash
GET /streaming/video/:id
```

**Odpověď**:
```json
{
  "status": "ok",
  "video": {
    "id": "video_1763680581794_xysuleco7",
    "timestamp": "2025-11-20T23:16:21.794Z",
    "duration": 60,
    "resolution": "3840x2160",
    "width": 3840,
    "height": 2160,
    "size": 12263516,
    "format": "MP4",
    "codec": "H.265+",
    "bitrate": 8192,
    "framerate": 20,
    "frames": 1200,
    "recordingActive": false,
    "irActive": true,
    "temperature": 50.32,
    "note": "V reálné implementaci by zde byl video stream nebo download link"
  },
  "timestamp": "2025-11-20T23:16:38.575Z"
}
```

**Příklad**:
```bash
curl http://localhost:9201/streaming/video/video_1763680581794_xysuleco7 | jq
```

## WebSocket události

Když je aktivován kontinuální streaming, kamera vysílá následující dodatečné události:

### streaming_started
```json
{
  "type": "streaming_started",
  "deviceId": "ip-camera-1",
  "config": {
    "snapshotInterval": 5000,
    "videoSegmentInterval": 60000
  },
  "timestamp": "2025-11-20T23:14:40.806Z"
}
```

### streaming_stopped
```json
{
  "type": "streaming_stopped",
  "deviceId": "ip-camera-1",
  "stats": {
    "snapshotsCaptured": 17,
    "videoSegmentsRecorded": 1,
    "storedSnapshots": 17,
    "storedVideoSegments": 1
  },
  "timestamp": "2025-11-20T23:16:38.575Z"
}
```

### snapshot_generated
```json
{
  "type": "snapshot_generated",
  "deviceId": "ip-camera-1",
  "snapshot": {
    "id": "snap_1763680491798_aoeeqap4r",
    "timestamp": "2025-11-20T23:14:51.798Z",
    "resolution": "3840x2160",
    "url": "/streaming/snapshot/snap_1763680491798_aoeeqap4r"
  },
  "timestamp": "2025-11-20T23:14:51.798Z"
}
```

### video_segment_created
```json
{
  "type": "video_segment_created",
  "deviceId": "ip-camera-1",
  "videoSegment": {
    "id": "video_1763680581794_xysuleco7",
    "timestamp": "2025-11-20T23:16:21.794Z",
    "duration": 60,
    "resolution": "3840x2160",
    "size": 12263516,
    "url": "/streaming/video/video_1763680581794_xysuleco7"
  },
  "timestamp": "2025-11-20T23:16:21.794Z"
}
```

### heartbeat (rozšířeno)
Heartbeat události nyní obsahují i informace o streamingu:
```json
{
  "type": "heartbeat",
  "deviceId": "ip-camera-1",
  "status": "online",
  "continuousStreamActive": true,
  "streaming": {
    "snapshotsCaptured": 17,
    "videoSegmentsRecorded": 1,
    "storedSnapshots": 17,
    "storedVideoSegments": 1
  },
  "timestamp": "2025-11-20T23:16:38.575Z"
}
```

## Příklady integrace

### Bash - Automatické stahování snímků

```bash
#!/bin/bash

CAMERA_URL="http://localhost:9201"
OUTPUT_DIR="/var/storage/camera-snapshots"

# Spustit streaming
curl -X POST "$CAMERA_URL/streaming/start"

# Každých 10 sekund stáhnout nové snímky
while true; do
    # Získat seznam snímků
    SNAPSHOTS=$(curl -s "$CAMERA_URL/streaming/snapshots?limit=5" | jq -r '.snapshots[].id')
    
    # Stáhnout každý snímek
    for SNAP_ID in $SNAPSHOTS; do
        if [ ! -f "$OUTPUT_DIR/$SNAP_ID.jpg" ]; then
            echo "Stahuji $SNAP_ID..."
            curl -s "$CAMERA_URL/streaming/snapshot/$SNAP_ID" \
                -o "$OUTPUT_DIR/$SNAP_ID.jpg"
        fi
    done
    
    sleep 10
done
```

### Python - Backend integrace se WebSocket

```python
import asyncio
import aiohttp
import json
from pathlib import Path

class CameraBackend:
    def __init__(self, camera_url, storage_path):
        self.camera_url = camera_url
        self.storage_path = Path(storage_path)
        self.storage_path.mkdir(parents=True, exist_ok=True)
    
    async def start_streaming(self):
        """Spustit kontinuální streaming"""
        async with aiohttp.ClientSession() as session:
            async with session.post(f"{self.camera_url}/streaming/start") as resp:
                result = await resp.json()
                print(f"Streaming spuštěn: {result}")
    
    async def download_snapshot(self, snapshot_id):
        """Stáhnout konkrétní snímek"""
        async with aiohttp.ClientSession() as session:
            url = f"{self.camera_url}/streaming/snapshot/{snapshot_id}"
            async with session.get(url) as resp:
                if resp.status == 200:
                    content = await resp.read()
                    filepath = self.storage_path / f"{snapshot_id}.jpg"
                    filepath.write_bytes(content)
                    print(f"Snímek uložen: {filepath}")
    
    async def listen_websocket(self):
        """Poslouchat WebSocket události"""
        ws_url = self.camera_url.replace('http://', 'ws://').replace('https://', 'wss://')
        
        async with aiohttp.ClientSession() as session:
            async with session.ws_connect(ws_url) as ws:
                print(f"WebSocket připojen k {ws_url}")
                
                async for msg in ws:
                    if msg.type == aiohttp.WSMsgType.TEXT:
                        data = json.loads(msg.data)
                        
                        # Reagovat na vygenerování snímku
                        if data.get('type') == 'snapshot_generated':
                            snapshot_id = data['snapshot']['id']
                            print(f"Nový snímek: {snapshot_id}")
                            await self.download_snapshot(snapshot_id)
                        
                        # Reagovat na vytvoření video segmentu
                        elif data.get('type') == 'video_segment_created':
                            video_id = data['videoSegment']['id']
                            print(f"Nový video segment: {video_id}")
                    
                    elif msg.type == aiohttp.WSMsgType.ERROR:
                        print(f'WebSocket chyba: {ws.exception()}')
                        break

async def main():
    backend = CameraBackend("http://localhost:9201", "/var/storage/camera-1")
    
    # Spustit streaming
    await backend.start_streaming()
    
    # Poslouchat události
    await backend.listen_websocket()

if __name__ == '__main__':
    asyncio.run(main())
```

### Node.js - Stahování a ukládání

```javascript
const axios = require('axios');
const WebSocket = require('ws');
const fs = require('fs').promises;
const path = require('path');

class CameraBackend {
    constructor(cameraUrl, storagePath) {
        this.cameraUrl = cameraUrl;
        this.storagePath = storagePath;
        this.downloadedSnapshots = new Set();
    }
    
    async startStreaming(config = {}) {
        const response = await axios.post(`${this.cameraUrl}/streaming/start`, config);
        console.log('Streaming spuštěn:', response.data);
    }
    
    async downloadSnapshot(snapshotId) {
        if (this.downloadedSnapshots.has(snapshotId)) {
            return; // Již staženo
        }
        
        const url = `${this.cameraUrl}/streaming/snapshot/${snapshotId}`;
        const response = await axios.get(url, { responseType: 'arraybuffer' });
        
        const filepath = path.join(this.storagePath, `${snapshotId}.jpg`);
        await fs.writeFile(filepath, response.data);
        
        this.downloadedSnapshots.add(snapshotId);
        console.log(`Snímek uložen: ${filepath}`);
    }
    
    async connectWebSocket() {
        const wsUrl = this.cameraUrl.replace('http://', 'ws://');
        const ws = new WebSocket(wsUrl);
        
        ws.on('open', () => {
            console.log('WebSocket připojen');
        });
        
        ws.on('message', async (data) => {
            const event = JSON.parse(data);
            
            // Automaticky stahovat nové snímky
            if (event.type === 'snapshot_generated') {
                await this.downloadSnapshot(event.snapshot.id);
            }
            
            // Logovat vytvoření video segmentů
            if (event.type === 'video_segment_created') {
                console.log('Nový video segment:', event.videoSegment);
            }
        });
        
        ws.on('error', (error) => {
            console.error('WebSocket chyba:', error);
        });
    }
}

// Použití
const backend = new CameraBackend('http://localhost:9201', '/var/storage/camera-1');

(async () => {
    await backend.startStreaming({
        snapshotInterval: 5000,
        videoSegmentInterval: 60000
    });
    
    await backend.connectWebSocket();
})();
```

## Konfigurace intervalů

### Optimální nastavení podle účelu

**Bezpečnostní monitoring (vysoká kvalita)**:
```json
{
  "snapshotInterval": 2000,      // Snímky každé 2 sekundy
  "videoSegmentInterval": 300000  // 5minutová videa
}
```

**Standardní monitoring**:
```json
{
  "snapshotInterval": 5000,      // Snímky každých 5 sekund (výchozí)
  "videoSegmentInterval": 60000   // 1minutová videa (výchozí)
}
```

**Úsporný režim**:
```json
{
  "snapshotInterval": 10000,     // Snímky každých 10 sekund
  "videoSegmentInterval": 120000  // 2minutová videa
}
```

**Detekce pohybu**:
```json
{
  "snapshotInterval": 1000,      // Snímky každou sekundu
  "videoSegmentInterval": 30000   // 30sekundová videa
}
```

## Omezení a poznámky

1. **Paměť**: Ukládá se posledních 1000 snímků a 500 video segmentů v paměti
2. **Minimální intervaly**:
   - Snímky: minimálně 1 sekunda (1000 ms)
   - Video segmenty: minimálně 10 sekund (10000 ms)
3. **Simulace**: V simulátoru se negenerují skutečná videa, pouze metadata
4. **Úložiště**: V reálné kameře by snímky/videa byly uloženy na MicroSD kartě

## Testování

### Test 1: Základní funkčnost
```bash
# 1. Spustit streaming
curl -X POST http://localhost:9201/streaming/start

# 2. Počkat 11 sekund (měly by se vygenerovat 2 snímky)
sleep 11

# 3. Zkontrolovat stav
curl http://localhost:9201/streaming/status | jq '.streaming.stats'

# Očekávaný výsledek: snapshotsCaptured >= 2
```

### Test 2: Stahování snímků
```bash
# 1. Získat seznam snímků
SNAP_ID=$(curl -s http://localhost:9201/streaming/snapshots | jq -r '.snapshots[0].id')

# 2. Stáhnout snímek
curl http://localhost:9201/streaming/snapshot/$SNAP_ID -o test.jpg

# 3. Ověřit soubor
file test.jpg
# Očekávaný výsledek: JPEG image data, 3840x2160
```

### Test 3: Video segmenty
```bash
# 1. Počkat 65 sekund na vytvoření video segmentu
sleep 65

# 2. Zkontrolovat video segmenty
curl http://localhost:9201/streaming/videos | jq '.count'

# Očekávaný výsledek: alespoň 1 video segment
```

### Test 4: Vlastní intervaly
```bash
# 1. Spustit se snímky každé 3 sekundy
curl -X POST http://localhost:9201/streaming/start \
  -H "Content-Type: application/json" \
  -d '{"snapshotInterval": 3000}'

# 2. Počkat 10 sekund
sleep 10

# 3. Zkontrolovat počet snímků (měly by být 3-4)
curl http://localhost:9201/streaming/status | jq '.streaming.stats.snapshotsCaptured'
```

## Řešení problémů

### Streaming se nespustí
```bash
# Zkontrolovat stav kamery
curl http://localhost:9201/status

# Ověřit, že kamera není offline
# "online": true
```

### Žádné snímky se negenerují
```bash
# Zkontrolovat, zda je streaming aktivní
curl http://localhost:9201/streaming/status | jq '.streaming.active'

# Mělo by být: true
```

### Snímky nejsou k dispozici
```bash
# Zkontrolovat seznam snímků
curl http://localhost:9201/streaming/snapshots | jq '.count'

# Pokud je count 0, počkejte alespoň 5 sekund od spuštění streamingu
```

## Podpora

Pro více informací:
- Hlavní dokumentace: `EVOLVEO-DETECTIVE-POE8-SMART.md`
- API dokumentace: `EVOLVEO-DETECTIVE-POE8-SMART-API.md`
- Testovací příklady: `TESTING-CAMERA.md`
