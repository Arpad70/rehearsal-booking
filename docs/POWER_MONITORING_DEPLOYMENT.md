# Power Monitoring System - Deployment Guide

Tento dokument popisuje, jak nasadit a používat systém Power Monitoring pro sledování spotřeby energie Shelly zařízení.

## 📋 Přehled Features

✅ **Automatické sběr dat** - Každých 5 minut  
✅ **Analýza energií** - Statistiky spotřeby  
✅ **Temperaturní monitoring** - Sledování teploty zařízení  
✅ **API endpoints** - RESTful přístup k datům  
✅ **Admin dashboard** - Filament UI s grafy  
✅ **Upozornění** - Automatické alerty při překročení limitů  

---

## 🚀 Rychlý Start

### 1. Ověření instalace

```bash
# Zkontrolovat migraci
php artisan migrate:status

# Vytvořit testovací data
php artisan db:seed --class=PowerMonitoringSeeder
```

### 2. Spustit sběr dat

```bash
# Jednorázový sběr z příkazové řádky
php artisan power-monitoring:collect

# Sběr ze specifického zařízení
php artisan power-monitoring:collect --device-id=1
```

### 3. Ověřit data v admin panelu

```
http://rehearsal-app.local/admin/power-monitorings
```

Měli byste vidět tabulku se záznamy o spotřebě energie.

---

## 📊 Admin Panel

### Přístup

```
http://rehearsal-app.local/admin
```

Na dashboardu uvidíte:
- **Power Monitoring Stats** - Přehled aktuální spotřeby
- **Power Consumption Chart** - Graf spotřeby za posledních 24 hodin

### Tabulka Power Monitoring

Navigujte na: Admin → Power Monitoring

Vidíte sloupce:
- **Device** - Zařízení (se filtrem)
- **Channel** - Kanál/relé
- **Power** - Aktuální spotřeba [W]
- **Total Energy** - Kumulativní energie [kWh]
- **Temperature** - Teplota zařízení [°C]
- **On** - Je zapnuto
- **Status** - normal/warning/alert

Kliknutím na řádek zobrazíte detaily.

---

## 🔌 API Endpoints

Všechny API endpointy vyžadují autentifikaci: `Authorization: Bearer TOKEN`

### Sběr dat

```bash
# Sběr ze všech zařízení
POST /api/v1/power-monitoring/collect

# Sběr z konkrétního zařízení
POST /api/v1/power-monitoring/collect/1
```

Response:
```json
{
  "success": true,
  "message": "Power data collected from 2 devices",
  "devices_collected": 2
}
```

### Získání dat

```bash
# Poslední data pro zařízení
GET /api/v1/power-monitoring/1/latest

# Historická data (poslední 100 záznamů)
GET /api/v1/power-monitoring/1?limit=100

# Data pro konkrétní kanál
GET /api/v1/power-monitoring/1/channel/0?limit=50
```

Response (latest):
```json
{
  "success": true,
  "device_id": 1,
  "data": {
    "power_w": 450.5,
    "power_formatted": "450 W",
    "energy_total": 12345.67,
    "energy_total_formatted": "12.35 kWh",
    "temperature_c": 42.5,
    "status": "normal",
    "created_at": "2025-11-19T10:30:00Z"
  }
}
```

### Statistiky

```bash
# Energetické statistiky (poslední 30 dní)
GET /api/v1/power-monitoring/1/stats/energy?days=30

# Teplotní statistiky (poslední 24 hodin)
GET /api/v1/power-monitoring/1/stats/temperature?hours=24

# Denní souhrny (posledních 30 dní)
GET /api/v1/power-monitoring/1/daily?days=30
```

### Upozornění

```bash
# Aktuální upozornění zařízení
GET /api/v1/power-monitoring/1/alerts
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
      "severity": "warning"
    }
  ]
}
```

---

## ⚙️ Konfigurace

### Environment Variables

V `.env` nastavte:

```bash
# Shelly Gateway API
SHELLY_GATEWAY_URL=http://192.168.1.100
SHELLY_AUTH_TOKEN=your_token_here  # Volitelné

# Queue (pro background jobs)
QUEUE_CONNECTION=database
```

### Naplánované úlohy (Scheduler)

V `app/Console/Kernel.php` je již nastaveno:

```php
$schedule->job(new CollectPowerMonitoringDataJob())
    ->everyFiveMinutes()
    ->name('collect-power-data')
    ->withoutOverlapping();
```

