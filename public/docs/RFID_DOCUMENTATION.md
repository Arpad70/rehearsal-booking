# RFID Manager - Dokumentace

## 📋 Přehled

Systém pro správu RFID tagů a sledování vybavení zkušeben přes USB RFID čtečku.

## 🔌 API Endpointy

### Veřejné endpointy (bez autentizace)

#### 1. Přečíst RFID tag
```http
POST /api/v1/rfid/read
Content-Type: application/json

{
  "rfid_tag": "RFID-SM58-001"
}
```

**Odpověď (úspěch):**
```json
{
  "success": true,
  "rfid_tag": "RFID-SM58-001",
  "equipment": {
    "id": 1,
    "name": "Shure SM58",
    "description": "Dynamický mikrofon",
    "category": {
      "id": 1,
      "name": "Audio - Mikrofony a reproboxy",
      "icon": "🔊"
    },
    "model": "SM58-LC",
    "serial_number": "SN12345",
    "status": "available",
    "location": "Zkušebna 1",
    "is_critical": false,
    "quantity_available": 2
  }
}
```

**Odpověď (nenalezeno):**
```json
{
  "success": false,
  "error": "RFID tag nenalezen v databázi",
  "rfid_tag": "RFID-UNKNOWN-999",
  "suggestion": "Zaregistrujte tento tag v admin panelu"
}
```

#### 2. Zkontrolovat dostupnost tagu
```http
POST /api/v1/rfid/check-availability
Content-Type: application/json

{
  "rfid_tag": "RFID-NEW-001"
}
```

**Odpověď (dostupný):**
```json
{
  "available": true,
  "rfid_tag": "RFID-NEW-001"
}
```

**Odpověď (obsazený):**
```json
{
  "available": false,
  "rfid_tag": "RFID-SM58-001",
  "used_by": {
    "id": 1,
    "name": "Shure SM58",
    "category": "Audio - Mikrofony a reproboxy"
  }
}
```

---

### Chráněné endpointy (vyžadují Sanctum token)

#### 3. Zapsat RFID tag
```http
POST /api/v1/rfid/write
Authorization: Bearer YOUR_SANCTUM_TOKEN
Content-Type: application/json

# Varianta A: Přiřadit k existujícímu vybavení
{
  "rfid_tag": "RFID-SM58-002",
  "equipment_id": 5
}

# Varianta B: Vytvořit nové vybavení
{
  "rfid_tag": "RFID-NEW-001",
  "equipment_name": "Nový mikrofon",
  "category_id": 1,
  "description": "Popis",
  "model": "Model XYZ",
  "serial_number": "SN999",
  "location": "Sklad"
}
```

**Odpověď (aktualizace):**
```json
{
  "success": true,
  "action": "updated",
  "message": "RFID tag přiřazen k existujícímu vybavení",
  "rfid_tag": "RFID-SM58-002",
  "equipment": {
    "id": 5,
    "name": "Shure SM58",
    "rfid_tag": "RFID-SM58-002"
  }
}
```

**Odpověď (vytvoření):**
```json
{
  "success": true,
  "action": "created",
  "message": "Nové vybavení vytvořeno s RFID tagem",
  "rfid_tag": "RFID-NEW-001",
  "equipment": {
    "id": 26,
    "name": "Nový mikrofon",
    "rfid_tag": "RFID-NEW-001"
  }
}
```

#### 4. Výpůjčka vybavení
```http
POST /api/v1/rfid/checkout
Authorization: Bearer YOUR_SANCTUM_TOKEN
Content-Type: application/json

{
  "rfid_tag": "RFID-SM58-001",
  "user_id": 2,
  "room_id": 1  // volitelné
}
```

**Odpověď:**
```json
{
  "success": true,
  "action": "checked_out",
  "equipment": {
    "id": 1,
    "name": "Shure SM58",
    "rfid_tag": "RFID-SM58-001"
  }
}
```

#### 5. Vrácení vybavení
```http
POST /api/v1/rfid/checkin
Authorization: Bearer YOUR_SANCTUM_TOKEN
Content-Type: application/json

{
  "rfid_tag": "RFID-SM58-001",
  "user_id": 2
}
```

**Odpověď:**
```json
{
  "success": true,
  "action": "checked_in",
  "equipment": {
    "id": 1,
    "name": "Shure SM58",
    "rfid_tag": "RFID-SM58-001"
  }
}
```

---

## 🖥️ Web rozhraní

### Přístup
Otevřete v prohlížeči: **http://localhost:8090/rfid-manager.html**

### Funkce

#### 📖 Číst Tag
- Slouží k vyhledání vybavení podle RFID tagu
- USB čtečka automaticky vyplní pole
- Zobrazí detail vybavení (název, kategorie, model, umístění, atd.)
- **Nevyžaduje přihlášení**

#### ✍️ Zapsat Tag
- Přiřazení RFID tagu k vybavení
- Možnost aktualizovat existující nebo vytvořit nové
- Kontrola dostupnosti tagu
- **Vyžaduje autentizaci** (Sanctum token)

#### 📤 Výpůjčka
- Zalogování výpůjčky/vrácení vybavení
- Dva režimy: Výpůjčka (checkout) a Vrácení (checkin)
- Ukládá info o uživateli a místnosti
- **Vyžaduje autentizaci** (Sanctum token)

#### 📊 Historie
- Zobrazení historie skenování
- Ukládá se do localStorage prohlížeče
- Zobrazuje čas, akci a vybavení

---

