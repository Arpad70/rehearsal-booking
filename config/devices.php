<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Simulator Host
    |--------------------------------------------------------------------------
    |
    | Host where device simulators are running.
    | - Docker: use 'host.docker.internal' or bridge IP (172.17.0.1)
    | - Local: use 'localhost'
    |
    */
    'simulator_host' => env('SIMULATOR_HOST', '172.17.0.1'),
    
    /*
    |--------------------------------------------------------------------------
    | Device Ports
    |--------------------------------------------------------------------------
    |
    | Base ports for each device type.
    | Device #1 = base_port, #2 = base_port+1, etc.
    |
    | WARNING: Actual simulators use different ports than documentation!
    | - Shelly: Simulators use 9301-9306 (conflicts with mixer!)
    | - Recommended: Move Shelly to 9501-9506 or Mixer to 9800
    |
    */
    'ports' => [
        'qr_reader' => env('QR_READER_BASE_PORT', 9101),
        'camera' => env('CAMERA_BASE_PORT', 9201),
        'shelly' => env('SHELLY_BASE_PORT', 9301), // Actual simulator ports (conflicts with mixer!)
        'keypad' => env('KEYPAD_BASE_PORT', 9401),
        'mixer' => env('MIXER_BASE_PORT', 9800), // Moved to avoid conflict with Shelly
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Circuit Breaker
    |--------------------------------------------------------------------------
    |
    | Configuration for circuit breaker pattern to handle device failures.
    |
    */
    'circuit_breaker' => [
        'failure_threshold' => env('DEVICE_FAILURE_THRESHOLD', 3),
        'recovery_timeout' => env('DEVICE_RECOVERY_TIMEOUT', 60), // seconds
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Timeouts
    |--------------------------------------------------------------------------
    */
    'timeout' => env('DEVICE_TIMEOUT', 5), // seconds
    'health_check_interval' => env('DEVICE_HEALTH_CHECK_INTERVAL', 60), // seconds
    
    /*
    |--------------------------------------------------------------------------
    | WebSocket Configuration
    |--------------------------------------------------------------------------
    */
    'websocket' => [
        'enabled' => env('DEVICE_WEBSOCKET_ENABLED', true),
        'heartbeat_interval' => 10, // seconds
    ],
];
