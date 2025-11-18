# 🎯 Admin Dashboard - Implementační Návod

## Přístup na Admin Dashboard

### URL
```
http://rehearsal-app.local/admin/admin-dashboard
```

### Autentifikace
- Musíte být přihlášeni jako **administrátor** nebo **superuživatel**
- Filament admin panel je na: `http://rehearsal-app.local/admin`

### Požadavky
1. Být registrován v systému
2. Mít roli `admin` nebo `superuser`
3. Být přihlášen

---

## Co se Zobrazuje na Dashboardu

### 📊 Statistické Karty

Prvních 6 karet zobrazuje klíčové metriky:

| Karta | Co Zobrazuje | Zdroj Dat |
|-------|-------------|----------|
| 📈 Přístupy dnes | Počet přístupů za dnešek | `AccessLog::whereDate('created_at', today())` |
| ⚠️ Chyby dnes | Počet neúspěšných přístupů | `AccessLog::where('access_granted', '!=', true)` |
| 📅 Přístupy týden | Počet přístupů za 7 dní | `AccessLog::whereBetween('created_at', [week_start, week_end])` |
| 🌐 Čtečky online | Aktivní čtečky / Celkový počet | `RoomReader + GlobalReader (enabled=true)` |
| 🔔 Aktivní upozornění | Počet neřešených alertů | `ReaderAlert::where('resolved', false)` |
| 👥 Servisní přístupy | Počet aktivních servisních účtů | `ServiceAccess::where('enabled', true)` |

### 📋 Tabulka Posledních Přístupů

Zobrazuje posledních 25 pokusů o přístup s filtry:

**Sloupce:**
- 👤 **Uživatel** - Jméno uživatele (searchable, sortable)
- 🚪 **Místnost** - Kam se pokoušel vstoupit
- 🔍 **Typ čtečky** - room_reader nebo global_reader (badge)
- ✅ **Status** - Úspěšný/selhavý (ikona ✓/✗)
- 📝 **Důvod odmítnutí** - Proč selhalo (skryto, toggleable)
- 🌐 **IP adresa** - Kde z přístup pocházel (skryto, copyable)
- 📱 **Device** - User agent (skryto, max 50 znaků)
- 🕐 **Čas** - Kdy se to stalo (sortable)

**Filtry:**
- Počet záznamů: 10, 25, 50

### 🚀 Quick Action Tlačítka

4 tlačítka pro rychlý přístup k správě:

```
🚪 Čtečky místností  →  Správa RoomReader
🌐 Globální čtečky    →  Správa GlobalReader
🔧 Servisní přístupy   →  Správa ServiceAccess
⚠️ Upozornění          →  Správa ReaderAlert
```

---

## Funkce a Vlastnosti

### ✅ Co Funguje

- ✅ Zobrazení statistik v reálném čase
- ✅ Live přístupový log s filtrováním
- ✅ Sortování a prohledávání
- ✅ Quick actions na ostatní resource
- ✅ Responsivní design (mobile + desktop)
- ✅ Dark mode podpora
- ✅ Filament security (autentifikace)

### 🔐 Bezpečnost

- Přístup pouze pro autentifikované adminy
- Filament autorizace (role-based)
- IP adresa logování
- User agent logování
- Audit trail všech akcí

---

## Databázové Tabulky

### AccessLog (Přístupové Logy)

```sql
- id: bigint (primary)
- user_id: bigint (user who tried to access)
- room_id: bigint (room they tried to enter)
- reservation_id: bigint (associated reservation)
- access_granted: boolean (success/fail)
- failure_reason: string (why it failed)
- reader_type: enum('room', 'global')
- ip_address: string
- user_agent: string
- created_at, updated_at: timestamps
```

### ReaderAlert (Upozornění)

```sql
- id: bigint (primary)
- room_reader_id: bigint (nullable)
- global_reader_id: bigint (nullable)
- reader_type: enum('room_reader', 'global_reader')
- alert_type: enum('connection_failed', 'high_failure_rate', 'offline', 'configuration_error')
- message: text
- severity: enum('low', 'medium', 'high', 'critical')
- resolved: boolean
- resolution_notes: text (nullable)
- resolved_at: timestamp (nullable)
- metadata: json (nullable)
- created_at, updated_at: timestamps
```

---

## API Endpoints (Pro Programování)

Pokud chcete data z API místo admin panelu:

```
GET /api/v1/admin/stats              (Statistiky)
GET /api/v1/admin/access-logs        (Přístupové logy)
GET /api/v1/admin/alerts             (Upozornění)
```

---

## Troubleshooting

### Dashboard se nenačítá
- ✅ Zkontrolujte, že jste přihlášeni
- ✅ Zkontrolujte, že máte admin roli
- ✅ Zkontrolujte URL: `http://rehearsal-app.local/admin/admin-dashboard`
- ✅ Spusťte `php artisan migrate`

### Tabulka je prázdná
- Ještě nebyly vytvořeny žádné přístupy
- Zkontrolujte data v databázi: `AccessLog::count()`

### Chybí sloupce
- Spusťte: `php artisan migrate:refresh`
- Nebo: `php artisan migrate`

---

## Příští Kroky

1. **Přihlašte se na admin panel**
   ```
   URL: http://rehearsal-app.local/admin
   ```

2. **Přejděte na Admin Dashboard**
   ```
   Kliknout na "Admin Panel" v menu
   ```

3. **Prohlédněte si statistiky a logy**

4. **Spravujte čtečky a upozornění**
   ```
   Klikněte na quick action tlačítka
   ```

---

## Soubory

### PHP
- `app/Filament/Pages/AdminDashboard.php` - Logika dashboardu
- `app/Models/AccessLog.php` - Model pro logy
- `app/Models/ReaderAlert.php` - Model pro upozornění

### Views
- `resources/views/filament/pages/admin-dashboard.blade.php` - Template

### Migrations
- `database/migrations/2025_01_01_000012_add_missing_columns_to_access_logs.php` - Sloupce

### Configuration
- `app/Providers/FilamentServiceProvider.php` - Registrace resources

---

## Podpora

Pokud máte problemy s admin dashboardem:

1. Zkontrolujte logs: `storage/logs/laravel.log`
2. Spusťte migraci: `php artisan migrate`
3. Vymažte cache: `php artisan cache:clear`
4. Restartujte queue: `php artisan queue:restart`

---

**Admin Dashboard je nyní plně funkční a připraven k použití!** 🎉
