<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Database\Factories\ReservationFactory;
use Illuminate\Support\Facades\Auth;

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
 * @property string|null $qr_code
 * @property \Illuminate\Support\Carbon|null $qr_generated_at
 * @property \Illuminate\Support\Carbon|null $qr_sent_at
 * @property \App\Models\Room $room
 * @property \App\Models\User $user
 */
class Reservation extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','room_id','start_at','end_at','status','access_token','token_valid_from','token_expires_at','used_at','qr_code','qr_generated_at','qr_sent_at','price','guest_name','guest_email','guest_phone'];  
    
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'token_valid_from' => 'datetime',
            'token_expires_at' => 'datetime',
            'used_at' => 'datetime',
            'qr_generated_at' => 'datetime',
            'qr_sent_at' => 'datetime',
            'price' => 'decimal:2',
        ];
    }

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
                Auth::id(),
                null,
                $res->getAttributes()
            );
        });

        // Log updates
        static::updated(function ($res) {
            $oldValues = $res->getOriginal();
            $newValues = $res->getAttributes();
            
            // Only log if something actually changed. Use JSON compare to handle nested arrays safely.
            $changes = [];
            foreach ($newValues as $k => $v) {
                $old = $oldValues[$k] ?? null;
                if (json_encode($v) !== json_encode($old)) {
                    $changes[$k] = $v;
                }
            }
            if (!empty($changes)) {
                AuditLog::logAction(
                    'updated',
                    self::class,
                    $res->id,
                    Auth::id(),
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
                Auth::id(),
                $res->getAttributes(),
                null
            );
        });
    }  

    public function room(): BelongsTo {  
        return $this->belongsTo(Room::class);  
    }  

    public function payments(): HasMany
    {
        return $this->hasMany(\App\Models\Payment::class);
    }

    public function user(): BelongsTo {  
        return $this->belongsTo(\App\Models\User::class);  
    }

    /**
     * Relationship: Reservation has many backup QR codes
     */
    public function backupQRCodes(): HasMany
    {
        return $this->hasMany(BackupQRCode::class);
    }

    /**
     * Generate backup QR codes (for redundancy)
     */
    public function generateBackupQRCodes(int $count = 2): void
    {
        // Remove old backup codes
        $this->backupQRCodes()->where('status', '!=', 'used')->delete();

        // Generate new ones
        for ($i = 2; $i <= ($count + 1); $i++) {
            $qrData = json_encode([
                'rid' => $this->id,
                'token' => substr($this->access_token, 0, 32),
                'room' => $this->room_id,
                'start' => $this->start_at->timestamp,
                'end' => $this->end_at->timestamp,
                'type' => 'reservation',
                'backup_seq' => $i,
            ]);

            // Genero QR
            app(\App\Services\QRCodeService::class)->generateQRImageFromData(
                $qrData,
                "qr_backup_{$this->id}_{$i}"
            );

            BackupQRCode::create([
                'reservation_id' => $this->id,
                'qr_code' => "/media/qrcodes/qr_backup_{$this->id}_{$i}.png",
                'qr_data' => $qrData,
                'sequence_number' => $i,
                'status' => 'active',
            ]);
        }
    }


    public function isTokenValid(string $token): bool {  
        if ($this->access_token !== $token) return false;  
        if (!$this->token_valid_from || !$this->token_expires_at) return false;  
        return now()->between($this->token_valid_from, $this->token_expires_at);  
    }

    /**
     * Check if QR code is valid and within access window
     * 15 minutes before start, up to end time
     */
    public function isQRValid(): bool
    {
        if (!$this->qr_code || !$this->qr_generated_at) {
            return false;
        }

        $now = now();
        $start = $this->start_at;
        $end = $this->end_at;

        // 15 minutes before to end of reservation
        $accessStart = $start->copy()->subMinutes(15);
        $accessEnd = $end;

        return $now->isBetween($accessStart, $accessEnd);
    }

    /**
     * Get QR access window information
     */
    public function getQRAccessWindow(): array
    {
        if (!$this->start_at || !$this->end_at) {
            return [];
        }

        return [
            'earliest_access' => $this->start_at->copy()->subMinutes(15),
            'latest_access' => $this->end_at,
            'window_minutes' => $this->start_at->diffInMinutes($this->end_at) + 15,
        ];
    }
}
