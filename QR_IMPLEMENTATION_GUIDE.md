# QR Reader System - Implementation Guide

## Přehled

Tato příručka popisuje, jak nakonfigurovat a používat implementovaný QR reader systém pro fyzický přístup do místností.

## Obsah
1. [Konfigurace Hardware](#konfigurace-hardware)
2. [Nastavení v Filamentu](#nastavení-v-filamentu)
3. [API Endpoints](#api-endpoints)
4. [Testy a Troubleshooting](#testy-a-troubleshooting)

---

## Konfigurace Hardware

### Podporované zařízení

#### 1. Relay (GPIO/Arduino/Shelly)
- **Příklady:** Arduino s GPIO pinem, Shelly Relay, vlastní elektronika
- **Protokol:** HTTP GET request
- **Parametry:** IP adresa, port, pin číslo, doba trvání

```
GET http://192.168.1.100:8080/relay/1/on?duration=5
Authorization: Bearer <reader_token>
```

#### 2. Smart Lock API
- **Příklady:** Yale, August, Nuki Smart Lock
- **Protokol:** HTTP POST s JSON
- **Parametry:** API URL, API key, lock ID

```
POST https://api.smartlock.com/unlock
Content-Type: application/json
Authorization: Bearer <api_key>

{
  "action": "unlock",
  "lock_id": "room_123",
  "duration": 5
}
```

#### 3. Webhook
- **Příklady:** Home Assistant, IFTTT, vlastní integraci
- **Protokol:** HTTP POST s HMAC-SHA256 podpisem
- **Parametry:** Webhook URL, webhook secret

```
POST https://your-webhook.com/unlock
Content-Type: application/json
X-Signature: sha256=<hmac_signature>

{
  "room_id": 1,
  "reader_id": 5,
  "action": "unlock",
  "timestamp": 1700000000
}
```

---

## Nastavení v Filamentu

### 1. Vytvoření místnosti

Nejdříve musí existovat místnost (Room). Pokud ještě neexistuje:

1. Jděte na **Správa místností → Místnosti**
2. Klikněte **Přidat novou místnost**
3. Vyplňte název, kapacitu, atd.
4. Uložte

### 2. Přidání QR čtečky do místnosti

#### Návod: Shelly Relay (nejčastěji používaný)

1. Jděte na **Správa zařízení → Čtečky místností**
2. Klikněte **Přidat novou čtečku místnosti**
3. Vyplňte:

| Pole | Příklad | Poznámka |
|------|---------|----------|
| **Room** | Místnost 1 | Vyberte z dropdown |
| **Reader Name** | QR Reader - Místnost 1 | Popis pro adminy |
| **Reader IP** | 192.168.1.100 | IP adresa Shelly zařízení |
| **Reader Port** | 8080 | Standardní port pro Shelly |
| **Reader Token** | abc123... | Heslo do Shelly (můžete vymyslet) |
| **Lock Type** | Relay | Vyberte "Relay" |
| **Enabled** | ✓ | Zapnuto |

4. Klikněte na **Add Configuration** (Door Lock Configuration):
   - **Key:** `relay_pin`
   - **Value:** `1` (pin číslo na desce)
   - Klikněte znovu pro další parametr:
   - **Key:** `duration`
   - **Value:** `5` (sekund)

5. Uložte (Save)

6. Tlačítko **Test Connection**
   - Mělo by vrátit: "✅ Reader online (123ms)"

#### Návod: Smart Lock API (např. Yale)

1. Postup stejný, ale:
   - **Lock Type:** vyberte "API"
   - **Configuration:**
     - `api_url`: https://api.smartlock.com/unlock
     - `api_key`: vaše API klíč z Yale
     - `lock_id`: room_123
     - `duration`: 5

#### Návod: Webhook (Home Assistant, IFTTT)

1. Postup stejný, ale:
   - **Lock Type:** vyberte "Webhook"
   - **Configuration:**
     - `webhook_url`: https://homeassistant.local:8123/webhook/unlock
     - `webhook_secret`: vaše tajné heslo (dlouhé!)

### 3. Přidání globální čtečky (Hlavní vchod)

1. Jděte na **Správa zařízení → Globální čtečky**
2. Klikněte **Přidat novou globální čtečku**
3. Vyplňte:

| Pole | Příklad | Poznámka |
|------|---------|----------|
| **Reader Name** | Hlavní vchod | Unikátní název |
| **Access Type** | entrance | Vchod, servis, nebo admin |
| **Reader IP** | 192.168.1.50 | IP čtečky |
| **Reader Port** | 8080 | Port |
| **Reader Token** | abc123... | Heslo |
| **Minutes Before** | 30 | Přístup 30 min před rezervací |
| **Minutes After** | 30 | Přístup 30 min po konci |
| **Lock Type** | relay | Relay/API/Webhook |

4. Podobně přidejte Door Lock Configuration

5. Pro servisní přístup (Service Access), zaškrtněte:
   - Allowed Service Types: cleaning, maintenance, admin

### 4. Přidělení servisního přístupu (Úklid, údržba)

1. Jděte na **Přístupová práva → Servisní přístup**
2. Klikněte **Přidat nový přístup**
3. Vyplňte:

| Pole | Příklad | Poznámka |
|------|---------|----------|
| **Staff Member** | Jan Novák | Vyberte z dropdown |
| **Access Type** | Cleaning | Úklid/Údržba/Admin |
| **Description** | Týdenní úklid | Důvod přístupu |
| **Unlimited Room Access** | ✓ | Zaškrtněte (nebo vyberte konkrétní místnosti) |
| **Valid From** | 2025-01-01 | Kdy začína právo |
| **Valid Until** | 2025-12-31 | Kdy končí právo |
| **Enable Access** | ✓ | Aktivovat přístup |

4. Klikněte **Generate QR**
   - Zkopírujete kód a vytisknete si QR

---

## API Endpoints

### Validace QR kódu

```
POST /api/v1/qr/validate
Content-Type: application/json

{
  "qr_data": "{\"rid\":1,\"token\":\"abc...\",\"room\":1,\"start\":...,\"end\":...,\"type\":\"reservation\"}",
  "room_id": 1,
  "reader_token": "abc123"
}
```

**Odpověď (úspěch):**
```json
{
  "access": true,
  "code": "QR_ACCESS_GRANTED",
  "message": "Room unlocked (5s)",
  "door_unlocked": true,
  "reservation": {
    "id": 1,
    "user_name": "Pavel Horák",
    "room_name": "Místnost 1",
    "start_at": "2025-01-20 14:00",
    "end_at": "2025-01-20 16:00"
  }
}
```

**Odpověď (selhání - příliš brzy):**
```json
{
  "access": false,
  "code": "TOO_EARLY",
  "message": "Access too early. Available in 12 minutes"
}
```

### Health Check

```
GET /api/v1/qr/status
```

**Odpověď:**
```json
{
  "status": "online",
  "timestamp": "2025-01-20T15:30:00Z",
  "server_time": "2025-01-20 16:30:00"
}
```

### Heartbeat (monitorování)

```
GET /api/v1/qr/heartbeat
```

**Odpověď:**
```json
{
  "alive": true,
  "timestamp": 1705770600
}
```

### Test připojení čtečky

```
POST /api/v1/rooms/{roomId}/readers/{readerId}/test
Authorization: Bearer <token>
```

**Odpověď:**
```json
{
  "success": true,
  "message": "Reader online (85ms)"
}
```

---

## Testy a Troubleshooting

### 1. Testování připojení čtečky

1. V Filamentu na stránce čtečky klikněte **Test Connection**
2. Mělo by se objevit oznámení:
   - ✅ Reader online (XYZms) → OK
   - ❌ Reader unreachable → Zkontrolujte IP/port/token

### 2. Testování API

Pomocí curl:

```bash
# Test health check
curl http://localhost:8000/api/v1/qr/status

# Test validace QR (příklad - v praxi bude jiný QR data)
curl -X POST http://localhost:8000/api/v1/qr/validate \
  -H "Content-Type: application/json" \
  -d '{
    "qr_data": "{\"rid\":1,\"token\":\"abc123\",\"room\":1,\"start\":1705680000,\"end\":1705687200,\"type\":\"reservation\"}",
    "room_id": 1,
    "reader_token": "your_reader_token"
  }'
```

### 3. Běžné problémy

#### Problem: "Reader unreachable"
- ✓ Zkontrolujte IP adresu (je dostupná z serveru?)
- ✓ Zkontrolujte port (je Shelly na 8080?)
- ✓ Zkontrolujte firewall (jsou otevřené porty?)
- ✓ Zkontrolujte token (je správný?)

#### Problem: "QR validation failed - TOO_EARLY"
- ✓ Uživatel se skenuje příliš brzy (více než 15 min před rezervací)
- ✓ Zkontrolujte systémový čas na serveru (ntp -p)

#### Problem: "Door unlock failed"
- ✓ Zkontrolujte, zda je reader v Filamentu zapnutý (enabled = ✓)
- ✓ Zkontrolujte relay konfiguraci (správný pin, trvání)
- ✓ Zkontrolujte fyzické připojení relé

#### Problem: "Unauthorized reader"
- ✓ Token v API požadavku se musí shodovat s `reader_token` v Filamentu
- ✓ Zkontrolujte přesný obsah tokenu (bez mezer, správné znaky)

### 4. Logging

Všechny pokusy o validaci se logují do `access_logs`:

```sql
SELECT * FROM access_logs 
WHERE created_at > NOW() - INTERVAL '1 HOUR'
ORDER BY created_at DESC
LIMIT 20;
```

Sloupce:
- `user_id` - Který uživatel se pokusil
- `access_code` - Kod (QR_SUCCESS, TOO_EARLY, EXPIRED, atd.)
- `access_type` - reservation / service
- `reader_type` - room / global
- `ip_address` - IP čtečky
- `validation_result` - success / failed

---

## Příklady Use Cases

### Use Case 1: Běžná rezervace
1. Uživatel si zarezervuje místnost
2. Při vytvoření rezervace se automaticky vygeneruje QR kód
3. QR kód se pošle emailem
4. Uživatel příjde 5-15 minut před rezervací
5. Naskenuje QR kód do čtečky na dveřích
6. Relé se aktivuje na 5 sekund → dveře se otevřou
7. Systém zaloguje úspěšný přístup

### Use Case 2: Servisní přístup (čistící)
1. Admin vytvoří servisní přístup pro Janu (cleaning)
2. Vygeneruje QR kód a vytiskne si
3. Jana si naskenuje kód kdykoliv během svého pracovního času
4. Přístup se ověří z tabulky `service_access`
5. Globální dveře se otevřou (30 min rozšíření)

### Use Case 3: Údržba přenosového zařízení
1. Admin vytvoří globální reader s webhookem
2. Webhook ukazuje na Home Assistant
3. HA má přímo řízený relé na dveřích
4. Při validaci QR se pošle webhook do HA
5. HA aktivuje relé a zaloguje přístup

---

## Bezpečnost

### Implementované ochrany:
- ✅ **Rate limiting:** 100 pokusů/minutu na QR endpoint
- ✅ **HMAC-SHA256 podpis:** Pro webhook komunikaci
- ✅ **Audit trail:** Všechny pokusy o přístup se logují
- ✅ **Časový limit:** QR kód platný jen v určitém okně
- ✅ **Token unikátnost:** Každá rezervace má jiný token
- ✅ **IP tracking:** Která čtečka se pokusila (IP adresa)

### Doporučené postupy:
- 🔒 Změňte výchozí tokeny v Filamentu na silné hesla
- 🔒 Používejte HTTPS pro webhook integraci
- 🔒 Pravidelně kontrolujte access_logs pro anomálie
- 🔒 Odstraňujte staré servisní přístupy (Revoke)
- 🔒 Zálohujte databázi

---

## Pokročilá konfigurace

### Vlastní relay protocol

Pokud máte vlastní zařízení, můžete je integrovat přes webhook:

```bash
# Příklad Home Assistant konfiguraci
automation:
  - alias: "Room Door Unlock"
    trigger:
      webhook_id: my_secret_webhook
    action:
      - service: light.turn_on
        data:
          entity_id: light.door_relay
          brightness_pct: 100
      - delay: "00:00:05"
      - service: light.turn_off
        data:
          entity_id: light.door_relay
```

### Rate limiting customization

V `config/reservations.php`:

```php
'qr_reader_rate_limit' => 100,  // pokusů za minutu
'qr_rate_window' => 1,          // okno v minutách
```

---

## Kontakt a Support

Pro technické problémy:
- 📧 Email: tech-support@zkusebny.cz
- 📞 Telefonicky: +420 123 456 789
- 🔧 GitHub Issues: https://github.com/Arpad70/rehearsal-booking

---

## Verze

- **Datum:** 18. listopadu 2025
- **Verze:** 2.0 (Phase 1-3 dokončeno)
- **Autor:** GitHub Copilot + Architectural Review

