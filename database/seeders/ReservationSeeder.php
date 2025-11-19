<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\User;
use App\Models\Room;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', 'user')->get();
        $rooms = Room::all();

        if ($users->isEmpty() || $rooms->isEmpty()) {
            return;
        }

        // Create reservations
        $reservations = [
            [
                'user_id' => $users->first()->id,
                'room_id' => $rooms->first()->id,
                'start_at' => now()->addDays(1)->setHour(9)->setMinute(0),
                'end_at' => now()->addDays(1)->setHour(11)->setMinute(0),
                'status' => 'confirmed',
                'access_token' => bin2hex(random_bytes(32)),
                'token_valid_from' => now(),
                'token_expires_at' => now()->addDays(2),
            ],
            [
                'user_id' => $users->skip(1)->first()->id,
                'room_id' => $rooms->skip(1)->first()->id ?? $rooms->first()->id,
                'start_at' => now()->addDays(2)->setHour(14)->setMinute(0),
                'end_at' => now()->addDays(2)->setHour(16)->setMinute(0),
                'status' => 'confirmed',
                'access_token' => bin2hex(random_bytes(32)),
                'token_valid_from' => now(),
                'token_expires_at' => now()->addDays(3),
            ],
            [
                'user_id' => $users->skip(2)->first()->id,
                'room_id' => $rooms->first()->id,
                'start_at' => now()->addDays(3)->setHour(10)->setMinute(0),
                'end_at' => now()->addDays(3)->setHour(12)->setMinute(0),
                'status' => 'pending',
                'access_token' => null,
            ],
        ];

        foreach ($reservations as $reservation) {
            Reservation::create($reservation);
        }
    }
}
