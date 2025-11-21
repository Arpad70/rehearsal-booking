# 📡 RFID Správa - Uživatelská příručka

## Přístup k RFID správě

V administračním panelu najdete RFID správu v menu **Správa vybavení** → **RFID Správa**.

## 🎯 Základní funkce

### 1. Zobrazení RFID tagů
- Tabulka zobrazuje všechno vybavení, které má přiřazený RFID tag
- Můžete filtrovat podle kategorie, stavu nebo kritického vybavení
- RFID tag je možné zkopírovat kliknutím na badge

### 2. Přidání nového RFID tagu

**Manuální zadání:**
1. Klikněte na "Přidat RFID tag"
2. Vyberte vybavení ze seznamu
3. Zadejte RFID tag ručně
4. Uložte

**Automatické načtení z čtečky:**
1. Připojte USB RFID čtečku k počítači
2. Klikněte na "Přidat RFID tag"
3. Klikněte do pole "RFID Tag"
4. Přiložte RFID tag ke čtečce
5. Tag se automaticky načte a vyplní
6. Vyberte vybavení a uložte

### 3. Úprava RFID tagu
1. Klikněte na "Upravit" u vybraného vybavení
2. Změňte RFID tag nebo přiřazení k vybavení
3. Uložte změny

### 4. Odebrání RFID tagu
1. Klikněte na "Odebrat tag" u vybraného vybavení
2. Potvrďte akci
3. RFID tag bude odebrán, vybavení zůstane v databázi

### 5. Test čtení RFID
1. Klikněte na "Test čtení" u vybraného vybavení
2. Systém provede API volání a zobrazí výsledek
3. Ověříte tím, že tag je správně nakonfigurován

## ⚙️ Nastavení čtečky

### Přístup k nastavení
Klikněte na tlačítko **"Nastavení čtečky"** v horní části stránky RFID Správa.

### Podporované čtečky
- **ACR122U** - USB NFC čtečka (~500 Kč)
- **PN532** - NFC/RFID modul
- **RC522** - Levný RFID modul
- **Mobilní NFC** - Android telefon s NFC

### Kde koupit
- **CZ.NIC** - ACR122U (~500 Kč)
- **Aliexpress** - Levnější alternativy (~200 Kč)
- **RFID tagy** - NTAG215 (~5-20 Kč/ks)
- **Starter kit** - Čtečka + 10 tagů (~600 Kč)

### Instalace ovladačů

**Linux:**
```bash
sudo apt-get install libpcsclite1 pcscd
sudo systemctl start pcscd
sudo systemctl enable pcscd
```

**Windows:**
Stáhněte ovladač z webu výrobce čtečky (obvykle není potřeba pro ACR122U).

### Režimy připojení

#### Mód 1: Keyboard Emulation (doporučeno)
- Čtečka funguje jako klávesnice
- Automaticky vyplňuje RFID tag do aktivního pole
- Není potřeba žádný software

**Použití:**
1. Připojte čtečku k USB
2. Klikněte do pole "RFID Tag" v jakémkoli formuláři
3. Přiložte tag ke čtečce
4. Tag se automaticky vyplní

#### Mód 2: Serial Communication
- Připojení přes sériový port
- Vyžaduje Python skript

**Použití:**
```bash
cd python_gateway
pip install pyserial requests
python rfid_scanner.py
```

#### Mód 3: NFC přes mobil
- Použijte Android aplikaci s NFC
- Doporučené aplikace:
  - NFC Tools
  - NFC TagWriter

## 📝 Přidání RFID tagu k vybavení

### V editaci vybavení

1. Otevřete **Správa vybavení** → **Vybavení**
2. Klikněte na vybavení, které chcete upravit
3. V sekci **"Technické údaje"** najdete pole **"RFID Tag"**
4. Máte 3 možnosti:

**Možnost A: Automatické načtení**
- Klikněte na tlačítko **"Načíst z čtečky"** (vedle pole)
- Přiložte RFID tag ke čtečce
- Tag se automaticky vyplní

**Možnost B: Manuální zadání z čtečky**
- Klikněte do pole "RFID Tag"
- Přiložte tag ke čtečce (keyboard emulation mód)
- Tag se automaticky vyplní

**Možnost C: Ruční zadání**
- Napište RFID tag ručně (např. "RFID-NOVYMIC-001")

5. Uložte změny

### Při vytváření nového vybavení

Stejný postup jako u editace - pole "RFID Tag" je k dispozici ve formuláři pro vytvoření nového vybavení.

## 🌐 Web rozhraní pro skenování

### Přístup
Otevřete v prohlížeči: `http://localhost:8090/rfid-manager.html`

### Funkce
1. **Záložka "Čtení"** - Načtení info o vybavení podle RFID tagu
2. **Záložka "Zápis"** - Přiřazení RFID tagu k vybavení
3. **Záložka "Výpůjčky"** - Checkout/Checkin vybavení
4. **Záložka "Historie"** - Zobrazení posledních 50 skenů

## 🔧 Řešení problémů

### Čtečka nefunguje
1. Zkontrolujte, zda je čtečka připojena k USB
2. V Linuxu: `lsusb` - měla by se zobrazit čtečka
3. Zkontrolujte, zda běží pcscd: `sudo systemctl status pcscd`
4. Restartujte pcscd: `sudo systemctl restart pcscd`

### RFID tag se nenačítá automaticky
1. Ujistěte se, že je čtečka v keyboard emulation módu
2. Zkontrolujte, zda je focus v RFID poli (kurzor bliká v poli)
3. Zkuste přiložit tag pomaleji ke čtečce
4. Zkuste jiný tag (může být vadný)

### API neodpovídá
1. Otevřete v admin panelu: **RFID Správa** → **Nastavení čtečky**
2. Klikněte na "Testovat připojení"
3. Zkontrolujte, zda běží web server: `docker ps | grep rehearsal`
4. Zkontrolujte logs: `docker logs rehearsal-app`

### RFID tag je duplicitní
- Systém automaticky kontroluje duplicity
- Každý tag může být přiřazen pouze k jednomu vybavení
- Při pokusu o přiřazení duplicitního tagu se zobrazí chyba

## 📊 Statistiky a logy

Všechna čtení RFID tagů se logují do tabulky `access_logs`:

```sql
SELECT * FROM access_logs 
WHERE action = 'rfid_scan' 
ORDER BY created_at DESC 
LIMIT 50;
```

## 🔐 Zabezpečení

- **Veřejné endpointy** (read, check-availability) - bez autentizace, throttle 60/min
- **Chráněné endpointy** (write, checkout, checkin) - vyžadují Sanctum token

Pro generování tokenu:
```bash
docker exec -it rehearsal-app php artisan tinker
$user = User::find(1);
$token = $user->createToken('rfid-device')->plainTextToken;
echo $token;
```

## 📚 Další dokumentace

- **Kompletní API dokumentace**: `docs/RFID_DOCUMENTATION.md`
- **Python skript**: `python_gateway/rfid_scanner.py`
- **Web rozhraní**: `public/rfid-manager.html`
