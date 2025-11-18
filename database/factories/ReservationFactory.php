<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Room;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory {  
    protected $model = Reservation::class;  
    public function definition() {  
        $start = Carbon::now()->addDays($this->faker->numberBetween(1,10))->hour(18)->minute(0);  
        $end = (clone $start)->addHours(2);  
        return [  
            'user_id'=>User::factory(),  
            'room_id'=>Room::factory(),  
            'start_at'=>$start,  
            'end_at'=>$end,  
            'status'=>'pending',  
            'token_valid_from'=>$start->subMinutes(5),  
            'token_expires_at'=>$end->addMinutes(5),  
        ];  
    }  
}  