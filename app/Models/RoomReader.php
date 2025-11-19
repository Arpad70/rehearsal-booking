<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomReader extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'reader_name',
        'reader_ip',
        'reader_port',
        'reader_token',
        'enabled',
        'door_lock_type',
        'door_lock_config',
    ];

    protected $casts = [
        'door_lock_config' => 'array',
        'enabled' => 'boolean',
    ];

    /**
     * Relationship: Reader belongs to a Room
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Check if reader is accessible and properly configured
     */
    public function isHealthy(): bool
    {
        return $this->enabled 
            && !empty($this->reader_ip) 
            && !empty($this->reader_token);
    }

    /**
     * Get door lock configuration for specific type
     */
    public function getLockConfig(): array
    {
        return $this->door_lock_config ?? [];
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
                'message' => "Reader online ({$duration}ms)",
                'response' => $result,
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Reader unreachable',
        ];
    }

    /**
     * Get access attempts in last 30 days
     */
    public function getAccessAttemptsLast30Days(): int
    {
        return AccessLog::where('reader_type', 'room')
            ->where('room_id', $this->room_id)
            ->where('access_logs.created_at', '>=', now()->subDays(30))
            ->count();
    }

    /**
     * Get success rate percentage (last 30 days)
     */
    public function getSuccessRate(): float
    {
        $total = AccessLog::where('reader_type', 'room')
            ->where('room_id', $this->room_id)
            ->where('access_logs.created_at', '>=', now()->subDays(30))
            ->count();

        if ($total === 0) {
            return 100.0;
        }

        $successful = AccessLog::where('reader_type', 'room')
            ->where('room_id', $this->room_id)
            ->where('access_granted', true)
            ->where('access_logs.created_at', '>=', now()->subDays(30))
            ->count();

        return ($successful / $total) * 100;
    }
}

