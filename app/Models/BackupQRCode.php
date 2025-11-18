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

    /**
     * Generate missing backup QR codes
     */
    public static function generateMissingBackups(): int
    {
        $qrService = app(\App\Services\QRCodeService::class);
        $count = 0;

        $reservations = Reservation::whereDoesntHave('backupQRCodes')
            ->where('qr_code', '!=', null)
            ->get();

        foreach ($reservations as $reservation) {
            $qrCode = $qrService->generateForReservation($reservation);
            if ($qrCode['success']) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Export all QR codes as ZIP file
     */
    public static function exportAsZip(): string
    {
        $zip = new \ZipArchive();
        $zipPath = storage_path("app/backup_qr_codes_" . date('Y-m-d_H-i-s') . '.zip');

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $backups = self::with('reservation')->get();

            foreach ($backups as $backup) {
                $filename = "backup_qr_{$backup->id}_{$backup->sequence_number}.png";
                $zip->addFromString($filename, $backup->qr_code);
            }

            $zip->close();
        }

        return "backup_qr_codes_" . date('Y-m-d_H-i-s') . '.zip';
    }
}
