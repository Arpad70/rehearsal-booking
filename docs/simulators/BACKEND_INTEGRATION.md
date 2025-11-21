# Backend Integration Guide
# Soundcraft Ui24R Mixer Simulator

## Přehled

Tento dokument popisuje, jak integrovat Soundcraft Ui24R mixer simulátor do vašeho backend systému pro správu nastavení kapel.

## Architektura

```
┌─────────────────┐         ┌──────────────────┐         ┌────────────────┐
│                 │         │                  │         │                │
│  Backend Server │ ◄─────► │  Mixer Simulator │ ◄─────► │  Musicians     │
│  (kontrola)     │  API    │  (soundcraft)    │  Block  │  (no access)   │
│                 │         │                  │         │                │
└─────────────────┘         └──────────────────┘         └────────────────┘
        │
        │ Store/Load
        ▼
┌─────────────────┐
│   Database      │
│   (show files)  │
└─────────────────┘
```

## Use Case: Správa nastavení kapel

### Workflow

1. **Příprava show file** (offline)
   - Backend vytvoří JSON show file pro kapelu
   - Obsahuje všechna nastavení kanálů, EQ, kompresory, efekty
   - Uloží do databáze s názvem kapely

2. **Před vystoupením** (remote upload)
   - Backend uploaduje show file do mixéru: `POST /api/shows/upload`
   - Mixer načte show: `POST /api/shows/load/[BandName]`
   - První scéna se automaticky aktivuje

3. **Během vystoupení** (live control)
   - Zvukař přepíná mezi scénami pomocí `POST /api/scenes/load/:name`
   - Backend monitoruje změny přes WebSocket
   - Live úpravy se ukládají zpět do backendu (optional)

4. **Bezpečnost** (lockdown)
   - Webové rozhraní se zakáže: `POST /api/security/web/disable`
   - Pouze backend může měnit nastavení
   - Musicians nemají přímý přístup k mixéru

### Bezpečnostní režimy

#### 1. Backend-Only Mode
Pouze backend server může ovládat mixer.

```bash
# Enable backend-only mode
curl -X POST http://mixer-ip/api/security/backend-only \
  -H "Content-Type: application/json" \
  -d '{"enabled": true}'
```

V tomto režimu mixer kontroluje IP adresu requestu:
- Povoleno: Backend server (nastaveno v `BACKEND_IP` env var)
- Zakázáno: Všechny ostatní IP adresy

#### 2. Web Access Disable
Kompletní zakázání webového přístupu.

```bash
# Disable web access
curl -X POST http://mixer-ip/api/security/web/disable
```

Po zakázání:
- Všechny API endpointy vrátí `403 Forbidden`
- Kromě `/api/security/web/enable` pro recovery
- Použijte pro maximum security během vystoupení

#### 3. Password Protection
Ochrana heslem pro všechny requesty.

```bash
# Set password
curl -X POST http://mixer-ip/api/security/password \
  -H "Content-Type: application/json" \
  -d '{"password": "secret123"}'

# Authenticated request
curl http://mixer-ip/api/state \
  -H "Authorization: Bearer secret123"
```

## API Integration Examples

### Python Backend Integration

