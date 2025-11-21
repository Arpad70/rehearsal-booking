<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Reservation> $reservations
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Device> $devices
 * @property int $id
 * @property string $name
 * @property string|null $location
 * @property int $capacity
 * @property string|null $shelly_ip
 * @property string|null $shelly_token
 */
class Room extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'location',
        'address',
        'latitude',
        'longitude',
        'image',
        'capacity',
        'price_per_hour',
        'is_public',
        'description',
        'image_url',
        'size',
        'shelly_token',
        'enabled',
        'reservation_default_price',
        'power_monitoring_enabled',
        'power_monitoring_type',
        'auto_lights_enabled',
        'auto_outlets_enabled',
        'access_control_device',
        'access_mode',
        'camera_enabled',
        'mixer_enabled',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'is_public' => 'boolean',
            'reservation_default_price' => 'decimal:2',
            'price_per_hour' => 'decimal:2',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'power_monitoring_enabled' => 'boolean',
            'auto_lights_enabled' => 'boolean',
            'auto_outlets_enabled' => 'boolean',
            'camera_enabled' => 'boolean',
            'mixer_enabled' => 'boolean',
        ];
    }  

    public function reservations(): HasMany {  
        return $this->hasMany(Reservation::class);  
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function readers(): HasMany
    {
        return $this->hasMany(RoomReader::class);
    }

    public function equipment(): BelongsToMany
    {
        return $this->belongsToMany(Equipment::class, 'room_equipment')
            ->withPivot('quantity', 'installed', 'condition_notes', 'last_inspection', 'status')
            ->withTimestamps();
    }
}
