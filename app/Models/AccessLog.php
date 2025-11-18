<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'validation_result',
        'ip_address',
        'user_agent',
        'access_code',
        'access_type',
        'reader_type',
        'global_reader_id',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
    ];

    /**
     * Relationship: Log belongs to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    /**
     * Relationship: Log belongs to GlobalReader
     */
    public function globalReader(): BelongsTo
    {
        return $this->belongsTo(GlobalReader::class)->withDefault();
    }

    /**
     * Check if validation was successful
     */
    public function wasSuccessful(): bool
    {
        return $this->validation_result === 'success';
    }

    /**
     * Get reason for validation failure
     */
    public function getFailureReason(): ?string
    {
        if ($this->wasSuccessful()) {
            return null;
        }

        return $this->access_code ?? 'unknown';
    }

    /**
     * Log an access attempt
     */
    public static function logAttempt(
        ?int $userId,
        string $result,
        string $ipAddress,
        string $userAgent,
        string $accessCode = null,
        string $accessType = 'reservation',
        string $readerType = 'room',
        ?int $globalReaderId = null
    ): self {
        return static::create([
            'user_id' => $userId,
            'validation_result' => $result,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'access_code' => $accessCode,
            'access_type' => $accessType,
            'reader_type' => $readerType,
            'global_reader_id' => $globalReaderId,
        ]);
    }
}