```python
import requests
import json

class SoundcraftMixerClient:
    def __init__(self, mixer_url, password=None):
        self.mixer_url = mixer_url
        self.headers = {}
        if password:
            self.headers['Authorization'] = f'Bearer {password}'
    
    def upload_show(self, show_file_path):
        """Upload show file to mixer"""
        with open(show_file_path, 'rb') as f:
            files = {'file': f}
            response = requests.post(
                f'{self.mixer_url}/api/shows/upload',
                files=files,
                headers=self.headers
            )
        return response.json()
    
    def load_show(self, show_name, load_first_scene=True):
        """Load show file (activate scenes)"""
        response = requests.post(
            f'{self.mixer_url}/api/shows/load/{show_name}',
            json={'loadFirstScene': load_first_scene},
            headers=self.headers
        )
        return response.json()
    
    def load_scene(self, scene_name):
        """Switch to different scene (cue recall)"""
        response = requests.post(
            f'{self.mixer_url}/api/scenes/load/{scene_name}',
            headers=self.headers
        )
        return response.json()
    
    def disable_web_access(self):
        """Lockdown mixer - only backend can control"""
        response = requests.post(
            f'{self.mixer_url}/api/security/web/disable',
            headers=self.headers
        )
        return response.json()
    
    def enable_web_access(self):
        """Re-enable web access"""
        response = requests.post(
            f'{self.mixer_url}/api/security/web/enable',
            json={'password': self.headers.get('Authorization', '').replace('Bearer ', '')},
            headers=self.headers
        )
        return response.json()
    
    def get_current_state(self):
        """Get complete mixer state"""
        response = requests.get(
            f'{self.mixer_url}/api/state',
            headers=self.headers
        )
        return response.json()

# Usage example
mixer = SoundcraftMixerClient('http://localhost:9301')

# Upload band setup
result = mixer.upload_show('bands/rock-band-xyz.show')
print(f"Show uploaded: {result['message']}")

# Load show
result = mixer.load_show('Rock Band XYZ', load_first_scene=True)
print(f"Show loaded: {result['message']}")
print(f"Current scene: {result['currentScene']}")

# Lockdown mixer
result = mixer.disable_web_access()
print(f"Security: {result['message']}")
```

### Node.js Backend Integration

```javascript
const axios = require('axios');
const FormData = require('form-data');
const fs = require('fs');

class SoundcraftMixerClient {
  constructor(mixerUrl, password = null) {
    this.mixerUrl = mixerUrl;
    this.headers = {};
    if (password) {
      this.headers['Authorization'] = `Bearer ${password}`;
    }
  }

  async uploadShow(showFilePath) {
    const formData = new FormData();
    formData.append('file', fs.createReadStream(showFilePath));
    
    const response = await axios.post(
      `${this.mixerUrl}/api/shows/upload`,
      formData,
      { headers: { ...this.headers, ...formData.getHeaders() } }
    );
    return response.data;
  }

  async loadShow(showName, loadFirstScene = true) {
    const response = await axios.post(
      `${this.mixerUrl}/api/shows/load/${encodeURIComponent(showName)}`,
      { loadFirstScene },
      { headers: this.headers }
    );
    return response.data;
  }

  async loadScene(sceneName) {
    const response = await axios.post(
      `${this.mixerUrl}/api/scenes/load/${encodeURIComponent(sceneName)}`,
      {},
      { headers: this.headers }
    );
    return response.data;
  }

  async disableWebAccess() {
    const response = await axios.post(
      `${this.mixerUrl}/api/security/web/disable`,
      {},
      { headers: this.headers }
    );
    return response.data;
  }

  async getCurrentState() {
    const response = await axios.get(
      `${this.mixerUrl}/api/state`,
      { headers: this.headers }
    );
    return response.data;
  }
}

// Usage
const mixer = new SoundcraftMixerClient('http://localhost:9301');

async function setupBandShow() {
  // Upload show file
  const uploadResult = await mixer.uploadShow('./bands/rock-band-xyz.show');
  console.log('Show uploaded:', uploadResult.message);

  // Load show
  const loadResult = await mixer.loadShow('Rock Band XYZ', true);
  console.log('Show loaded:', loadResult.message);
  console.log('Current scene:', loadResult.currentScene);

  // Lockdown mixer
  const securityResult = await mixer.disableWebAccess();
  console.log('Security:', securityResult.message);
}

setupBandShow().catch(console.error);
```

## Show File Format

### Struktura JSON souboru

