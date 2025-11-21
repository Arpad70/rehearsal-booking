# Docker Deployment

Aplikace je plně kontejnerizovaná pomocí Docker a Docker Compose.

## 🚀 Rychlý start

```bash
./docker-setup.sh
```

Tento skript automaticky:
- Zkopíruje `.env.docker` do `.env`
- Vygeneruje `APP_KEY`
- Spustí všechny kontejnery (App, MySQL, Redis, Queue Worker)
- Spustí migrace a seedery
- Optimalizuje aplikaci

## 📦 Kontejnery

- **app** - PHP 8.3-FPM + Nginx (port 8090)
- **mysql** - MySQL 8.0 (port 3306)
- **redis** - Redis 7 (port 6379)
- **queue** - Laravel Queue Worker

## 🔧 Manuální spuštění

```bash
# Zkopíruj .env
cp .env.docker .env

# Spusť kontejnery
docker-compose up -d

# Vygeneruj APP_KEY
docker-compose exec app php artisan key:generate

# Spusť migrace
docker-compose exec app php artisan migrate --force

# Naplň databázi
docker-compose exec app php artisan db:seed --class=RoomLandingSeeder
docker-compose exec app php artisan db:seed --class=PromotionSeeder
```

## 📍 URL

- **Aplikace**: http://localhost:8090
- **Admin panel**: http://localhost:8090/admin

### Přihlašovací údaje
- **Email**: admin@example.com
- **Heslo**: password

## 🛠️ Užitečné příkazy

```bash
# Zobrazit logy
docker-compose logs -f

# Spustit artisan příkaz
docker-compose exec app php artisan [command]

# Vstoupit do kontejneru
docker-compose exec app bash

# Restartovat kontejnery
docker-compose restart

# Zastavit kontejnery
docker-compose down

# Smazat vše včetně volumes
docker-compose down -v
```

## 🔄 Update aplikace

```bash
# Stáhni změny
git pull

# Rebuild kontejnery
docker-compose build

# Restartuj
docker-compose down
docker-compose up -d

# Spusť migrace
docker-compose exec app php artisan migrate --force

# Optimalizuj
docker-compose exec app php artisan optimize
```

## 📊 Monitorování

```bash
# Sleduj logy aplikace
docker-compose logs -f app

# Sleduj logy queue
docker-compose logs -f queue

# Sleduj MySQL
docker-compose logs -f mysql
```

## ⚠️ Produkční prostředí

Pro produkci upravte:

1. `.env.docker`:
   - Změňte `APP_DEBUG=false`
   - Nastavte silné heslo pro `DB_PASSWORD`
   - Nakonfigurujte MAIL_* proměnné

2. `docker-compose.yml`:
   - Změňte porty pokud je potřeba
   - Přidejte SSL certifikáty
   - Nastavte restart policy na `always`

3. Přidejte reverzní proxy (Nginx/Traefik) s SSL
