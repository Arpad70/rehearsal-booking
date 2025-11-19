<?php

namespace Database\Seeders;

use App\Models\AccessLog;
use App\Models\User;
use App\Models\Reservation;
use Illuminate\Database\Seeder;

class AccessLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $reservations = Reservation::all();

        if ($users->isEmpty() || $reservations->isEmpty()) {
            return;
        }

        $logs = [
            [
                'reservation_id' => $reservations->first()->id,
                'user_id' => $users->first()->id,
                'room_id' => $reservations->first()->room_id,
                'location' => 'Místnost 1',
                'action' => 'QR_SCANNED',
                'result' => 'SUCCESS',
                'access_granted' => true,
                'ip' => '192.168.1.100',
                'created_at' => now()->subHours(2),
            ],
            [
                'reservation_id' => $reservations->skip(1)->first()->id,
                'user_id' => $users->skip(1)->first()->id,
                'room_id' => $reservations->skip(1)->first()->room_id,
                'location' => 'Místnost 2',
                'action' => 'QR_SCANNED',
                'result' => 'SUCCESS',
                'access_granted' => true,
                'ip' => '192.168.1.101',
                'created_at' => now()->subHours(1),
            ],
            [
                'reservation_id' => null,
                'user_id' => $users->skip(2)->first()->id,
                'room_id' => $reservations->first()->room_id,
                'location' => 'Místnost 1',
                'action' => 'QR_SCANNED',
                'result' => 'FAILED',
                'access_granted' => false,
                'failure_reason' => 'Invalid reservation time',
                'ip' => '192.168.1.102',
                'created_at' => now()->subMinutes(30),
            ],
        ];

        foreach ($logs as $log) {
            AccessLog::create($log);
        }
    }
}
