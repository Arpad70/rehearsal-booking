# Testing Guide - Device Integration

## Přehled testů

V projektu jsou připraveny **2 testovací soubory** s celkem **15 testy** pro device integration:

### Unit Tests (`tests/Unit/Services/DeviceHealthServiceTest.php`)
- ✅ 7 testů pro `DeviceHealthService`
- Testují: online/offline status, health check history, availability statistics

### Feature Tests (`tests/Feature/Commands/DeviceHealthCheckCommandTest.php`)
- ✅ 8 testů pro `devices:health-check` Artisan command
- Testují: command execution, filtering, console output, error handling

## Problém s testovacím prostředím

**Status:** ⚠️ Testy jsou napsány, ale nefungují kvůli problému s databázovou konfigurací.

**Problém:** Laravel ignoruje `phpunit.xml` nastavení a používá produkční `.env` soubor pro testy. To způsobuje:
- RefreshDatabase trait spouští migrace vícekrát
- SQLite :memory: má problémy s duplicitními tabulkami
- Testy zamrzají na HTTP requestech (i přes Http::fake())

## Doporučení pro budoucnost

### Řešení 1: MySQL testovací databáze (doporučeno)
```bash
# Vytvořit testovací databázi
mysql -u root -p -e "CREATE DATABASE rehearsal_test;"
mysql -u root -p -e "GRANT ALL ON rehearsal_test.* TO 'rehearsal'@'%';"

# Upravit phpunit.xml
<env name="DB_CONNECTION" value="mysql"/>
<env name="DB_DATABASE" value="rehearsal_test"/>

# Spustit migrace
php artisan migrate --database=mysql --env=testing

# Spustit testy
php artisan test
```

### Řešení 2: Pest PHP (moderní testing framework)
```bash
composer require pestphp/pest --dev
composer require pestphp/pest-plugin-laravel --dev

# Pest má lepší podporu pro databázové testy
```

### Řešení 3: Mock všechny HTTP requesty
- Přepsat `DeviceHealthService` aby používal dependency injection
- Vytvořit mock `HttpClient` interface
- Testy budou rychlejší a spolehlivější

## Současné řešení

**Testy jsou připraveny, ale ne spustitelné.** Pro ověření funkčnosti používejte:

1. **Manuální testování:**
   ```bash
   php artisan devices:health-check
   php artisan devices:health-check --type=qr_reader
   ```

2. **Bash test skripty:**
   ```bash
   ./scripts/test-complete-integration.sh
   ```

3. **Filament UI:**
   - Navigovat na `/admin/devices`
   - Kliknout "Run Health Check" na jednotlivých zařízeních
   - Sledovat real-time status updates

## Test Coverage plán

Když budou testy funkční, pokrývají:

### DeviceHealthService (Unit)
- ✅ `performHealthCheck()` - vytvoření health check záznamu
- ✅ `isOnline()` - kontrola online statusu
- ✅ `isOnline()` - false pro offline zařízení  
- ✅ `isOnline()` - false pro staré záznamy
- ✅ `getLastHealthCheck()` - nejnovější záznam
- ✅ `getAvailabilityStats()` - statistiky dostupnosti
- ✅ `checkAllDevices()` - hromadná kontrola

### DeviceHealthCheckCommand (Feature)
- ✅ Spuštění bez parametrů
- ✅ Filtrování podle typu zařízení
- ✅ Kontrola specifického zařízení
- ✅ Zobrazení statistik
- ✅ Prázdný seznam zařízení
- ✅ Neplatný typ zařízení
- ✅ Neplatné ID zařízení
- ✅ Ukládání response time

## Závěr

**Implementace: 100%** ✅  
**Testovací kód: 100%** ✅  
**Funkční testy: 0%** ❌ (kvůli problému s DB konfigurací)

Pro produkční použití doporučuji nejprve vyřešit problém s testovacím prostředím jedním z výše uvedených řešení.
