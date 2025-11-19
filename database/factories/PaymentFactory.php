<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Payment;
use App\Models\Reservation;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition()
    {
        $reservation = Reservation::inRandomOrder()->first();
        $amount = $reservation?->price ?? $this->faker->numberBetween(100, 500);

        return [
            'reservation_id' => $reservation?->id,
            'amount' => $amount,
            'currency' => 'CZK',
            'paid_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
