#!/bin/bash

echo "🚀 Nastavuji Docker prostředí pro RockSpace Rehearsal App..."

# Zkopíruj .env soubor
if [ ! -f .env ]; then
    echo "📝 Kopíruji .env.docker do .env..."
    cp .env.docker .env
fi

# Instaluj závislosti na hostu (využije /mnt/data místo systémového disku)
echo "📦 Instaluji PHP závislosti..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "📦 Instaluji Node.js závislosti..."
npm ci

echo "🎨 Builduji frontend assets..."
npm run build

# Vygeneruj APP_KEY pokud neexistuje
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Generuji APP_KEY..."
    php artisan key:generate
fi

# Spusť kontejnery (pouze PHP-FPM, Nginx, MySQL, Redis)
echo "🐳 Spouštím Docker kontejnery..."
sudo docker-compose up -d --build

# Počkej na MySQL
echo "⏳ Čekám na MySQL..."
sleep 15

# Spusť migrace
echo "📊 Spouštím databázové migrace..."
sudo docker-compose exec -T app php artisan migrate --force

# Spusť seedery
echo "🌱 Naplňuji databázi..."
sudo docker-compose exec -T app php artisan db:seed --class=RoomLandingSeeder
sudo docker-compose exec -T app php artisan db:seed --class=PromotionSeeder

# Optimalizuj aplikaci
echo "⚡ Optimalizuji aplikaci..."
sudo docker-compose exec -T app php artisan config:cache
sudo docker-compose exec -T app php artisan route:cache
sudo docker-compose exec -T app php artisan view:cache

echo ""
echo "✅ Docker prostředí je připraveno!"
echo ""
echo "📍 Aplikace běží na: http://localhost:8090"
echo "📍 Admin panel: http://localhost:8090/admin"
echo ""
echo "🔐 Výchozí přihlašovací údaje:"
echo "   Email: admin@example.com"
echo "   Heslo: password"
echo ""
echo "💾 Všechna data jsou uložena na /mnt/data:"
echo "   - Projekt: /mnt/data/www/rehearsal-app"
echo "   - MySQL: /mnt/data/docker-volumes/mysql"
echo "   - Redis: /mnt/data/docker-volumes/redis"
echo "   - Cache: /mnt/data/docker-cache"
echo ""
echo "📦 Užitečné příkazy:"
echo "   sudo docker-compose logs -f       - Zobrazit logy"
echo "   sudo docker-compose down          - Zastavit kontejnery"
echo "   sudo docker-compose restart       - Restartovat kontejnery"
echo ""
