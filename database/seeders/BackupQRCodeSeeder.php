<?php

namespace Database\Seeders;

use App\Models\BackupQRCode;
use App\Models\Reservation;
use Illuminate\Database\Seeder;

class BackupQRCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reservations = Reservation::all();
        
        if ($reservations->isEmpty()) {
            return;
        }

        $backupQRCodes = [
            [
                'reservation_id' => $reservations->first()->id,
                'qr_code' => 'storage/app/qr_codes/backup_' . bin2hex(random_bytes(8)) . '.png',
                'qr_data' => json_encode([
                    'reservation_id' => $reservations->first()->id,
                    'type' => 'primary',
                    'generated_at' => now()->toIso8601String(),
                ]),
                'sequence_number' => 1,
                'status' => 'active',
                'used_at' => null,
                'used_by_reader' => null,
            ],
            [
                'reservation_id' => $reservations->count() > 1 ? $reservations->get(1)->id : $reservations->first()->id,
                'qr_code' => 'storage/app/qr_codes/backup_' . bin2hex(random_bytes(8)) . '.png',
                'qr_data' => json_encode([
                    'reservation_id' => $reservations->count() > 1 ? $reservations->get(1)->id : $reservations->first()->id,
                    'type' => 'backup',
                    'generated_at' => now()->toIso8601String(),
                ]),
                'sequence_number' => 2,
                'status' => 'used',
                'used_at' => now()->subHours(2),
                'used_by_reader' => 'reader_room_1',
            ],
            [
                'reservation_id' => $reservations->last()->id,
                'qr_code' => 'storage/app/qr_codes/backup_' . bin2hex(random_bytes(8)) . '.png',
                'qr_data' => json_encode([
                    'reservation_id' => $reservations->last()->id,
                    'type' => 'primary',
                    'generated_at' => now()->toIso8601String(),
                ]),
                'sequence_number' => 1,
                'status' => 'active',
                'used_at' => null,
                'used_by_reader' => null,
            ],
        ];

        foreach ($backupQRCodes as $code) {
            BackupQRCode::create($code);
        }
    }
}
