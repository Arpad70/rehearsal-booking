# Railway.app Deployment Guide

## Quick Start

1. Vytvořte účet na [Railway.app](https://railway.app)
2. Propojte váš GitHub repozitář
3. Vytvořte nový projekt a přidejte služby:
   - Web Service (hlavní aplikace)
   - MySQL Database
   - Redis

## Konfigurace služeb

### Web Service

Railway automaticky detekuje `Dockerfile` a použije ho pro build.

### Environment Variables

Nastavte následující proměnné prostředí v Railway dashboardu:

```env
APP_NAME="Rehearsal App"
APP_ENV=production
APP_KEY=base64:... # Vygenerujte pomocí: php artisan key:generate --show
APP_DEBUG=false
APP_URL=https://your-app.up.railway.app

DB_CONNECTION=mysql
DB_HOST=${{MySQL.PRIVATE_URL}}
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=${{MySQL.MYSQL_ROOT_PASSWORD}}

REDIS_HOST=${{Redis.PRIVATE_URL}}
REDIS_PASSWORD=
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@rehearsal.app
MAIL_FROM_NAME="${APP_NAME}"

STRIPE_KEY=your_stripe_key
STRIPE_SECRET=your_stripe_secret
STRIPE_WEBHOOK_SECRET=your_webhook_secret

GOPAY_GOID=your_gopay_id
GOPAY_CLIENT_ID=your_client_id
GOPAY_CLIENT_SECRET=your_client_secret
```

### Generování APP_KEY

```bash
php artisan key:generate --show
```

## Deployment

### Automatický (pomocí GitHub Actions)

1. V GitHub repository přidejte secrets:
   - `RAILWAY_TOKEN` - Personal Access Token z Railway
   - `RAILWAY_SERVICE_ID` - ID vaší služby

2. Push do `main` nebo `master` branche automaticky spustí deployment

### Manuální

```bash
railway login
railway link
railway up
```

## Domain Setup

1. V Railway dashboardu otevřete vaši službu
2. Jděte do Settings → Domains
3. Vygenerujte Railway domain nebo přidejte vlastní

## Monitoring a Logs

Railway poskytuje:
- Real-time logs
- Metriky využití (CPU, RAM, Network)
- Build history
- Deployment history

## Pokročilé nastavení

### Queue Worker

Pro správné fungování queue jobů vytvořte samostatnou službu:

1. Duplikujte web service
2. Změňte start command na: `php artisan queue:work --tries=3`

### Scheduled Tasks

Railway nepodporuje cron přímo. Použijte externí službu jako:
- [cron-job.org](https://cron-job.org)
- [EasyCron](https://www.easycron.com)

Vytvořte endpoint `/cron` a volejte ho každou minutu.

### Automatické migrace

Pro automatické spuštění migrací při deployu upravte `startCommand`:

```bash
php artisan migrate --force && supervisord -c /etc/supervisor/supervisord.conf
```

## Cena

- **Free tier**: $5 kredit měsíčně (vyžaduje kartu)
- **Developer**: $5/měsíc (500 hodin runtime)
- **Team**: $20/měsíc (team features)

Odhadovaná cena pro produkci: ~$15-20/měsíc

## Troubleshooting

### Build fails
- Zkontrolujte Dockerfile syntax
- Ověřte, že všechny závislosti jsou v composer.json

### Database connection error
- Použijte `${{MySQL.PRIVATE_URL}}` místo public URL
- Ověřte DB credentials

### 500 Error
- Zkontrolujte logs v Railway dashboardu
- Ověřte APP_KEY v environment variables
- Spusťte `php artisan config:clear`

### Queue jobs nefungují
- Vytvořte samostatnou queue worker service
- Ověřte Redis connection
