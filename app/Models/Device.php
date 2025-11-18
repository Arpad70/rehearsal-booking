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
     * @var array<string,string>
     */
    protected $casts = [
        'meta' => 'array'
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
