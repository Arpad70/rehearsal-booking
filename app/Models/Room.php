<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
    protected $fillable = ['name','location','capacity','shelly_ip','shelly_token'];  

    public function reservations(): HasMany {  
        return $this->hasMany(Reservation::class);  
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }  
}
