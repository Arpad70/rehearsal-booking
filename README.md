<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Rehearsal Space Reservation System

A Laravel-based management system for scheduling and controlling access to rehearsal spaces. Features automatic device control (Shelly smart devices), real-time availability checking, comprehensive audit logging, and a REST API.

**Key Features:**
- 📅 Room booking with conflict detection
- 🔐 Token-based access control
- 💡 Smart device integration (Shelly relay control)
- 📊 Comprehensive audit logging
- 📱 REST API with rate limiting
- 🖥️ Filament admin panel
- 🔄 Automated cleanup jobs

---

## 🎫 QR Reader System (NEW!)

Complete QR code access control system with automatic email delivery, multi-protocol door support, and comprehensive monitoring.

**Features:**
- 🎫 Automatic QR generation for reservations
- 📧 Email delivery with QR codes
- 👥 Staff service access codes (cleaning, maintenance, admin)
- 🔓 Multi-protocol door control (Relay, API, Webhook)
- 📊 Real-time dashboard with metrics
- 🚨 Monitoring and alerts system
- 📈 Detailed access reporting
- 🔄 Backup QR codes for redundancy

**Quick Setup:**
```bash
php artisan migrate
php artisan queue:work --queue=emails
```

Then visit `/admin` → QR Reader to add your first reader.

**Documentation:**
- 📘 [Quick Reference](./QUICK_REFERENCE.md) - 5-minute overview
- 📗 [Complete Guide](./COMPLETE_DOCUMENTATION.md) - Full documentation
- 📙 [Implementation Guide](./QR_IMPLEMENTATION_GUIDE.md) - Setup & API
- 📕 **[Admin Backend Guide](./ADMIN_BACKEND_GUIDE.md)** - Detailed admin UI walkthrough ⭐ NEW!
- 📕 [Phase Summary](./PHASE_SUMMARY.md) - Technical details

---

## Quick Start

### Prerequisites
- PHP 8.3+
- Laravel 12.35
- MySQL 8.0+ (or SQLite)
- Composer
- Node.js (for frontend)

### Installation

```bash
# Clone repository
git clone https://github.com/Arpad70/rehearsal-booking.git
cd rehearsal-booking

# Install dependencies
composer install
npm install

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Run migrations (includes QR reader system)
php artisan migrate

# Create storage symlink
php artisan storage:link

# Start development server
php artisan serve

# In another terminal, start queue worker (for emails & device control)
php artisan queue:work

# In another terminal, start scheduler (for cleanup jobs)
php artisan schedule:work
```

**Access the application:**
- App: `http://localhost:8000`
- Admin: `http://localhost:8000/admin`

### Configuration

Edit `.env`:

```env
APP_NAME="Rehearsal App"
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=rehearsal_booking
DB_USERNAME=root
DB_PASSWORD=

# Email (for QR delivery)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=app-password

# Shelly smart device (optional)
SERVICES_SHELLY_GATEWAY_URL=http://192.168.1.50:5000/toggle
SHELLY_FAILURE_NOTIFY_EMAIL=admin@example.com

# IP whitelist (optional)
RESERVATIONS_IP_WHITELIST_ENABLED=false
RESERVATIONS_IP_WHITELIST=192.168.1.0/24
```

---

## API Documentation

### Public API Reference

- **Full Documentation:** [`API_DOCUMENTATION.md`](./API_DOCUMENTATION.md)
- **Quick Reference:** [`API_QUICK_REFERENCE.md`](./API_QUICK_REFERENCE.md)

**Base URL:** `/api/v1`

**Key Endpoints:**
- `GET /api/v1/rooms` - List all rooms
- `GET /api/v1/rooms/{id}/availability` - Check room availability
- `POST /api/v1/access/validate` - Validate access token
- `POST /api/v1/reservations` - Create reservation (auth required)
- `GET /api/v1/reservations` - List user's reservations (auth required)

**Authentication:** Bearer token (Sanctum)

**Rate Limiting:** 60 requests/minute per IP

---

## Architecture

### Database Models

| Model | Purpose |
|-------|---------|
| `User` | System users (administrators, staff) |
| `Room` | Rehearsal spaces with metadata |
| `Device` | Smart devices (Shelly relays) |
| `Reservation` | Booking records with access tokens |
| `AccessLog` | Audit trail of access attempts |
| `AuditLog` | Historical changes to reservations |

### Key Components

**Controllers:**
- `RoomController` - Room listing & availability
- `ReservationController` - Booking CRUD operations
- `AccessController` - Access token validation

**Jobs:**
- `TurnOnShellyJob` / `TurnOffShellyJob` - Device control (async)
- `ArchiveAccessLogsJob` - Daily cleanup of old access logs

**Policies:**
- `ReservationPolicy` - Authorization rules for bookings

**Middleware:**
- `ThrottleAccessValidation` - Rate limiting for access endpoint

---

## Features in Detail

### 1. Conflict-Free Booking

Uses database-level locking to prevent race conditions:

```php
$existing = Reservation::lockForUpdate()
    ->whereOverlaps($start, $end)
    ->exists();
```

**Minimum booking duration:** 15 minutes (configurable)

### 2. Access Token System

Each reservation generates a unique 64-character hex token:

- Token is marked `used_at` only when validated at the door
- Cannot be re-used or pre-validated
- Automatically revokes if reservation is cancelled

### 3. Smart Device Control

When a reservation begins, automatically triggers device toggle:

```
Shelly Device (192.168.1.100) 
  → Turn on relay when reservation starts
  → Turn off relay when reservation ends
```

Supports both:
- Centralized gateway (if available)
- Direct device control (fallback)

