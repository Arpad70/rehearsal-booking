# Apache Virtual Host Configuration

This directory contains the Apache Virtual Host configuration for the Rehearsal Booking Application.

## Setup Instructions

### 1. Copy Configuration File

```bash
sudo cp rehearsal-app.conf /etc/apache2/sites-available/
```

### 2. Enable the Virtual Host

```bash
sudo a2ensite rehearsal-app.conf
```

### 3. Enable Required Apache Modules

```bash
sudo a2enmod rewrite
sudo a2enmod proxy
sudo a2enmod proxy_fcgi
sudo a2enmod headers
sudo a2enmod expires
sudo a2enmod deflate
```

### 4. Test Configuration

```bash
sudo apache2ctl configtest
# Should output: Syntax OK
```

### 5. Restart Apache

```bash
sudo systemctl restart apache2
```

### 6. Add Host Entry (if not already present)

Edit `/etc/hosts` and add:

```
127.0.0.1   rehearsal-app.local www.rehearsal-app.local
```

### 7. Access Application

Open browser and navigate to:
- `http://rehearsal-app.local`
- `http://www.rehearsal-app.local`

## Configuration Features

✅ **PHP-FPM Integration** - Uses PHP 8.3 FPM socket for better performance
✅ **Laravel URL Rewriting** - Proper mod_rewrite rules for clean URLs
✅ **Security Headers** - X-Frame-Options, X-Content-Type-Options, etc.
✅ **Performance Optimization** - Gzip compression, expires headers, caching
✅ **File Protection** - Denies access to `.env`, `.lock`, `*.log` files
✅ **Logging** - Separate error and access logs for debugging

## Required Apache Modules

The configuration requires these modules:
- `mod_rewrite` - URL rewriting
- `mod_proxy` - Proxy support for PHP-FPM
- `mod_proxy_fcgi` - FastCGI proxy
- `mod_headers` - Custom HTTP headers
- `mod_expires` - Cache expiration headers
- `mod_deflate` - Gzip compression

## PHP-FPM Socket

The configuration uses `/run/php/php8.3-fpm.sock`. If your PHP-FPM socket is different, update:

```apache
<FilesMatch \.php$>
    SetHandler "proxy:unix:/run/php/php8.3-fpm.sock|fcgi://localhost/"
</FilesMatch>
```

To find your PHP-FPM socket:
```bash
sudo find /run -name "php*-fpm.sock" 2>/dev/null
```

## Troubleshooting

### Virtual Host not working

1. Check if site is enabled:
   ```bash
   sudo apache2ctl -S | grep rehearsal-app
   ```

2. Check Apache error log:
   ```bash
   sudo tail -f /var/log/apache2/error.log
   ```

3. Check application error log:
   ```bash
   sudo tail -f /var/log/apache2/rehearsal-app_error.log
   ```

### PHP not executing

1. Verify PHP-FPM is running:
   ```bash
   systemctl status php8.3-fpm
   ```

2. Check socket permissions:
   ```bash
   ls -la /run/php/php8.3-fpm.sock
   ```

3. Verify Apache has proxy modules:
   ```bash
   apache2ctl -M | grep proxy
   ```

### DNS resolution

If `rehearsal-app.local` doesn't resolve, ensure it's in `/etc/hosts`:
```bash
sudo grep rehearsal-app.local /etc/hosts
```

## Performance Tips

1. **Enable OpCache** - Edit `php.ini` and enable OpCache
2. **Use FastCGI Caching** - Can be configured in mod_cache
3. **Enable HTTP/2** - Use mod_http2 for better performance
4. **Database Optimization** - Ensure database indexes are created

## Security Notes

⚠️ This configuration:
- Denies direct access to `.env` file
- Prevents directory listing
- Sets X-Frame-Options to prevent clickjacking
- Enforces proper MIME types
- Enables HSTS headers (recommended to add)

For production, consider adding:
```apache
Header set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```

## Logs Location

- **Access Log**: `/var/log/apache2/rehearsal-app_access.log`
- **Error Log**: `/var/log/apache2/rehearsal-app_error.log`

Monitor logs with:
```bash
sudo tail -f /var/log/apache2/rehearsal-app_error.log
```

## Related Files

- Configuration: `rehearsal-app.conf`
- Application: `/mnt/data/www/rehearsal-app/`
- Public directory: `/mnt/data/www/rehearsal-app/public/`
- Environment file: `/mnt/data/www/rehearsal-app/.env`
