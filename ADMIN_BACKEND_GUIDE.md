# 🎭 Admin Backend - Joomla com_zkusebny Style

## Přehled

Admin backend aplikace byl komplexně přepracován podle vzoru Joomla komponenty `com_zkusebny`. Nový design poskytuje pokročilé funkce pro správu QR čteček, přístupu a monitorování se sofistikovaným uživatelským rozhraním.

## 📊 Admin Dashboard

### Lokace
- **URL:** `/admin/admin-dashboard`
- **Navigace:** Admin Panel (domovská stránka)

### Statistiky a Metriky
```
┌─────────────────────────────────────────────────────────────┐
│  📊 Admin Panel                                             │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │ Přístupy     │  │ Chyby        │  │ Přístupy     │      │
│  │ dnes         │  │ dnes         │  │ týden        │      │
│  │ [ČÍSLO] ↑    │  │ [ČÍSLO] ⚠️   │  │ [ČÍSLO] 📅   │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
│                                                             │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │ Čtečky       │  │ Aktivní       │  │ Servisní     │      │
│  │ online       │  │ upozornění    │  │ přístupy     │      │
│  │ [X/Y] 📡     │  │ [N] 🔔        │  │ [M] 🔧      │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│  🔓 Poslední pokusy o přístup                              │
├─────────────────────────────────────────────────────────────┤
│  Uživatel  │ Místnost │ Čtečka │ Status │ Čas            │
│  ────────────────────────────────────────────────────────  │
│  Jan Novák │ Studio A │ room   │ ✅     │ 14:32:15       │
│  ...       │ ...      │ ...    │ ...    │ ...            │
└─────────────────────────────────────────────────────────────┘
```

### Quick Actions
- 🚪 **Čtečky místností** - Správa room readers
- 🌐 **Globální čtečky** - Správa global readers
- 🔧 **Servisní přístupy** - Správa service access
- ⚠️ **Upozornění** - Řešení alert notifikací

---

## 🚪 Správa Čteček Místností (Room Readers)

### EditRoomReader Formulář

#### Sekce 1: 📍 Informace o čtečce
```
┌─────────────────────────────────────┐
│ Základní údaje o QR čtečce         │
├─────────────────────────────────────┤
│ Jméno čtečky: [MainDoor-01      ]  │
│ Místnost:     [Studio A ▼       ]  │
│ Aktivní:      [☑] Zapnutá        │
└─────────────────────────────────────┘
```

**Pole:**
- `reader_name` - Unikátní identifikátor
- `room_id` - Vazba na místnost (searchable select)
- `enabled` - Toggle pro aktivaci/deaktivaci

#### Sekce 2: 🌐 Síťové nastavení
```
┌──────────────────────────────────────────────────┐
│ Připojení k čtečce zařízení                     │
├──────────────────────────────────────────────────┤
│ IP adresa:        [192.168.1.100        ]       │
│ Port:             [8080                 ]       │
│ Bezpečnostní token: [••••••••••••••••   ]       │
└──────────────────────────────────────────────────┘
```

**Validace:**
- IP: Kontrola IPv4 formátu
- Port: 1-65535
- Token: Povinný, skrytý input se zobrazením

#### Sekce 3: 🔓 Konfigurace zámku

**Výběr typu zámku:**
```
Typ zámku: [🔌 Relay / 📡 API / 🪝 Webhook ▼]
```

##### a) Relay (GPIO/Arduino/Shelly)
```
┌──────────────────────────────────────────────┐
│ URL relaye:    [http://192.168.1.100:8080...│
│ GPIO pin:      [1                            │
│ Doba otevření: [5 sekund                    │
│ Metoda:        [GET (disabled)              │
└──────────────────────────────────────────────┘
```

##### b) Smart Lock API
```
┌──────────────────────────────────────────────┐
│ API URL:       [https://api.smartlock.com...│
│ API klíč:      [••••••••••••••••           │
│ Lock ID:       [room_123                    │
│ Doba otevření: [5 sekund                    │
└──────────────────────────────────────────────┘
```

##### c) Webhook (Home Assistant)
```
┌──────────────────────────────────────────────┐
│ Webhook URL:   [https://webhook.example...  │
│ Secret (HMAC): [••••••••••••••••           │
│ Doba otevření: [5 sekund                    │
└──────────────────────────────────────────────┘
```

### Header Actions
```
[🧪 Test připojení] [🔓 Testuj odemčení] [❌ Smazat]
```

**Funkčnost:**
- **Test připojení** - Zkontroluje dostupnost čtečky
- **Test odemčení** - Vyšle unlock command (s potvrzením)
- **Smazat** - Smaže čtečku z databáze

---

## 🌐 Správa Globálních Čteček (Global Readers)

### Speciální Pole

#### Sekce: ⏰ Nastavení přístupu
```
┌──────────────────────────────────────────────┐
│ Přístup před začátkem:  [15       ] minut   │
│ Přístup po konci:       [15       ] minut   │
│ Povolit vícenásobný:    [☑] Ano          │
└──────────────────────────────────────────────┘
```

