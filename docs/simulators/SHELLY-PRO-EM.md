# Shelly Pro EM - Dokumentace simulátoru

## Přehled

Simulátor **Shelly Pro EM** je dvoukanálový měřič energie navržený pro monitorování spotřeby v zkušebnách:

- **Kanál 0 (em1:0)**: Měření světel **s možností spínání relé**
- **Kanál 1 (em1:1)**: Měření zásuvek (trvale zapnuto, **pouze monitoring**)

## Architektura pro 6 zkušeben

Pro každou zkušebnu je nasazen 1x Shelly Pro EM:

| Zkušebna | Container | Port | Device ID | Kanál 0 | Kanál 1 |
|----------|-----------|------|-----------|---------|---------|------|
| Lab-01 | shelly-pro-em-1 | 9501 | shelly-pro-em-1 | Světla (s relé) | Zásuvky (monitoring) |
| Lab-02 | shelly-pro-em-2 | 9502 | shelly-pro-em-2 | Světla (s relé) | Zásuvky (monitoring) |
| Lab-03 | shelly-pro-em-3 | 9503 | shelly-pro-em-3 | Světla (s relé) | Zásuvky (monitoring) |
| Lab-04 | shelly-pro-em-4 | 9504 | shelly-pro-em-4 | Světla (s relé) | Zásuvky (monitoring) |
| Lab-05 | shelly-pro-em-5 | 9505 | shelly-pro-em-5 | Světla (s relé) | Zásuvky (monitoring) |
| Lab-06 | shelly-pro-em-6 | 9506 | shelly-pro-em-6 | Světla (s relé) | Zásuvky (monitoring) |

## API Endpointy

### 1. Základní informace
```bash
curl http://localhost:9501/
```

### 2. Status zařízení (všechny kanály)
```bash
curl http://localhost:9501/status
```

**Odpověď:**
```json
{
  "switch": [{"ison": false, "source": "http", ...}],
  "em1": [
    {
      "id": 0,
      "voltage": 230.0,
      "current": 0.0,
      "power": 0.0,
      "pf": 0.0,
      "total": 0.0
    },
    {
      "id": 1,
      "voltage": 230.0,
      "current": 0.65,
      "power": 149.5,
      "pf": 0.89,
      "total": 1.234
    }
  ],
  "uptime": 3600
}
```

### 3. Ovládání světel (Kanál 0)

#### Zapnout světla
```bash
curl "http://localhost:9501/relay/0?turn=on"
```

#### Vypnout světla
```bash
curl "http://localhost:9501/relay/0?turn=off"
```

#### Toggle (přepnout)
```bash
curl "http://localhost:9501/relay/0?turn=toggle"
```

#### Zapnout s časovačem (30s)
```bash
curl "http://localhost:9501/relay/0?turn=on&timer=30"
```

### 4. Měření spotřeby

#### Gen2 RPC API - Switch status (Kanál 0 - Světla)
```bash
curl "http://localhost:9501/rpc/Switch.GetStatus?id=0"
```

**Odpověď:**
```json
{
  "id": 0,
  "source": "http",
  "output": true,
  "apower": 125.3,
  "voltage": 230.0,
  "current": 0.545,
  "pf": 0.92,
  "aenergy": {
    "total": 2.456,
    "by_minute": [0, 0, 0],
    "minute_ts": 1700000000
  },
  "temperature": {
    "tC": 35.5,
    "tF": 95.9
  }
}
```

#### Gen2 RPC API - EM1 status (Kanál 0 nebo 1)
```bash
# Kanál 0 - Světla
curl "http://localhost:9501/rpc/EM1.GetStatus?id=0"

# Kanál 1 - Zásuvky
curl "http://localhost:9501/rpc/EM1.GetStatus?id=1"
```

**Odpověď:**
```json
{
  "id": 1,
  "voltage": 230.0,
  "current": 0.652,
  "act_power": 149.96,
  "aprt_power": 168.49,
  "pf": 0.89,
  "freq": 50.0,
  "total_act_energy": 3.678,
  "total_act_ret_energy": 0.0
}
```

#### Backward compatible meter API
```bash
# Kanál 0
curl http://localhost:9501/meter/0

# Kanál 1
curl http://localhost:9501/meter/1
```

### 5. WebSocket Monitoring

#### Připojení
```javascript
const ws = new WebSocket('ws://localhost:9501');

ws.on('message', (data) => {
  const event = JSON.parse(data);
  console.log('Event:', event.type);
});
```

#### Heartbeat (každých 10s)
```json
{
  "type": "heartbeat",
  "deviceId": "shelly-pro-em-1",
  "status": "online",
  "uptime": 3600,
  "temperature": 35.5,
  "error": null,
  "switch": false,
  "em1_0_power": 0.0,
  "em1_1_power": 149.5,
  "total_power": 149.5,
  "timestamp": "2025-11-20T10:30:00.000Z"
}
```

