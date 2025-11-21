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

        // QR Readers (Entry E QR R1)
        Device::firstOrCreate(
            ['ip' => '172.17.0.1:9101'],
            [
                'type' => 'qr_reader',
                'room_id' => $room1->id,
                'meta' => json_encode([
                    'name' => 'QR Reader - Studio 1',
                    'description' => 'Entry E QR R1 door scanner',
                    'model' => 'Entry E QR R1',
                    'firmware' => 'v3.2.1',
                    'port' => 9101,
                    'enabled' => true,
                ]),
            ]
        );

        Device::firstOrCreate(
            ['ip' => '172.17.0.1:9102'],
            [
                'type' => 'qr_reader',
                'room_id' => $room2->id,
                'meta' => json_encode([
                    'name' => 'QR Reader - Studio 2',
                    'description' => 'Entry E QR R1 door scanner',
                    'model' => 'Entry E QR R1',
                    'firmware' => 'v3.2.1',
                    'port' => 9102,
                    'enabled' => true,
                ]),
            ]
        );

        // RFID Keypads
        Device::firstOrCreate(
            ['ip' => '172.17.0.1:9401'],
            [
                'type' => 'keypad',
                'room_id' => $room1->id,
                'meta' => json_encode([
                    'name' => 'Keypad - Studio 1',
                    'description' => 'RFID Keypad 7612 access control',
                    'model' => 'RFID Keypad 7612',
                    'firmware' => 'v4.1.2',
                    'port' => 9401,
                    'enabled' => true,
                ]),
            ]
        );

        Device::firstOrCreate(
            ['ip' => '172.17.0.1:9402'],
            [
                'type' => 'keypad',
                'room_id' => $room2->id,
                'meta' => json_encode([
                    'name' => 'Keypad - Studio 2',
                    'description' => 'RFID Keypad 7612 access control',
                    'model' => 'RFID Keypad 7612',
                    'firmware' => 'v4.1.2',
                    'port' => 9402,
                    'enabled' => true,
                ]),
            ]
        );

        // IP Cameras (EVOLVEO Detective POE8 SMART)
        Device::firstOrCreate(
            ['ip' => '172.17.0.1:9201'],
            [
                'type' => 'camera',
                'room_id' => $room1->id,
                'meta' => json_encode([
                    'name' => 'Camera 1 - Studio 1',
                    'description' => 'EVOLVEO Detective POE8 SMART',
                    'model' => 'Detective POE8 SMART',
                    'firmware' => 'v2.3.5',
                    'port' => 9201,
                    'resolution' => '8MP (3840×2160)',
                    'enabled' => true,
                ]),
            ]
        );

        Device::firstOrCreate(
            ['ip' => '172.17.0.1:9202'],
            [
                'type' => 'camera',
                'room_id' => $room1->id,
                'meta' => json_encode([
                    'name' => 'Camera 2 - Studio 1',
                    'description' => 'EVOLVEO Detective POE8 SMART',
                    'model' => 'Detective POE8 SMART',
                    'firmware' => 'v2.3.5',
                    'port' => 9202,
                    'resolution' => '8MP (3840×2160)',
                    'enabled' => true,
                ]),
            ]
        );

        Device::firstOrCreate(
            ['ip' => '172.17.0.1:9203'],
            [
                'type' => 'camera',
                'room_id' => $room2->id,
                'meta' => json_encode([
                    'name' => 'Camera 1 - Studio 2',
                    'description' => 'EVOLVEO Detective POE8 SMART',
                    'model' => 'Detective POE8 SMART',
                    'firmware' => 'v2.3.5',
                    'port' => 9203,
                    'resolution' => '8MP (3840×2160)',
                    'enabled' => true,
                ]),
            ]
        );

        // Soundcraft Mixers
        Device::firstOrCreate(
            ['ip' => '172.17.0.1:9301'],
            [
                'type' => 'mixer',
                'room_id' => $room1->id,
                'meta' => json_encode([
                    'name' => 'Mixer - Studio 1',
                    'description' => 'Soundcraft Ui24R digital mixer',
                    'model' => 'Soundcraft Ui24R',
                    'firmware' => 'v5.2',
                    'port' => 9301,
                    'channels' => 24,
                    'enabled' => true,
                ]),
            ]
        );

        Device::firstOrCreate(
            ['ip' => '172.17.0.1:9302'],
            [
                'type' => 'mixer',
                'room_id' => $room2->id,
                'meta' => json_encode([
                    'name' => 'Mixer - Studio 2',
                    'description' => 'Soundcraft Ui24R digital mixer',
                    'model' => 'Soundcraft Ui24R',
                    'firmware' => 'v5.2',
                    'port' => 9302,
                    'channels' => 24,
                    'enabled' => true,
                ]),
            ]
        );

        echo "✅ Devices created successfully\n";
    }
}