**Případ užití:**
- Obsluha může vstoupit 15 minut před a 15 minut po rezervaci
- Vícenásobný přístup: stejný QR kód mohou použít více osob

---

## 🔧 Správa Servisních Přístupů (Service Access)

### EditServiceAccess Formulář

#### Sekce 1: 👤 Údaje o přístupu
```
┌──────────────────────────────────────────────┐
│ Jméno:        [Jan Novák                  ] │
│ Typ přístupu: [🧹 Čištění / 🔧 Údržba / 👨] │
│ Email:        [jan@example.com            ] │
│ Telefon:      [+420 777 777 777           ] │
└──────────────────────────────────────────────┘
```

#### Sekce 2: ⏰ Platnost přístupu
```
┌──────────────────────────────────────────────┐
│ Platný od:      [2025-01-15 08:00     📅]   │
│ Platný do:      [2025-03-15 18:00     📅]   │
│ Počet přístupů: [Neomezeno              ]   │
│ Aktivní:        [☑] Ano                   │
└──────────────────────────────────────────────┘
```

#### Sekce 3: 🚪 Přístup do místností
```
┌──────────────────────────────────────────────┐
│ [☑] Přístup do všech místností             │
│                                             │
│ Nebo (pokud vypnuto):                       │
│ ☐ Studio A                                 │
│ ☐ Studio B                                 │
│ ☑ Fotka                                    │
│ ☑ Kancelář                                 │
└──────────────────────────────────────────────┘
```

#### Sekce 4: 📋 Poznámky a omezení
```
┌──────────────────────────────────────────────┐
│ Poznámky: [Jen během pracovní doby ▼    ]   │
│ Důvod zrušení: [Automaticky vyplněno   ]   │
│ Zrušeno: [2025-02-15 12:30 (disabled) ]   │
└──────────────────────────────────────────────┘
```

### Header Actions
```
[📱 Vygeneruj QR kód] [❌ Zruš přístup] [🗑️ Smazat]
```

**Zrušení přístupu - Modal formulář:**
```
┌──────────────────────────────────────────────┐
│ Důvod zrušení:                              │
│ ┌──────────────────────────────────────────┐│
│ │ Např: Ukončení pracovní smlouvy        ││
│ └──────────────────────────────────────────┘│
│ [Zruš přístup] [Zrušit]                   │
└──────────────────────────────────────────────┘
```

---

## 💾 Správa Záložních QR Kódů (Backup QR Codes)

### Header Actions
```
[🔄 Vygeneruj zálohy] [📥 Export všech] [➕ Nový]
```

### Funkčnost
- **Vygeneruj zálohy** - Vytvoří záložní QR pro rezervace bez záloh
- **Export všech** - Stáhne ZIP soubor se všemi QR obrázky
  - Strukturování: `backup_qr_{id}_{sequence}.png`
  - Komprese pro přenos

---

## ⚠️ Správa Upozornění (Reader Alerts)

### ReaderAlertResource List

#### Sloupcová Schéma
```
┌────────────────────────────────────────────────────────┐
│ Typ │ Čtečka │ Závažnost │ Status │ Zpráva │ Čas    │
├────────────────────────────────────────────────────────┤
│ 🔌  │ Main-1 │ Kritická  │ ⏳     │ Sem...│ 14:32 │
│ 📈  │ Door-2 │ Vysoká    │ ✅     │ Sop...│ 13:15 │
│ ❌  │ Front  │ Kritická  │ ⏳     │ Čte...│ 12:00 │
└────────────────────────────────────────────────────────┘
```

#### Filtry
- **Typ upozornění:** connection_failed, high_failure_rate, offline, configuration_error
- **Závažnost:** critical, high, medium, low
- **Stav:** Vyřešeno / Čeká na řešení
- **Smazané:** Zobrazit/skrýt

#### Header Actions
```
[✅ Vyřeš všechny] [➕ Nový]
```

**Vyřeš všechny** - Označí všechny aktivní upozornění jako vyřešená

### EditReaderAlert Formulář

#### Sekce 1: ⚠️ Podrobnosti
```
┌──────────────────────────────────────────┐
│ Typ čtečky:   [room_reader (disabled)  ] │
│ ID čtečky:    [1 (disabled)            ] │
│ Typ upozor.:  [connection_failed      ] │
│ Závažnost:    [critical               ] │
│                                        │
│ Zpráva:                                │
│ ┌────────────────────────────────────┐│
│ │ Selhání připojení k čtečce...     ││
│ └────────────────────────────────────┘│
└──────────────────────────────────────────┘
```

#### Sekce 2: 🔧 Řešení
```
┌──────────────────────────────────────────┐
│ Vyřešeno: [☑] Ano                      │
│                                        │
│ Poznámky k řešení:                     │
│ ┌────────────────────────────────────┐│
│ │ Restart čtečky vyřešil problém   ││
│ └────────────────────────────────────┘│
│                                        │
│ Čas vyřešení: [2025-01-15 14:45     ] │
└──────────────────────────────────────────┘
```