```json
{
  "name": "Rock Band XYZ",
  "description": "Spring tour 2024",
  "created": "2024-01-15T10:00:00Z",
  "modified": "2024-01-15T10:00:00Z",
  "globalSettings": {
    "sampleRate": 48000,
    "recordingEnabled": true,
    "meterMode": "post-fader",
    "soloMode": "pfl"
  },
  "scenes": [
    {
      "name": "Song 1 - Intro",
      "description": "Ambient intro, keys heavy",
      "created": "2024-01-15T10:00:00Z",
      "modified": "2024-01-15T10:00:00Z",
      "data": {
        "channels": [
          {
            "id": 1,
            "name": "Lead Vocal",
            "type": "combo",
            "mute": false,
            "solo": false,
            "gain": 18.0,
            "phantom": true,
            "phase": false,
            "hpf": {
              "enabled": true,
              "frequency": 80
            },
            "gate": {
              "enabled": true,
              "threshold": -35,
              "range": 15,
              "attack": 1.5,
              "release": 100
            },
            "compressor": {
              "enabled": true,
              "threshold": -15,
              "ratio": 4.0,
              "attack": 8,
              "release": 120,
              "gain": 3
            },
            "eq": {
              "enabled": true,
              "highpass": { "enabled": true, "freq": 80 },
              "low": { "enabled": true, "freq": 100, "gain": -2, "q": 1.0 },
              "lowMid": { "enabled": true, "freq": 300, "gain": 3, "q": 1.4 },
              "highMid": { "enabled": true, "freq": 3000, "gain": 4, "q": 1.2 },
              "high": { "enabled": true, "freq": 10000, "gain": 2, "q": 1.0 }
            },
            "fader": 0.82,
            "pan": 0.5,
            "auxSends": [0.6, 0.4, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0],
            "fxSends": [0.25, 0.15, 0.0, 0.0]
          }
        ],
        "auxBusses": [...],
        "fxProcessors": [...],
        "mainLR": {...}
      }
    }
  ]
}
```

### Generování show file v backendu

```python
def create_show_file(band_name, scenes_data):
    """Create show file from band setup"""
    show = {
        "name": band_name,
        "description": f"Show file for {band_name}",
        "created": datetime.now().isoformat(),
        "modified": datetime.now().isoformat(),
        "globalSettings": {
            "sampleRate": 48000,
            "recordingEnabled": True,
            "meterMode": "post-fader",
            "soloMode": "pfl"
        },
        "scenes": scenes_data
    }
    return show

def create_vocal_channel(channel_id, name, gain=18.0):
    """Template for vocal channel"""
    return {
        "id": channel_id,
        "name": name,
        "type": "combo",
        "gain": gain,
        "phantom": True,
        "eq": {
            "enabled": True,
            "low": {"freq": 100, "gain": -2, "q": 1.0},
            "lowMid": {"freq": 300, "gain": 3, "q": 1.4},
            "highMid": {"freq": 3000, "gain": 4, "q": 1.2},
            "high": {"freq": 10000, "gain": 2, "q": 1.0}
        },
        "compressor": {
            "enabled": True,
            "threshold": -15,
            "ratio": 4.0,
            "attack": 8,
            "release": 120,
            "gain": 3
        },
        "fader": 0.82,
        "pan": 0.5
    }

# Usage
channels = [
    create_vocal_channel(1, "Lead Vocal", 18.0),
    create_vocal_channel(2, "Backing Vocal", 16.0)
]

scene = {
    "name": "Main Mix",
    "description": "Standard setup",
    "data": {
        "channels": channels,
        "auxBusses": [],
        "fxProcessors": [],
        "mainLR": {}
    }
}

show = create_show_file("Rock Band XYZ", [scene])

# Save to file
with open('rock-band-xyz.show', 'w') as f:
    json.dump(show, f, indent=2)
```

## WebSocket Integration

Pro real-time monitoring změn v mixéru:

```javascript
const WebSocket = require('ws');

const ws = new WebSocket('ws://localhost:9301/');

ws.on('open', () => {
  console.log('Connected to mixer');
});

ws.on('message', (data) => {
  const message = JSON.parse(data);
  
  switch (message.type) {
    case 'connected':
      console.log('Mixer connected:', message.deviceId);
      break;
      
    case 'heartbeat':
      console.log('Heartbeat:', {
        uptime: message.uptime,
        temperature: message.temperature,
        currentScene: message.currentScene
      });
      break;
      
    case 'scene_loaded':
      console.log('Scene changed:', message.scene);
      // Update backend database
      updateBandScene(message.scene);
      break;
      
    case 'channel_updated':
      console.log('Channel updated:', message.channelId);
      // Log changes
      logChannelChange(message.channel);
      break;
      
    case 'show_uploaded':
      console.log('Show uploaded:', message.show);
      break;
  }
});

ws.on('close', () => {
  console.log('Disconnected from mixer');
});
```

## Database Schema (Example)

```sql
-- Table: bands
CREATE TABLE bands (
  id INTEGER PRIMARY KEY,
  name TEXT NOT NULL,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: show_files
CREATE TABLE show_files (
  id INTEGER PRIMARY KEY,
  band_id INTEGER REFERENCES bands(id),
  name TEXT NOT NULL,
  description TEXT,
  show_data JSON NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: mixer_sessions
CREATE TABLE mixer_sessions (
  id INTEGER PRIMARY KEY,
  band_id INTEGER REFERENCES bands(id),
  show_file_id INTEGER REFERENCES show_files(id),
  mixer_id TEXT NOT NULL,
  started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  ended_at TIMESTAMP,
  current_scene TEXT
);

-- Table: scene_changes
CREATE TABLE scene_changes (
  id INTEGER PRIMARY KEY,
  session_id INTEGER REFERENCES mixer_sessions(id),
  scene_name TEXT NOT NULL,
  changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Docker Compose Integration

```yaml
services:
  backend:
    image: your-backend:latest
    environment:
      - MIXER_URL=http://soundcraft-mixer
      - MIXER_PASSWORD=secret123
    networks:
      - simulator-network

  soundcraft-mixer:
    build: ./soundcraft-mixer
    container_name: soundcraft-mixer
    environment:
      - PORT=80
      - DEVICE_ID=mixer-1
      - BACKEND_IP=backend
    networks:
      - simulator-network
    restart: unless-stopped

networks:
  simulator-network:
    driver: bridge
```

## Best Practices

### 1. Před vystoupením
```bash
# 1. Upload show file
curl -X POST http://mixer/api/shows/upload -F "file=@band.show"

# 2. Load show
curl -X POST http://mixer/api/shows/load/BandName \
  -d '{"loadFirstScene": true}'

# 3. Enable backend-only mode
curl -X POST http://mixer/api/security/backend-only \
  -d '{"enabled": true}'

# 4. Disable web access
curl -X POST http://mixer/api/security/web/disable
```

### 2. Během vystoupení
```bash
# Přepínání scén
curl -X POST http://mixer/api/scenes/load/Song1
curl -X POST http://mixer/api/scenes/load/Song2
```

### 3. Po vystoupení
```bash
# 1. Download current state
curl http://mixer/api/shows/download/BandName > band-updated.show

# 2. Enable web access
curl -X POST http://mixer/api/security/web/enable \
  -d '{"password": "secret123"}'

# 3. Disable backend-only mode
curl -X POST http://mixer/api/security/backend-only \
  -d '{"enabled": false}'
```

## Troubleshooting

### Problem: 403 Forbidden
**Řešení**: Web access je zakázán nebo backend-only režim aktivní
```bash
# Recovery endpoint (vždy funguje)
curl -X POST http://mixer/api/security/web/enable
```

### Problem: Show file se nenačte
**Řešení**: Ověřit JSON formát
```bash
# Validate JSON
jq . band.show

# Check structure
jq -r '.name, .scenes | length' band.show
```

### Problem: WebSocket nepřipojí
**Řešení**: Zkontrolovat firewall a proxy
```bash
# Test WebSocket
websocat ws://mixer-ip:9301/
```

## API Reference

Kompletní API dokumentaci najdete v `README.md` souboru v projektu.

## Support

Pro podporu a bug reports:
- GitHub Issues: [your-repo]
- Email: support@example.com
- Dokumentace: /soundcraft-mixer/README.md
