# 🏢 Multi-Device IoT Simulator

Simulace 26 IoT zařízení s přímým přístupem (bez Nginx proxy).

## 📊 Přehled zařízení

| Typ zařízení | Počet | Porty | WebSocket |
|-------------|-------|-------|-----------|
| QR Čtečky | 6 | 9101-9106 | ✅ |
| IP Kamery | 12 | 9201-9212 | ❌ |
| Shelly PRO 1 | 6 | 9301-9306 | ❌ |
| Klávesnice | 2 | 9401-9402 | ✅ |

**Celkem:** 26 zařízení

## 🚀 Spuštění

```bash
cd /mnt/data/www/Simulace
sudo docker compose up -d --build
```

## 🌐 Přístup

Každé zařízení má vlastní port přístupný přímo na http://localhost:XXXX

### 📱 QR Čtečky (6x)

| # | HTTP | WebSocket | Popis |
|---|------|-----------|-------|
| 1 | http://localhost:9101 | ws://localhost:9101 | QR čtečka #1 |
| 2 | http://localhost:9102 | ws://localhost:9102 | QR čtečka #2 |
| 3 | http://localhost:9103 | ws://localhost:9103 | QR čtečka #3 |
| 4 | http://localhost:9104 | ws://localhost:9104 | QR čtečka #4 |
| 5 | http://localhost:9105 | ws://localhost:9105 | QR čtečka #5 |
| 6 | http://localhost:9106 | ws://localhost:9106 | QR čtečka #6 |

**API Příklad:**
```bash
curl -X POST http://localhost:9101/scan \
  -H "Content-Type: application/json" \
  -d '{"code":"TOKEN_123","authorized":true}'
```

### 📹 IP Kamery (12x)

| # | HTTP | Snapshot | Stream |
|---|------|----------|--------|
| 1 | http://localhost:9201 | /snapshot | /stream |
| 2 | http://localhost:9202 | /snapshot | /stream |
| 3 | http://localhost:9203 | /snapshot | /stream |
| 4 | http://localhost:9204 | /snapshot | /stream |
| 5 | http://localhost:9205 | /snapshot | /stream |
| 6 | http://localhost:9206 | /snapshot | /stream |
| 7 | http://localhost:9207 | /snapshot | /stream |
| 8 | http://localhost:9208 | /snapshot | /stream |
| 9 | http://localhost:9209 | /snapshot | /stream |
| 10 | http://localhost:9210 | /snapshot | /stream |
| 11 | http://localhost:9211 | /snapshot | /stream |
| 12 | http://localhost:9212 | /snapshot | /stream |

**API Příklad:**
```bash
curl http://localhost:9201/snapshot --output snapshot.jpg
```

### 🔌 Shelly PRO 1 (6x)

| # | HTTP | Relé | Status |
|---|------|------|--------|
| 1 | http://localhost:9301 | /relay/0 | /status |
| 2 | http://localhost:9302 | /relay/0 | /status |
| 3 | http://localhost:9303 | /relay/0 | /status |
| 4 | http://localhost:9304 | /relay/0 | /status |
| 5 | http://localhost:9305 | /relay/0 | /status |
| 6 | http://localhost:9306 | /relay/0 | /status |

**API Příklad:**
```bash
curl "http://localhost:9301/relay/0?turn=on"
```

### 🔢 Klávesnice (2x)

| # | HTTP | WebSocket | Popis |
|---|------|-----------|-------|
| 1 | http://localhost:9401 | ws://localhost:9401 | Klávesnice #1 |
| 2 | http://localhost:9402 | ws://localhost:9402 | Klávesnice #2 |

**API Příklad:**
```bash
curl -X POST http://localhost:9401/verify-pin \
  -H "Content-Type: application/json" \
  -d '{"pin":"1234","authorized":true}'
```

## 🔧 Správa

```bash
# Zobrazit běžící kontejnery
sudo docker compose ps

# Zobrazit logy
sudo docker compose logs -f

# Zastavit
sudo docker compose down

# Restart
sudo docker compose restart

# Rebuild konkrétního zařízení
sudo docker compose up -d --build qr-reader-1
```

## 📡 WebSocket Real-time Monitoring

### QR Čtečky
```javascript
const ws = new WebSocket('ws://localhost:9101');
ws.onmessage = (event) => {
  const data = JSON.parse(event.data);
  console.log('QR Event:', data.type);
};
```

### Klávesnice
```javascript
const ws = new WebSocket('ws://localhost:9401');
ws.onmessage = (event) => {
  const data = JSON.parse(event.data);
  console.log('Keypad Event:', data.type);
};
```

## 🎯 Testování

### Test QR čtečky #1
```bash
curl -X POST http://localhost:9101/scan \
  -H "Content-Type: application/json" \
  -d '{"code":"ACCESS_TOKEN","authorized":true,"unlockDuration":10}'
```

### Test klávesnice #1
```bash
curl -X POST http://localhost:9401/verify-pin \
  -H "Content-Type: application/json" \
  -d '{"pin":"5678","authorized":true,"unlockDuration":10}'
```

### Test IP kamery #1
```bash
curl http://localhost:9201/snapshot --output camera1.jpg
```

### Test Shelly #1
```bash
curl "http://localhost:9301/relay/0?turn=toggle"
```

## 📝 Poznámky

- QR čtečky mají výstup na **pin 4 (+5V)** pro 10 sekund při autorizaci
- Klávesnice mají **výstup pro 10 sekund** při správném PIN
- IP kamery generují **dynamické obrázky** a **MJPEG stream**
- Shelly měří **spotřebu energie** v reálném čase

## 🔄 Regenerace konfigurace

Pokud potřebujete změnit počet zařízení:

```bash
# Upravte počty v generate-compose-direct.py
python3 generate-compose-direct.py

# Restartujte
sudo docker compose down
sudo docker compose up -d --build
```

## 💾 Záloha

Původní konfigurace je uložena v: `docker-compose.yml.backup`
