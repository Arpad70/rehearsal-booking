<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupQRCode extends Model
{
    use HasFactory;

    protected $table = 'backup_qr_codes';

    protected $fillable = [
        'reservation_id',
        'qr_code',
        'qr_data',
        'sequence_number',
        'status',
        'used_at',
        'used_by_reader',
    ];

    protected $casts = [
        'qr_data' => 'array',
        'used_at' => 'datetime',
    ];

    /**
     * Relationship: Backup QR belongs to Reservation
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Mark QR as used
     */
    public function markAsUsed(string $readerName = null): void
    {
        $this->update([
            'status' => 'used',
            'used_at' => now(),
            'used_by_reader' => $readerName,
        ]);
    }

    /**
     * Revoke QR code
     */
    public function revoke(): void
    {
        $this->update(['status' => 'revoked']);
    }

    /**
     * Check if QR is currently valid
     */
    public function isValid(): bool
    {
        return $this->status === 'active' && $this->reservation->isQRValid();
    }

    /**
     * Get QR data as decoded array
     */
    public function getDecodedData(): array
    {
        if (is_array($this->qr_data)) {
            return $this->qr_data;
        }
        
        return json_decode($this->qr_data, true) ?? [];
    }
}
