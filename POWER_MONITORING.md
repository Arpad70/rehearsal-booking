# Power Monitoring API

Power monitoring system pro sběr a analýzu dat o spotřebě energie z Shelly zařízení.

## API Endpoints

### Sběr dat

#### Sběr dat ze všech zařízení
```
POST /api/v1/power-monitoring/collect
```

Příklad:
```bash
curl -X POST http://rehearsal-app.local/api/v1/power-monitoring/collect \
  -H "Authorization: Bearer YOUR_TOKEN"
```

Response:
```json
{
  "success": true,
  "message": "Power data collected from 2 devices",
  "devices_collected": 2
}
```

#### Sběr dat z konkrétního zařízení
```
POST /api/v1/power-monitoring/collect/{deviceId}
```

Příklad:
```bash
curl -X POST http://rehearsal-app.local/api/v1/power-monitoring/collect/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### Získání dat

#### Poslední data pro zařízení
```
GET /api/v1/power-monitoring/{deviceId}/latest
```

Příklad:
```bash
curl http://rehearsal-app.local/api/v1/power-monitoring/1/latest \
  -H "Authorization: Bearer YOUR_TOKEN"
```

Response:
```json
{
  "success": true,
  "device_id": 1,
  "data": {
    "id": 123,
    "channel": 0,
    "power_w": 450.5,
    "power_formatted": "450 W",
    "voltage_v": 230.5,
    "current_a": 1.95,
    "energy_total": 12345.67,
    "energy_total_formatted": "12.35 kWh",
    "is_on": true,
    "temperature_c": 42.5,
    "status": "normal",
    "consumption_status": "normal",
    "created_at": "2025-11-19T10:30:00Z"
  }
}
```

#### Historická data pro zařízení
```
GET /api/v1/power-monitoring/{deviceId}?limit=100
```

Vrací poslední 100 měření (výchozí).

#### Data pro konkrétní kanál
```
GET /api/v1/power-monitoring/{deviceId}/channel/{channel}?limit=50
```

Příklad:
```bash
curl http://rehearsal-app.local/api/v1/power-monitoring/1/channel/0?limit=50 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### Statistiky

#### Statistiky energií
```
GET /api/v1/power-monitoring/{deviceId}/stats/energy?days=30
```

Response:
```json
{
  "success": true,
  "device_id": 1,
  "period_days": 30,
  "stats": {
    "total_energy": 12345.67,
    "today_energy": 450.5,
    "month_energy": 8900.0,
    "average_power": 345.25,
    "max_power": 2100.0,
    "min_power": 50.0,
    "measurements_count": 432
  }
}
```

#### Statistiky teploty
```
GET /api/v1/power-monitoring/{deviceId}/stats/temperature?hours=24
```

Response:
```json
{
  "success": true,
  "device_id": 1,
  "period_hours": 24,
  "stats": {
    "current_temp": 42.5,
    "average_temp": 40.2,
    "max_temp": 45.8,
    "min_temp": 38.1,
    "limit_temp": 80.0
  }
}
```

#### Denní souhrn energií
```
GET /api/v1/power-monitoring/{deviceId}/daily?days=30
```

Response:
```json
{
  "success": true,
  "device_id": 1,
  "days": 30,
  "data": [
    {
      "date": "2025-11-19",
      "energy_wh": 4500.5,
      "avg_power_w": 187.5
    },
    {
      "date": "2025-11-18",
      "energy_wh": 5200.0,
      "avg_power_w": 216.7
    }
  ]
}
```

---

### Upozornění

#### Aktuální upozornění zařízení
```
GET /api/v1/power-monitoring/{deviceId}/alerts
```

Response:
```json
{
  "success": true,
  "device_id": 1,
  "alerts_count": 2,
  "alerts": [
    {
      "type": "excessive_power",
      "power_w": 2500,
      "threshold_w": 2000,
      "severity": "warning"
    },
    {
      "type": "overheating",
      "temperature_c": 76.5,
      "limit_c": 80,
      "severity": "critical"
    }
  ]
}
```

---

## Console Commands

### Sběr dat z příkazové řádky

```bash
# Sběr ze všech zařízení
php artisan power-monitoring:collect

# Sběr z konkrétního zařízení
php artisan power-monitoring:collect --device-id=1
```

---

## Naplánované úlohy

Přidejte do `app/Console/Kernel.php`:

```php
$schedule->job(new CollectPowerMonitoringDataJob)
    ->everyFiveMinutes()
    ->withoutOverlapping();
```

---

## Modely a Services

### PowerMonitoring Model

```php
$record = PowerMonitoring::where('device_id', 1)
    ->orderBy('created_at', 'desc')
    ->first();

echo $record->power; // Spotřeba v W
echo $record->getFormattedPower(); // Formatované (např. "450 W")
echo $record->energy_total; // Celková energie v Wh
echo $record->getFormattedEnergy(); // Formatované (např. "12.35 kWh")
echo $record->temperature; // Teplota v °C
echo $record->getConsumptionStatus(); // Stav: offline, standby, low, normal, high
```

### PowerMonitoringService

```php
$service = new PowerMonitoringService();

// Sběr dat
$service->collectDeviceData($device);
$service->collectRoomData($roomId);
$service->collectAllData();

// Analýza dat
$stats = $service->getEnergyStats($device, 30); // Posledních 30 dní
$temps = $service->getTemperatureStats($device, 24); // Posledních 24 hodin
$data = $service->getLatestData($device, 100); // Posledních 100 záznamů

// Detekce problémů
$powerAlert = $service->checkExcessivePowerConsumption($device, 2000);
$tempAlert = $service->checkOverheating($device);

// Vyčištění starých dat
$service->cleanupOldData(90); // Smazat data starší než 90 dní
```

---

## Stavové statusy

- `normal` - Normální provoz
- `warning` - Zvýšená teplota nebo spotřeba
- `alert` - Kritický stav

## Stavy spotřeby

- `offline` - Zařízení je vypnuté/nedostupné
- `standby` - Pohotovostní režim (< 10W)
- `low` - Nízká spotřeba (10-500W)
- `normal` - Normální spotřeba (500-2000W)
- `high` - Vysoká spotřeba (> 2000W)

---

## Příklady použití

### Monitorování spotřeby místnosti

```php
$room = Room::with('devices')->find(1);

foreach ($room->devices as $device) {
    if ($device->type !== 'shelly') continue;
    
    $service = new PowerMonitoringService();
    $latest = $service->getLatestData($device, 1)->first();
    
    if ($latest && $latest->power > 2000) {
        // Upozornit na vysokou spotřebu
    }
}
```

### Analýza denní spotřeby

```php
$device = Device::find(1);
$service = new PowerMonitoringService();

$daily = PowerMonitoring::getDailyEnergy($device->id, 30);

foreach ($daily as $day) {
    echo "{$day->date}: {$day->energy_wh}Wh (avg {$day->avg_power_w}W)\n";
}
```

### Automatické vypínání při překročení spotřeby

```php
$device = Device::find(1);
$service = new PowerMonitoringService();

if ($service->checkExcessivePowerConsumption($device, 2500)) {
    ShellyGen2Service::turnOff($device, 0);
    // Odeslat notifikaci
}
```

---

## Databáze

Tabulka `power_monitoring` uchovává:
- Napětí, proud, výkon, účiník
- Kumulativní energii (celkem, dnes, měsíc)
- Stav relé (zapnuto/vypnuto)
- Teplotu zařízení
- Metadata a raw data
- Timestamp každého měření

Navržena pro dlouhodobé uchovávání s indexy na device_id, channel a created_at.
