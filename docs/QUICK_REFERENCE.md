# QR Reader System - Quick Reference Guide

## ⚡ Super rychlý přehled (5 minut)

### Co máš?
Kompletní QR Reader systém s:
- 🎫 QR generování pro rezervace + emailing
- 🔓 Odemykání dveří (Relay, API, Webhook)
- 👥 Servisní přístupy (čištění, údržba, admin)
- 📊 Dashboard s metrikami
- 🚨 Monitoring & alerts
- 📈 Detailní reporting

### Jak to funguje?
1. Rezervace je vytvořena → Email s QR kódem
2. Host naskenuje QR → Server ověří
3. Server odemkne dveře → Host vstoupí
4. Pokus je zalogged → Vidí se v reports

### Součásti systému

| Komponenta | Co dělá | Kde |
|------------|--------|-----|
| **RoomReader** | Čtečka u místnosti | `/admin` → QR Reader → Room Readers |
| **GlobalReader** | Globální přístup | `/admin` → QR Reader → Global Readers |
| **ServiceAccess** | Servisní přístup | `/admin` → Access Control → Service Access |
| **Dashboard** | Metriky & grafy | `/admin` (home page) |
| **Reports** | Detail analýza | `/admin` → Reports |

---

## 🛠️ Setup (10 minut)

### 1. Instalace
```bash
cd /mnt/data/www/rehearsal-app
composer install
npm install
php artisan migrate
```

### 2. Queue worker (pro emaily)
```bash
php artisan queue:work --queue=emails
# V produkci: supervisor config
```

### 3. Spustit
```bash
php artisan serve
# Otevřít: http://localhost:8000/admin
```

---

## 📱 Admin Interface (30 sekund)

Všechno je v `/admin` Filament dashboardu:

```
Dashboard (home)
├── Stats widgets (dnes, týden, měsíc)
├── Trend graf (7 dní)
└── Active alerts (upozornění)

QR Reader
├── Room Readers (čtečky u místností)
├── Global Readers (vchod, servis, admin)
├── Backup QR Codes (backup kódy)
└── Service Access (personál)

Access Control
└── Service Access (servisní přístupy)

Reports
├── Access Reports (všechny pokusy)
└── Reader Statistics (statistiky čteček)
```

---

## 🔌 Připojení čtečky (15 minut)

### Step 1: Přidat RoomReader v adminu
1. Jdi na `/admin` → QR Reader → Room Readers
2. Klikni "+ Add"
3. Vyplň:
   - Room: (vyber místnost)
   - Reader Name: "MainDoor-01"
   - Reader IP: 192.168.1.100
   - Reader Port: 8080
   - Reader Token: (long secret string)
   - Door Lock Type: relay (vybrat typ)
   - Door Lock Config: (JSON s nastavením)

### Step 2: Test připojení
1. Klikni na reader
2. Dole je tlačítko "Test Connection"
3. Měl by vrátit "online"

### Step 3: Nakonfigurovat zámek

#### Pro Relay (Shelly, Arduino)
```json
{
  "pin": 1,
  "url": "http://192.168.1.100:8080/relay/{pin}/on"
}
```

#### Pro API (Smart Lock)
```json
{
  "url": "https://api.smartlock.com/unlock",
  "api_key": "your_api_key",
  "lock_id": "room_123"
}
```

#### Pro Webhook
```json
{
  "url": "https://webhook.example.com/unlock",
  "secret": "your_webhook_secret"
}
```

---

## 📧 Emaily

### Co se posílá?
1. **Na vytvoření rezervace** → QR kód emailem
2. **Na aktivaci servisního kódu** → Přístupový kód emailem

### Email nastavení (.env)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=app-password
```

### Troubleshooting
```bash
# Queue worker běží?
ps aux | grep "queue:work"

# Pokud ne, spustit znovu
php artisan queue:work --queue=emails

# Jsou joby v databázi?
php artisan tinker
>>> DB::table('jobs')->count();
```

---

## 🔍 Monitoring & Alerts

### Dashboard (vidí se sám)
- Dnes: počet přístupů
- Týden: nové rezervace
- Měsíc: celkové přístupy
- Úspěšnost: % (měla by být > 95%)

### Alerts
- **Offline** - Reader je nedostupný
- **High failure rate** - Chyby (> 10%)
- **No activity** - Žádný pokus (> 12h)
- **Configuration error** - Špatné nastavení

### Ruční monitoring
```bash
# Spustit monitoring
php artisan readers:monitor

