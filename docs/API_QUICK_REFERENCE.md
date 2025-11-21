# API Quick Reference

## Common Use Cases

### 1. Check if a room is available

```bash
curl -X GET "https://your-domain.com/api/v1/rooms/1/availability" \
  -d "start_at=2025-01-20T10:00:00Z&end_at=2025-01-20T12:00:00Z"
```

### 2. Create a reservation (authenticated user)

```bash
curl -X POST "https://your-domain.com/api/v1/reservations" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "room_id": 1,
    "start_at": "2025-01-20T10:00:00Z",
    "end_at": "2025-01-20T12:00:00Z",
    "notes": "Band rehearsal"
  }'
```

### 3. Get all your reservations

```bash
curl -X GET "https://your-domain.com/api/v1/reservations?status=upcoming" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 4. Validate an access token (at the door)

```bash
curl -X POST "https://your-domain.com/api/v1/access/validate" \
  -H "Content-Type: application/json" \
  -d '{"token": "a1b2c3d4e5f6..."}'
```

---

## Configuration

Key settings in `config/reservations.php`:

```php
'min_duration_minutes' => 15,           // Minimum booking duration
'min_advance_booking_hours' => 1,       // How far in advance bookings open
'token_length' => 64,                   // Access token length (hex)
'api_access_rate_limit' => '60,1',      // Format: 'limit,minutes'
```

---

## Access Log Cleanup

The system automatically archives (deletes) AccessLog records older than 1 year:
- **Schedule:** Daily at 02:00 UTC
- **Configuration:** Adjust in `ArchiveAccessLogsJob.php`
- **No action needed:** Fully automated

---

## Testing

Run the test suite:

```bash
./vendor/bin/phpunit tests/Feature/ReservationTest.php
./vendor/bin/phpunit tests/Feature/AccessValidationTest.php
```

Key test coverage:
- Overlap detection for concurrent bookings
- Token validation and expiration
- Authorization checks (policy-based)
- Rate limiting on access endpoint

---

## Troubleshooting

### "Rate limit exceeded" errors

**Issue:** Too many requests to an endpoint
**Solution:** 
- Wait 60 seconds and retry
- For access validation, check if IP whitelist is configured in `config/reservations.php`

### "Invalid token format" 

**Issue:** Token is not 64 hex characters
**Solution:** 
- Tokens are auto-generated when creating reservations
- Verify token from reservation response or by fetching reservation details

### Device toggle (Shelly) failures

**Issue:** Physical device doesn't turn on/off
**Check:**
1. Is `SHELLY_FAILURE_NOTIFY_EMAIL` configured? (You'll get email notifications)
2. Is the Shelly IP address correct? (`config('services.shelly.gateway_url')`)
3. Is the gateway/device reachable?
4. Check logs: `storage/logs/laravel.log`

**Recovery:** The job automatically retries 3 times with 60-second backoff

---

## Security Notes

1. **Access tokens** are 64-character hex strings, generated randomly per reservation
2. **Rate limiting** prevents brute-force attacks on the access validation endpoint
3. **Audit logging** records every access attempt (IP, timestamp, user agent)
4. **Authorization** uses policy-based checks - users can only access/modify their own reservations
5. **Database indexes** ensure efficient queries even with thousands of reservations

---

## Performance Tips

1. **Use availability endpoints** to check before creating reservations (prevents unnecessary validations)
2. **Batch access log cleanup** happens automatically - no manual intervention needed
3. **Pagination** on reservation listing - use `limit` parameter for large result sets
4. **Caching** should be implemented for frequently-accessed room data (future enhancement)

---

## Webhook Payload Examples

When integrated (future feature):

```json
{
  "event": "reservation.created",
  "data": {
    "id": 7,
    "room_id": 1,
    "user_id": 1,
    "start_at": "2025-01-20T10:00:00Z",
    "end_at": "2025-01-20T12:00:00Z",
    "token": "a1b2c3d4..."
  },
  "timestamp": "2025-01-19T15:30:00Z"
}
```

---

## Version Migration Guide

When updating from earlier versions, ensure:

1. Database migrations are run: `php artisan migrate`
2. Queue worker is running for background jobs: `php artisan queue:work`
3. Scheduler is active for cleanup jobs: `php artisan schedule:work`

No breaking changes in v1.0 - all previous endpoints remain compatible.