**Error Handling:** 3 automatic retries with 60-second backoff, email notification on final failure

### 4. Audit Trail

Every action is logged:
- Who created/modified/cancelled each reservation
- IP address and user agent of each access attempt
- Old and new values for any changes
- Indexed for efficient historical queries

**Automatic cleanup:** Access logs older than 1 year are archived daily at 02:00 UTC

### 5. Admin Panel

Full CRUD interface powered by Filament 3.0:
- User management
- Reservation management
- Room configuration
- Device status monitoring

Access at: `/admin` (requires authentication)

---

## Testing

Comprehensive test suite included:

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/ReservationTest.php

# Run with coverage
php artisan test --coverage
```

**Test Coverage:**
- ✅ Overlap detection for concurrent bookings
- ✅ Token validation and unauthorized access
- ✅ Authorization policy enforcement
- ✅ Rate limiting on access endpoint
- ✅ Device control error handling

---

## Development

### Key Files & Directories

```
app/
  ├── Http/
  │   ├── Controllers/           # API endpoints
  │   ├── Requests/              # Request validation
  │   └── Middleware/            # Rate limiting, auth
  ├── Jobs/                       # Queue jobs (device control, archival)
  ├── Models/                     # Database models
  ├── Policies/                   # Authorization rules
  └── Console/
      └── Kernel.php             # Scheduled jobs

routes/
  ├── api.php                     # API routes
  └── api/v1.php                  # v1-specific routes

database/
  ├── migrations/                 # Schema changes
  └── factories/                  # Test data

config/
  └── reservations.php            # App-specific configuration

tests/
  ├── Feature/
  │   ├── ReservationTest.php     # Booking logic tests
  │   └── AccessValidationTest.php # Token validation tests
  └── Unit/
```

### Code Quality

- **PHPStan:** Static analysis for type safety
- **Tests:** Feature tests for critical paths
- **Migrations:** Proper schema versioning
- **Indexes:** Optimized database queries

---

## Deployment

### Production Checklist

- [ ] `.env` configured with production values
- [ ] Database backed up before migrations
- [ ] Queue worker running (supervisor/systemd)
- [ ] Scheduler active (cron or supervisor)
- [ ] HTTPS enabled
- [ ] Rate limits configured appropriately
- [ ] Error notification email configured
- [ ] Logs rotated (see `config/logging.php`)

### Docker (Optional)

A Dockerfile can be added to containerize the application. Currently using standard PHP FPM setup.

---

## Troubleshooting

### Queue jobs not processing?

```bash
# Check queue status
php artisan queue:work

# Or use supervisor for production
supervisor -c /etc/supervisor/conf.d/rehearsal.conf
```

### Device not toggling?

1. Check Shelly IP in database: `php artisan tinker`
2. Verify network connectivity to device
3. Check logs: `tail -f storage/logs/laravel.log`
4. Test manually: `curl http://192.168.1.100/relay/0/toggle`

### Rate limiting too strict?

Edit `config/reservations.php`:

```php
'api_access_rate_limit' => '120,1',  // 120 requests per 1 minute
```

---

## Improvements Implemented

This codebase includes comprehensive improvements across security, stability, and maintainability:

**P0 - Critical Security:**
- ✅ Race condition fix (database-level locking)
- ✅ API brute-force protection (rate limiting)
- ✅ Input validation enforcement

**P1 - Stability:**
- ✅ Database indexes for performance
- ✅ Audit logging for compliance
- ✅ Robust error handling in device jobs

**P2 - Quality & Features:**
- ✅ Authorization policy refactoring
- ✅ Comprehensive audit trail
- ✅ API versioning for backward compatibility

**P3 - Testing & Admin:**
- ✅ Feature test coverage
- ✅ Device relationship optimization
- ✅ Filament admin panel

**P4 - Optimization:**
- ✅ Automated access log archival
- ✅ Job logic refactoring
- ✅ Comprehensive API documentation

For details, see [`IMPROVEMENTS.md`](./IMPROVEMENTS.md) (if present).

---

## License

The Rehearsal App is open-source software licensed under the MIT license.

## Support

For questions or issues, please contact the development team.


## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Developer setup — running tests

Unit and feature tests use an SQLite driver in the CLI. Make sure the PHP SQLite extension is installed for the PHP version you use in CLI.

On Debian/Ubuntu (replace `8.2` with your PHP version if different):

```bash
# install sqlite extension for PHP CLI
sudo apt update
sudo apt install -y php8.2-sqlite3

# then install composer deps and run tests
composer install
php ./vendor/bin/phpunit
```

If you use a different OS or setup (Docker, Homebrew, etc.), install the `pdo_sqlite`/`sqlite3` extension for your PHP CLI accordingly.

## Shelly gateway / device toggle setup

This project can toggle Shelly devices either via a central gateway or by calling the device directly.

- To use a central gateway (recommended), set the environment variable `SHELLY_GATEWAY_URL` in your `.env`:

```env
SHELLY_GATEWAY_URL=https://shelly-gateway.example/api
# Optional: email to notify if a toggle job fails after retries
SHELLY_FAILURE_NOTIFY_EMAIL=devops@example.com
```

- If no gateway is configured, the application will try a few common Shelly HTTP endpoints directly on the device IP (best-effort). For reliable operation use a gateway or adapt `app/Jobs/ToggleShellyJob.php` to your device model's API.

Job behavior:
- The toggle job will attempt up to 3 tries (with backoff). If all attempts fail, it will log an error and optionally notify `SHELLY_FAILURE_NOTIFY_EMAIL`.

If you run the worker locally for testing, use:

```bash
php artisan queue:work --tries=3
```