## 🔧 Nastavení USB RFID čtečky

### Typy čteček

#### 1. **Keyboard Emulation Mode** (nejjednodušší)
Čtečka se chová jako klávesnice:
- Připojte USB čtečku
- Klikněte do vstupního pole
- Přiložte RFID tag
- Čtečka automaticky "napíše" kód

**Výhody:** Nevyžaduje instalaci driverů, funguje okamžitě

**Příklad čteček:**
- ACR122U (NFC)
- HID ProxPoint Plus
- RFID-RC522 s USB

#### 2. **API/Serial Mode** (pokročilé)
Čtečka komunikuje přes sériový port nebo vlastní API.

**Python skript pro čtení:**
```python
import serial
import requests

# Připojení k USB čtečce
ser = serial.Serial('/dev/ttyUSB0', 9600)

while True:
    if ser.in_waiting > 0:
        rfid_tag = ser.readline().decode('utf-8').strip()
        
        # Odeslat na API
        response = requests.post(
            'http://localhost:8090/api/v1/rfid/read',
            json={'rfid_tag': rfid_tag}
        )
        
        data = response.json()
        print(f"Vybavení: {data['equipment']['name']}")
```

#### 3. **NFC přes mobil** (nejlevnější řešení)
Použijte Android telefon s NFC:
- Aplikace: **NFC Tools**
- Přečtěte tag mobilem
- Ručně zadejte kód do web rozhraní

---

## 📝 Formát RFID tagů

### Doporučený formát
```
RFID-[ZKRATKA]-[ČÍSLO]

Příklady:
RFID-SM58-001    (Shure SM58 mikrofon #1)
RFID-AKG-001     (AKG mikrofon #1)
RFID-GUITAR-15   (Kytara #15)
RFID-AMP-042     (Zesilovač #42)
```

### Generování nových tagů
```bash
# V admin panelu
http://localhost:8090/admin/equipment
→ Vytvořit nové vybavení
→ Pole "RFID Tag"
→ Zadat: RFID-[KOD]
```

---

## 🔐 Autentizace

Pro chráněné endpointy (write, checkout, checkin) potřebujete Sanctum token.

### Získání tokenu

1. **Přes Tinker:**
```bash
docker exec rehearsal-app php artisan tinker
```

```php
$user = User::find(2);
$token = $user->createToken('rfid-manager')->plainTextToken;
echo $token;
```

2. **Použití v JavaScriptu:**
```javascript
const token = 'YOUR_SANCTUM_TOKEN_HERE';

fetch('http://localhost:8090/api/v1/rfid/write', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`
  },
  body: JSON.stringify({
    rfid_tag: 'RFID-NEW-001',
    equipment_name: 'Nový mikrofon'
  })
});
```

---

## 📊 Logování

Všechny RFID operace se logují do tabulky `access_logs`:

```sql
SELECT 
  al.created_at,
  u.name as user_name,
  e.name as equipment_name,
  al.action,
  al.ip_address
FROM access_logs al
LEFT JOIN users u ON al.user_id = u.id
LEFT JOIN equipment e ON al.equipment_id = e.id
WHERE al.action IN ('rfid_scan', 'checkout', 'checkin')
ORDER BY al.created_at DESC
LIMIT 20;
```

---

## 🛒 Nákup RFID komponent

### Co koupit

1. **USB RFID čtečka** (~500 Kč)
   - ACR122U (NFC) - doporučeno
   - HID ProxPoint Plus
   - Generic USB RFID Reader

2. **RFID tagy** (~5-20 Kč/ks)
   - NTAG215 (NFC kompatibilní)
   - Mifare Classic 1K
   - EM4305 (125 kHz)

3. **Kde koupit:**
   - AliExpress: "USB NFC Reader ACR122U"
   - Amazon.de
   - TME.eu (elektronika)
   - Lokální obchody s bezpečnostní technikou

### Doporučený starter kit
```
1x ACR122U USB čtečka      ~500 Kč
10x NTAG215 NFC tagy       ~100 Kč
-----------------------------------
Celkem:                    ~600 Kč
```

---

## 🧪 Testování

### Test 1: Přečíst existující tag
```bash
curl -X POST http://localhost:8090/api/v1/rfid/read \
  -H "Content-Type: application/json" \
  -d '{"rfid_tag": "RFID-SM58-001"}'
```

### Test 2: Zkontrolovat dostupnost
```bash
curl -X POST http://localhost:8090/api/v1/rfid/check-availability \
  -H "Content-Type: application/json" \
  -d '{"rfid_tag": "RFID-NEW-999"}'
```

### Test 3: Web rozhraní
1. Otevřete: http://localhost:8090/rfid-manager.html
2. Záložka "Číst Tag"
3. Zadejte: `RFID-SM58-001`
4. Klikněte "Vyhledat vybavení"

---

## 📞 Podpora

V případě problémů zkontrolujte:
1. ✅ Docker kontejnery běží: `docker ps`
2. ✅ API je dostupné: `curl http://localhost:8090/api/v1/rfid/read`
3. ✅ Logs: `docker logs rehearsal-app`
4. ✅ USB čtečka je rozpoznána: `lsusb` (Linux)

---

## 🎯 Další kroky

- [ ] Vytvořit mobilní aplikaci pro NFC skenování
- [ ] Přidat automatickou inventuru (projít místnost a naskenovat vše)
- [ ] Dashboard s real-time statistikami
- [ ] Notifikace při výpůjčce kritického vybavení
- [ ] Export historie do CSV/Excel