Spusťte Laravel scheduler (obvykle v cronu):

```bash
* * * * * /usr/bin/php /path/to/artisan schedule:run >> /dev/null 2>&1
```

---

## 🔧 Ruční Operace

### Inicializace dat

```bash
# Vytvořit zařízení
php artisan db:seed --class=DeviceSeeder

# Vytvořit simulovaná data
php artisan db:seed --class=PowerMonitoringSeeder

# Všechny seedery
php artisan db:seed
```

### Sběr dat

```bash
# Sběr ze všech zařízení
php artisan power-monitoring:collect

# Sběr z konkrétního zařízení
php artisan power-monitoring:collect --device-id=1
```

### Čistění starých dat

```bash
# V artisan tinker:
$service = new \App\Services\PowerMonitoringService();
$service->cleanupOldData(90); // Smazat starší než 90 dní
```

---

## 📈 Příklady Použití

### 1. Monitorování spotřeby místnosti

```php
$room = Room::with('devices')->find(1);

foreach ($room->devices as $device) {
    $stats = PowerMonitoring::where('device_id', $device->id)
        ->where('created_at', '>=', now()->subHours(24))
        ->avg('power');
    
    echo "Room {$room->name}: Average power {$stats}W\n";
}
```

### 2. Detekce anomálií

```php
$alerts = PowerMonitoring::where('status', '!=', 'normal')
    ->where('created_at', '>=', now()->subHours(1))
    ->get();

foreach ($alerts as $alert) {
    // Poslat notifikaci
    Notification::send($users, new PowerAlertNotification($alert));
}
```

### 3. Analýza denní spotřeby

```php
$dailyStats = PowerMonitoring::selectRaw(
    'DATE(created_at) as date, SUM(energy_today) as total_energy, AVG(power) as avg_power'
)
->where('device_id', 1)
->groupBy('date')
->orderBy('date', 'desc')
->limit(30)
->get();

foreach ($dailyStats as $day) {
    echo "{$day->date}: {$day->total_energy}Wh (avg {$day->avg_power}W)\n";
}
```

---

## 🐛 Troubleshooting

### Problém: "Nelze se připojit k Shelly zařízení"

```
ŘEŠENÍ:
1. Ověřit IP adresu v tabulce devices
2. Ověřit, zda je zařízení online
3. Zkontrolovat firewall
```

### Problém: "Žádná data se nesbírají"

```
ŘEŠENÍ:
1. Zkontrolovat logs: storage/logs/laravel.log
2. Spustit: php artisan power-monitoring:collect
3. Ověřit, zda zařízení mají type='shelly'
```

### Problém: Scheduler nespouští úlohy

```
ŘEŠENÍ:
1. Zkontrolovat cron: crontab -e
2. Zkontrolovat queue: php artisan queue:work
3. Spustit ručně: php artisan power-monitoring:collect
```

---

## 📚 Struktura Databáze

### Tabulka `power_monitoring`

| Sloupec | Typ | Popis |
|---------|-----|-------|
| id | int | Primary key |
| device_id | int | Odkaz na zařízení |
| room_id | int | Odkaz na místnost |
| channel | int | Kanál/relé číslo |
| power | decimal | Výkon v W |
| energy_total | decimal | Celková energie v Wh |
| energy_today | decimal | Dnešní energie v Wh |
| temperature | decimal | Teplota v °C |
| status | enum | normal/warning/alert |
| created_at | timestamp | Čas měření |

---

## 🔐 Bezpečnost

### API Autentifikace

```bash
# Geneirovat Sanctum token
php artisan tinker
$token = User::first()->createToken('power-api');
echo $token->plainTextToken;
```

### Autorizace

Všechny API endpointy vyžadují `auth:sanctum` middleware. Ověřte permissions v PolicyPowerMonitoring (pokud existuje).

---

## 📋 Podpora

Pro problémy nebo dotazy:

1. Zkontrolujte logs: `storage/logs/laravel.log`
2. Spusťte diagnostiku: `php artisan tinker`
3. Ověřte databázi: `php artisan tinker`

```php
DB::table('power_monitoring')->count();  // Počet záznamů
DB::table('devices')->where('type', 'shelly')->count();  // Počet zařízení
```

---

## 📝 Changelog

### v1.0.0 (2025-11-19)
- ✅ Inicální release
- ✅ Power monitoring seeder
- ✅ API endpoints
- ✅ Admin dashboard widgets
- ✅ Filament resource
- ✅ Scheduled data collection
