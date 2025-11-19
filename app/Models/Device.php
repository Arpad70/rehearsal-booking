<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $room_id
 * @property string $type
 * @property string $ip
 * @property array<string,mixed> $meta
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \App\Models\Room|null $room
 */
class Device extends Model
{
    protected $fillable = ['room_id', 'type', 'ip', 'meta'];
    
    /**
     * @return array<string,string>
     */
    protected function casts(): array
    {
        return [
            'meta' => 'array'
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Virtual name attribute used by admin UI.
     * Prefer `meta.name`, fallback to `ip` or id.
     */
    public function getNameAttribute(): string
    {
        $meta = $this->meta ?? [];
        if (is_array($meta) && isset($meta['name']) && $meta['name']) {
            return (string) $meta['name'];
        }

        if (!empty($this->ip)) {
            return $this->ip;
        }

        return 'Device ' . $this->id;
    }
}
