<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reservation;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentsSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure reservations have a price: take room default or config fallback
        $default = config('services.reservations.default_price', 250);

        Reservation::with('room')->chunk(100, function ($reservations) use ($default) {
            foreach ($reservations as $res) {
                if (empty($res->price)) {
                    $roomDefault = $res->room?->reservation_default_price;
                    $res->price = $roomDefault ?? $default;
                    $res->save();
                }
            }
        });

        // Create payments for about half of reservations
        $reservations = Reservation::inRandomOrder()->take(50)->get();
        foreach ($reservations as $res) {
            Payment::create([
                'reservation_id' => $res->id,
                'amount' => $res->price ?? $default,
                'currency' => 'CZK',
                'paid_at' => now()->subDays(rand(0, 20))->subHours(rand(0, 23)),
            ]);
        }

        // Create some standalone bank payments (no reservation)
        for ($i = 0; $i < 10; $i++) {
            Payment::create([
                'reservation_id' => null,
                'amount' => rand(100, 1000),
                'currency' => 'CZK',
                'paid_at' => now()->subDays(rand(0, 40)),
            ]);
        }
    }
}
