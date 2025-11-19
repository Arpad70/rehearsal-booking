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
    protected $fillable = ['name','location','capacity','shelly_ip','shelly_token','enabled','reservation_default_price'];

    protected $casts = [
        'enabled' => 'boolean',
        'reservation_default_price' => 'decimal:2',
    ];  

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