---

## 🎨 Design & UI Prvky

### Barvové Schéma
- **Success (Zelená):** ✅ Úspěch, online, vyřešeno
- **Warning (Oranžová):** ⚠️ Pozor, vysoká chybovost
- **Danger (Červená):** ❌ Kritické, offline, chyba
- **Info (Modrá):** ℹ️ Informace, test
- **Primary (Fialová):** 👨 Primární akce

### Ikonografické Prvky
- 🚪 Čtečky místností (Room readers)
- 🌐 Globální čtečky (Global readers)
- 🔧 Servisní přístupy (Service access)
- ⚠️ Upozornění (Alerts)
- 📊 Dashboard, statistiky
- 🧪 Test akce
- 📱 QR kódy
- 🔓 Odemčení

---

## 🔧 Technická Implementace

### Filament Resources
```
app/Filament/Resources/
├── RoomReaderResource.php (enhanced)
├── RoomReaderResource/Pages/
│   └── EditRoomReader.php (pokročilý formulář)
├── GlobalReaderResource.php (enhanced)
├── GlobalReaderResource/Pages/
│   └── EditGlobalReader.php (pokročilý formulář)
├── ServiceAccessResource.php (enhanced)
├── ServiceAccessResource/Pages/
│   └── EditServiceAccess.php (pokročilý formulář)
├── BackupQRCodeResource.php (enhanced)
├── BackupQRCodeResource/Pages/
│   └── ListBackupQRCodes.php (export actions)
├── ReaderAlertResource.php (nový)
└── ReaderAlertResource/Pages/
    ├── ListReaderAlerts.php
    ├── CreateReaderAlert.php
    └── EditReaderAlert.php
```

### Admin Pages
```
app/Filament/Pages/
└── AdminDashboard.php (nová)
```

### Views
```
resources/views/filament/pages/
└── admin-dashboard.blade.php (nová)
```

### Model Extensions
```php
// BackupQRCode.php
BackupQRCode::generateMissingBackups(): int
BackupQRCode::exportAsZip(): string

// RoomReader.php
RoomReader::testConnection(): array
RoomReader::unlockDoor(): array

// GlobalReader.php
GlobalReader::testConnection(): array
```

---

## 🚀 Použití v Praxi

### Typický workflow čtečky:

1. **Instalace čtečky:**
   - Adminer vytvoří nový Room Reader záznam
   - Vyplní IP adresu, port, token
   - Vybere typ zámku (relay/API/webhook)
   - Vyplní lock-specific konfiguraci

2. **Testování:**
   - Klikne na "Test připojení" → ověří dostupnost
   - Klikne na "Test odemčení" → testuje lock funcionality

3. **Monitorování:**
   - Sleduje alerts na AdminDashboard
   - Řeší problémy v ReaderAlerts
   - Kontroluje access logs v tabulce

4. **Správa servisních přístupů:**
   - Vytvoří nový ServiceAccess záznam
   - Vybere osobu, typ (čištění/údržba)
   - Nastaví časové okno
   - Vybere místnosti
   - Klikne "Vygeneruj QR" → odešle QR e-mailem

5. **Zrušení přístupu:**
   - Klikne na "Zruš přístup"
   - Zadá důvod (např. "Ukončení smlouvy")
   - Systém automaticky zakáže QR

---

## 📋 Kontrolní List

- ✅ AdminDashboard s statistikami
- ✅ RoomReader pokročilý formulář
- ✅ GlobalReader s časovými okny
- ✅ ServiceAccess s revokací
- ✅ BackupQRCode s exportem ZIP
- ✅ ReaderAlertResource s monitoringem
- ✅ Test connection akce
- ✅ Test unlock akce
- ✅ QR generate akce
- ✅ Dashboard v navigaci
- ✅ Všechny akce s potvrzenímem
- ✅ Validace polí
- ✅ Dark mode podpora
- ✅ Mobile responsivní
- ✅ České texty a popisy

---

## 🎯 Vlastnosti Inspirované com_zkusebny

1. **Podrobné konfigurace** - Několik oblastí nastavení na stránce
2. **Podmíněné formuláře** - Lišící se pole dle vybraného typu
3. **Testovací akce** - Ověření funkcionality přímo z admin rozhraní
4. **Centralizovaný monitoring** - Přehled o celém systému na jedné stránce
5. **Hromadné operace** - Export, generování, řešení najednou
6. **Detailní logy** - Kompletní záznam všech akcí
7. **Intuitivní UI** - Emoji ikony, jasná strukturace, barevné rozlišení
8. **Modální formuláře** - Potvrzen akce (test, zrušení, smazání)
9. **Rychlé akce** - Quick action menu na hlavní stránce
10. **Autentifikace** - Filament role/permissions support

---

## 📞 Support

Pokud narazíte na problém s admin backendem:
1. Zkontrolujte ReaderAlerts
2. Ověřte network connectivity v Dashboard
3. Zkontrolujte server logs
4. Kontaktujte administrátora