#### Switch Change (změna stavu světel)
```json
{
  "type": "switch_change",
  "deviceId": "shelly-pro-em-1",
  "switch": {
    "id": 0,
    "output": true,
    "power": 125.3
  },
  "timestamp": "2025-11-20T10:30:00.000Z"
}
```

## Simulace poruch

### Offline
```bash
curl -X POST http://localhost:9501/simulate \
  -H "Content-Type: application/json" \
  -d '{"action":"offline"}'
```

### Online
```bash
curl -X POST http://localhost:9501/simulate \
  -H "Content-Type: application/json" \
  -d '{"action":"online"}'
```

### Přehřátí
```bash
curl -X POST http://localhost:9501/simulate \
  -H "Content-Type: application/json" \
  -d '{"action":"overheating","data":{"value":85.0}}'
```

### Změna teploty
```bash
curl -X POST http://localhost:9501/simulate \
  -H "Content-Type: application/json" \
  -d '{"action":"temperature","data":{"value":42.0}}'
```

### Přetížení kanálu
```bash
curl -X POST http://localhost:9501/simulate \
  -H "Content-Type: application/json" \
  -d '{"action":"overpower","data":{"channel":0}}'
```

### Ztráta spojení
```bash
curl -X POST http://localhost:9501/simulate \
  -H "Content-Type: application/json" \
  -d '{"action":"connection_lost"}'
```

## Stavový log

```bash
curl http://localhost:9501/state-log
```

**Odpověď:**
```json
{
  "status": "ok",
  "count": 15,
  "log": [
    {
      "type": "relay_switch",
      "message": "Relé zapnuto",
      "deviceId": "shelly-pro-em-1",
      "timestamp": "2025-11-20T10:30:00.000Z",
      "deviceStatus": {
        "online": true,
        "error": null,
        "temperature": 35.5,
        "uptime": 3600,
        "switchState": true,
        "em1_channel_0": {...},
        "em1_channel_1": {...}
      },
      "newState": true,
      "previousState": false
    }
  ]
}
```

## Příklady použití

### 1. Ovládání světel v Lab-01
```bash
# Zapnout světla
curl "http://localhost:9501/relay/0?turn=on"

# Zkontrolovat spotřebu světel
curl "http://localhost:9501/rpc/EM1.GetStatus?id=0"
```

### 2. Monitoring spotřeby zásuvek v Lab-02
```bash
# Získat aktuální spotřebu zásuvek (kanál 1)
curl "http://localhost:9501/rpc/EM1.GetStatus?id=1"
```

### 3. Celková spotřeba zkušebny
```bash
# Status zobrazí oba kanály
curl http://localhost:9501/status | jq '.em1'
```

### 4. WebSocket monitoring všech zkušeben
```python
import asyncio
import websockets
import json

async def monitor_lab(lab_id, port):
    uri = f"ws://localhost:{port}"
    async with websockets.connect(uri) as ws:
        while True:
            data = await ws.recv()
            event = json.loads(data)
            if event['type'] == 'heartbeat':
                print(f"{lab_id}: Light={event['em1_0_power']}W, Sockets={event['em1_1_power']}W")

# Monitor všech 6 zkušeben
asyncio.gather(
    monitor_lab('Lab-01', 9301),
    monitor_lab('Lab-02', 9302),
    monitor_lab('Lab-03', 9303),
    monitor_lab('Lab-04', 9304),
    monitor_lab('Lab-05', 9305),
    monitor_lab('Lab-06', 9306)
)
```

## Technické parametry

### Kanál 0 (Světla)
- Relé: Ano (ovládatelné)
- Napětí: 230V AC
- Proud: 0-10A
- Výkon: 0-2300W (simulované 100-150W při zapnutí)
- Power Factor: 0.85-0.95

### Kanál 1 (Zásuvky)
- Relé: Ne (trvale zapnuto)
- Napětí: 230V AC
- Proud: 0-16A
- Výkon: 0-3680W (simulované 50-250W proměnné)
- Power Factor: 0.80-0.95

## Rozdíly oproti reálnému Shelly Pro EM

### Implementováno:
- ✅ 2 měřicí kanály (em1:0, em1:1)
- ✅ 1 relé výstup (pouze kanál 0)
- ✅ Gen2 RPC API (Switch.GetStatus, EM1.GetStatus)
- ✅ HTTP REST API (/relay/0?turn=on/off/toggle)
- ✅ WebSocket heartbeat a notifikace
- ✅ Power metering (napětí, proud, výkon, PF)
- ✅ Měření energie (total_act_energy)
- ✅ Fault simulation

### Zjednodušeno:
- ⚠️ Relé je skutečně "dry contact" (potenciálově oddělené), zde simulované
- ⚠️ Kalibrace a přesnost měření (simulované hodnoty)
- ⚠️ MQTT, Modbus podpory (pouze HTTP a WebSocket)
- ⚠️ Detailní EMData komponenta (minutové historické záznamy)

## Restart všech Shelly Pro EM

```bash
sudo docker compose restart shelly-pro-em-1 shelly-pro-em-2 shelly-pro-em-3 shelly-pro-em-4 shelly-pro-em-5 shelly-pro-em-6
```