# Nebo v tinker
php artisan tinker
>>> App\Models\ReaderAlert::unresolved()->get();
```

---

## 📊 Reports & Stats

### Access Report
Jdi na `/admin` → Reports → Access Reports

Vidíš:
- Všechny pokusy (poslední)
- Filtrování: výsledek, typ, datum
- Export do CSV
- Detail jednotlivých pokusů

### Reader Statistics
Jdi na `/admin` → Reports → Reader Statistics

Vidíš:
- Pokusy za 30 dní
- Úspěšnost %
- Poslední aktivita
- Status reader

---

## 🔐 Bezpečnost - DŮLEŽITÉ!

### Reader Token
- Dlouhý string (32+ znaků)
- Unikátní na čtečku
- Nikdy nesdílej veřejně

### Přístup
- QR je unikátní
- Platný jen určitou dobu (15 min před + do konce)
- Nesdílitelný (server ověřuje)

### IP Whitelist (volitelně)
Nakonfigurovat v .env:
```env
READER_IP_WHITELIST=192.168.1.100,192.168.1.101
```

---

## 🚀 Produkce Checklist

- [ ] Nastavit HTTPS (SSL certifikát)
- [ ] Email konfigurován (SMTP)
- [ ] Database backupy (cron)
- [ ] Queue worker běží (supervisor)
- [ ] Monitoring je aktivní (cron)
- [ ] Storage permissions OK
- [ ] Logs vidí správný format
- [ ] Admin hesla silná

---

## 💻 API pro čtečky

### Endpoint
```
POST http://server:8000/api/v1/qr/validate
```

### Request
```json
{
  "qr_data": "QR data (JSON encoded)",
  "room_id": 1,
  "reader_token": "reader_secret"
}
```

### Response - Success
```json
{
  "access": true,
  "message": "Access granted",
  "door_unlocked": true
}
```

### Response - Error
```json
{
  "access": false,
  "reason": "TOO_EARLY",
  "message": "Access not yet available"
}
```

### Možné důvody
- TOO_EARLY - Přístup není ještě dostupný
- EXPIRED - Rezervace skončila
- INVALID_QR - Neplatný QR format
- WRONG_ROOM - QR je pro jinou místnost
- INVALID_TOKEN - Špatný token

---

## 🐛 Troubleshooting

### "Reader unreachable"
```bash
# Zkontroluj IP
ping 192.168.1.100

# Zkus manuálně
curl http://192.168.1.100:8080/status

# Zkontroluj firewall
# Zkontroluj port (je otevřený?)
```

### "Email není odeslán"
```bash
# Queue worker běží?
ps aux | grep queue:work

# Zkus manuálně
php artisan tinker
>>> Mail::raw('test', function($m) { $m->to('test@example.com'); });
```

### "QR validation failed"
```bash
# Zkontroluj room_id
# Zkontroluj reader_token
# Zkontroluj čas (server vs. čtečka)
```

---

## 📞 Kde najít odpovědi?

| Otázka | Kde hledat |
|--------|-----------|
| Jak se čtečka připojuje? | COMPLETE_DOCUMENTATION.md |
| Jak funguje API? | COMPLETE_DOCUMENTATION.md (API section) |
| Jak nastavit emaily? | QR_IMPLEMENTATION_GUIDE.md |
| Kde jsou logy? | storage/logs/laravel.log |
| Jak debugovat? | `php artisan tinker` |
| Jak backupovat? | COMPLETE_DOCUMENTATION.md (Database) |

---

## 🎓 Čtení v pořadí

1. **FINAL_SUMMARY.md** - Co je hotovo (2 min)
2. **PHASE_SUMMARY.md** - Detaily jednotlivých fází (10 min)
3. **QR_IMPLEMENTATION_GUIDE.md** - Praktický návod (15 min)
4. **COMPLETE_DOCUMENTATION.md** - Úplné info (30 min)
5. **Kód v `/app`** - Implementační detaily

---

## 🎯 Základní workflow

### Pro správce
1. Přidat čtečku (`/admin` → Room Readers)
2. Test připojení (klik na reader)
3. Monitorovat alerts (home page)
4. Kontrolovat reports (30 dní)

### Pro hosta
1. Vytvořit rezervaci (web)
2. Dostane email s QR
3. Přijít k dveřím
4. Naskenovat QR
5. Dveře se odemknou

### Pro personál
1. Admin vytvoří ServiceAccess
2. Zaměstnanec dostane email s kódem
3. Naskenuje kód na čtečce
4. Má přístup

---

## 🔑 Důležité soubory

```
/app/Models/ - Datové modely
/app/Services/ - Business logika (QR, door, monitoring)
/app/Http/Controllers/Api/ - API endpointy
/app/Jobs/ - Asynchronní joby (emaily)
/app/Mail/ - Email templaty
/app/Filament/Resources/ - Admin interface
/app/Filament/Widgets/ - Dashboard widgets
/database/migrations/ - Database schema
/resources/views/emails/ - Email templates
/config/reservations.php - Konfigurace QR systému
```

---

## ✅ Status

**HOTOVO:** Všechny Phase 1-6 ✅  
**TESTOVANÉ:** Migrují, modely, services ✅  
**DOKUMENTOVANÉ:** 4 dokumenty (2000+ řádků) ✅  
**READY FOR PRODUCTION:** ✅

---

## 🚀 Spustit demo

```bash
# 1. Start dev server
php artisan serve

# 2. Start queue worker (new terminal)
php artisan queue:work --queue=emails

# 3. Start scheduler (new terminal)
while true; do php artisan schedule:run; sleep 60; done

# 4. Otevřít
http://localhost:8000
http://localhost:8000/admin

# 5. Login (create user first)
php artisan tinker
>>> App\Models\User::create([...])
```

---

**Verze:** 1.0  
**Status:** ✅ COMPLETE  
**Poslední update:** 18. listopadu 2025  
**Repository:** https://github.com/Arpad70/rehearsal-booking

