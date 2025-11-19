<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int|null $id
 * @property int|null $user_id
 * @property string|null $access_type
 * @property string|null $access_code
 * @property array|null $allowed_rooms
 * @property bool|null $unlimited_access
 * @property bool|null $enabled
 * @property bool|null $revoked
 * @property \Illuminate\Support\Carbon|null $valid_from
 * @property \Illuminate\Support\Carbon|null $valid_until
 */
class ServiceAccess extends Model
{
    use HasFactory;

    protected $table = 'service_access';

    protected $fillable = [
        'user_id',
        'access_type',
        'access_code',
        'description',
        'allowed_rooms',
        'unlimited_access',
        'valid_from',
        'valid_until',
        'enabled',
        'revoked',
        'revoke_reason',
    ];

    protected function casts(): array
    {
        return [
            'allowed_rooms' => 'array',
            'unlimited_access' => 'boolean',
            'enabled' => 'boolean',
            'revoked' => 'boolean',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
        ];
    }

    /**
     * Relationship: Access belongs to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if access is currently valid
     */
    public function isValid(): bool
    {
        if ($this->revoked || !$this->enabled) {
            return false;
        }
        
        if ($this->unlimited_access) {
            return true;
        }
        
        $now = now();
        
        if ($this->valid_from && $now->isBefore($this->valid_from)) {
            return false;
        }
        
        if ($this->valid_until && $now->isAfter($this->valid_until)) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if access is allowed for specific room
     */
    public function allowsRoom(int $roomId): bool
    {
        if (!$this->allowed_rooms) {
            return true; // Allow all if not restricted
        }
        
        return in_array('*', $this->allowed_rooms) || in_array($roomId, $this->allowed_rooms);
    }

    /**
     * Get remaining validity time
     */
    public function getRemainingTime(): ?int
    {
        if (!$this->valid_until) {
            return null;
        }
        
        $remaining = $this->valid_until->diffInSeconds(now());
        return $remaining > 0 ? $remaining : null;
    }

    /**
     * Increment usage counter
     */
    public function recordUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Revoke this access
     */
    public function revoke(string $reason = ''): void
    {
        $this->update([
            'revoked' => true,
            'revoke_reason' => $reason,
        ]);
    }

    /**
     * Get allowed rooms with their names
     */
    public function getAllowedRoomsWithNames(): array
    {
        if ($this->unlimited_access || !$this->allowed_rooms) {
            return [];
        }

        return Room::whereIn('id', $this->allowed_rooms)->get()->toArray();
    }

    /**
     * Generate and send access code email
     */
    public function sendAccessCodeEmail(): void
    {
        \App\Jobs\SendServiceAccessCodeEmail::dispatch($this);
    }
}
