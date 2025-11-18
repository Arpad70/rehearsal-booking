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
    'min_duration_minutes' => 15,

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
];
