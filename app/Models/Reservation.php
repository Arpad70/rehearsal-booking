<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Database\Factories\ReservationFactory;

/**
 * @property int $id
 * @property int $user_id
 * @property int $room_id
 * @property \Illuminate\Support\Carbon $start_at
 * @property \Illuminate\Support\Carbon $end_at
 * @property string|null $status
 * @property string $access_token
 * @property \Illuminate\Support\Carbon|null $token_valid_from
 * @property \Illuminate\Support\Carbon|null $token_expires_at
 * @property \Illuminate\Support\Carbon|null $used_at
 * @property \App\Models\Room $room
 * @property \App\Models\User $user
 */
class Reservation extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','room_id','start_at','end_at','status','access_token','token_valid_from','token_expires_at','used_at'];  
    /**
     * @var array<string,string>
     */
    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'token_valid_from' => 'datetime',
        'token_expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    protected static function booted() {  
        static::creating(function ($res) {  
            if (empty($res->access_token)) {  
                $res->access_token = bin2hex(random_bytes(32));  
            }  
        });

        // Log creation
        static::created(function ($res) {
            AuditLog::logAction(
                'created',
                self::class,
                $res->id,
                auth()->id(),
                null,
                $res->getAttributes()
            );
        });

        // Log updates
        static::updated(function ($res) {
            $oldValues = $res->getOriginal();
            $newValues = $res->getAttributes();
            
            // Only log if something actually changed
            $changes = array_diff_assoc($newValues, $oldValues);
            if (!empty($changes)) {
                AuditLog::logAction(
                    'updated',
                    self::class,
                    $res->id,
                    auth()->id(),
                    $oldValues,
                    $newValues
                );
            }
        });

        // Log deletion
        static::deleted(function ($res) {
            AuditLog::logAction(
                'deleted',
                self::class,
                $res->id,
                auth()->id(),
                $res->getAttributes(),
                null
            );
        });
    }  

    public function room(): BelongsTo {  
        return $this->belongsTo(Room::class);  
    }  

    public function user(): BelongsTo {  
        return $this->belongsTo(\App\Models\User::class);  
    }

    public function isTokenValid(string $token): bool {  
        if ($this->access_token !== $token) return false;  
        if (!$this->token_valid_from || !$this->token_expires_at) return false;  
        return now()->between($this->token_valid_from, $this->token_expires_at);  
    }  
}
