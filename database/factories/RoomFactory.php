<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Room;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    protected $model = Room::class;  
    public function definition() {  
        return [  
            'name'=>$this->faker->word,  
            'location'=>$this->faker->city,  
            'capacity'=>$this->faker->numberBetween(1,6),  
            'shelly_ip'=>null,  
            'shelly_token'=>null,  
        ];  
    }
}