<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Reservation Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for reservation system behavior
    |
    */

    // Minimum duration for a reservation in minutes
    'min_duration_minutes' => 60,

    // How many minutes before reservation start should token become valid
    'token_valid_before_minutes' => 5,

    // How many minutes after reservation end should token remain valid
    'token_valid_after_minutes' => 5,

    // How many minutes before start should the room turn on
    'turn_on_before_minutes' => 1,

    // How many minutes after end should the room turn off
    'turn_off_after_minutes' => 2,

    /*
    |--------------------------------------------------------------------------
    | Access Control Configuration
    |--------------------------------------------------------------------------
    |
    | Security settings for the access validation endpoint
    |
    */

    // Maximum attempts for access validation per IP per time window
    'api_access_rate_limit' => 60,  // 60 attempts

    // Time window for rate limiting in minutes
    'api_access_rate_window' => 1,  // per 1 minute

    // Enable IP whitelisting (requires environment variable ACCESS_ALLOWED_IPS)
    'ip_whitelist_enabled' => false,

    /*
    |--------------------------------------------------------------------------
    | QR Code Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for QR code generation and validation
    |
    */

    // QR reader rate limit (requests per minute)
    'qr_reader_rate_limit' => 100,  // 100 attempts per minute

    // Time window for QR rate limiting in minutes
    'qr_rate_window' => 1,

    // QR code access window before reservation start
    'qr_access_minutes_before' => 15,  // 15 minutes before start

    // QR code image cleanup after N days
    'qr_cleanup_days' => 30,

    // Whether to generate QR codes automatically when reservation is created
    'auto_generate_qr' => true,

    /*
    |--------------------------------------------------------------------------
    | Reader Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for room and global readers
    |
    */

    // Default relay pin for new room readers
    'default_relay_pin' => 1,

    // Default unlock duration in seconds
    'default_unlock_duration' => 5,

    // Default unlock duration for global readers in seconds
    'default_global_unlock_duration' => 10,

    // Global reader access window extensions
    'global_reader_minutes_before' => 30,  // 30 min before start
    'global_reader_minutes_after' => 30,   // 30 min after end
];

