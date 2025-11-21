<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Device>
 */
class DeviceFactory extends Factory
{
    protected $model = Device::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id' => null,
            'type' => $this->faker->randomElement(['shelly', 'lock', 'reader', 'qr_reader', 'keypad', 'camera', 'mixer']),
            'ip' => $this->faker->localIpv4() . ':' . $this->faker->numberBetween(8000, 9999),
            'meta' => [
                'name' => $this->faker->words(3, true),
                'location' => $this->faker->optional()->word(),
            ],
        ];
    }

    /**
     * Indicate that the device is a QR reader.
     */
    public function qrReader(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'qr_reader',
            'ip' => '172.17.0.1:' . $this->faker->numberBetween(9101, 9102),
            'meta' => [
                'name' => 'QR Reader ' . $this->faker->numberBetween(1, 10),
                'port' => $this->faker->numberBetween(9101, 9102),
            ],
        ]);
    }

    /**
     * Indicate that the device is a keypad.
     */
    public function keypad(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'keypad',
            'ip' => '172.17.0.1:' . $this->faker->numberBetween(9401, 9402),
            'meta' => [
                'name' => 'Keypad ' . $this->faker->numberBetween(1, 10),
                'port' => $this->faker->numberBetween(9401, 9402),
            ],
        ]);
    }

    /**
     * Indicate that the device is a camera.
     */
    public function camera(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'camera',
            'ip' => '172.17.0.1:' . $this->faker->numberBetween(9201, 9203),
            'meta' => [
                'name' => 'Camera ' . $this->faker->numberBetween(1, 10),
                'port' => $this->faker->numberBetween(9201, 9203),
            ],
        ]);
    }

    /**
     * Indicate that the device is a mixer.
     */
    public function mixer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'mixer',
            'ip' => '172.17.0.1:' . $this->faker->numberBetween(9301, 9302),
            'meta' => [
                'name' => 'Mixer ' . $this->faker->numberBetween(1, 10),
                'port' => $this->faker->numberBetween(9301, 9302),
            ],
        ]);
    }

    /**
     * Indicate that the device is a Shelly relay.
     */
    public function shelly(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'shelly',
            'ip' => $this->faker->localIpv4(),
            'meta' => [
                'name' => 'Shelly ' . $this->faker->numberBetween(1, 10),
                'model' => 'Shelly Plus 2PM',
            ],
        ]);
    }

    /**
     * Indicate that the device belongs to a room.
     */
    public function forRoom(Room|int|null $room = null): static
    {
        return $this->state(fn (array $attributes) => [
            'room_id' => $room instanceof Room ? $room->id : $room,
        ]);
    }
}
