<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\Room;
use Illuminate\Database\Seeder;

class DeviceSeeder extends Seeder
{
    public function run(): void
    {
        // Create test rooms first
        $room1 = Room::firstOrCreate(
            ['name' => 'Studio 1'],
            [
                'location' => 'Building A',
                'capacity' => 10,
            ]
        );

        $room2 = Room::firstOrCreate(
            ['name' => 'Studio 2'],
            [
                'location' => 'Building A',
                'capacity' => 8,
            ]
        );

        // Create test Shelly devices with IP addresses for testing
        Device::firstOrCreate(
            ['ip' => '192.168.1.100'],
            [
                'type' => 'shelly',
                'meta' => json_encode([
                    'name' => 'Climate Control Studio 1',
                    'description' => 'Shelly Pro 2PM for HVAC system',
                    'room_id' => $room1->id,
                    'model' => 'Shelly Pro 2PM',
                    'enabled' => true,
                ]),
            ]
        );

        Device::firstOrCreate(
            ['ip' => '192.168.1.101'],
            [
                'type' => 'shelly',
                'meta' => json_encode([
                    'name' => 'Lighting Studio 1',
                    'description' => 'Shelly Plus 2PM for studio lighting',
                    'room_id' => $room1->id,
                    'model' => 'Shelly Plus 2PM',
                    'enabled' => true,
                ]),
            ]
        );

        Device::firstOrCreate(
            ['ip' => '192.168.1.102'],
            [
                'type' => 'shelly',
                'meta' => json_encode([
                    'name' => 'Climate Control Studio 2',
                    'description' => 'Shelly Pro 2PM for HVAC',
                    'room_id' => $room2->id,
                    'model' => 'Shelly Pro 2PM',
                    'enabled' => true,
                ]),
            ]
        );

        Device::firstOrCreate(
            ['ip' => '192.168.1.103'],
            [
                'type' => 'shelly',
                'meta' => json_encode([
                    'name' => 'Power Monitoring - Main',
                    'description' => 'Main power monitoring device',
                    'model' => 'Shelly Pro 4PM',
                    'enabled' => true,
                ]),
            ]
        );

        echo "✅ Devices created successfully\n";
    }
}
