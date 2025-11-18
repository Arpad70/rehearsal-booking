<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GlobalReader extends Model
{
    use HasFactory;

    protected $fillable = [
        'reader_name',
        'access_type',
        'reader_ip',
        'reader_port',
        'reader_token',
        'enabled',
        'door_lock_type',
        'door_lock_config',
        'access_minutes_before',
        'access_minutes_after',
        'allowed_service_types',
    ];

    protected $casts = [
        'door_lock_config' => 'array',
        'allowed_service_types' => 'array',
        'enabled' => 'boolean',
    ];

    /**
     * Relationship: Reader has many access logs
     */
    public function accessLogs(): HasMany
    {
        return $this->hasMany(AccessLog::class);
    }

    /**
     * Check if reader is properly configured
     */
    public function isHealthy(): bool
    {
        return $this->enabled 
            && !empty($this->reader_ip) 
            && !empty($this->reader_token);
    }

    /**
     * Get door lock configuration
     */
    public function getLockConfig(): array
    {
        return $this->door_lock_config ?? [];
    }

    /**
     * Check if service type is allowed on this reader
     */
    public function allowsServiceType(string $serviceType): bool
    {
        if (!$this->allowed_service_types) {
            return true; // Allow all if not restricted
        }
        
        return in_array($serviceType, $this->allowed_service_types);
    }

    /**
     * Get access window boundaries (seconds from now)
     */
    public function getAccessBoundaries(): array
    {
        return [
            'before_seconds' => $this->access_minutes_before * 60,
            'after_seconds' => $this->access_minutes_after * 60,
        ];
    }

    /**
     * Test connection to reader device
     */
    public function testConnection(): array
    {
        $url = "http://{$this->reader_ip}:{$this->reader_port}/status";
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 3,
                'header' => "Authorization: Bearer {$this->reader_token}",
            ]
        ]);
        
        $start = microtime(true);
        $result = @file_get_contents($url, false, $context);
        $duration = round((microtime(true) - $start) * 1000);
        
        if ($result !== false) {
            return [
                'success' => true,
                'message' => "Global reader online ({$duration}ms)",
                'response' => $result,
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Global reader unreachable',
        ];
    }
}
