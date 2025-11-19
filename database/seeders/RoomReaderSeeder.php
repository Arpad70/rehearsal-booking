<?php

namespace Database\Seeders;

use App\Models\RoomReader;
use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomReaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = Room::all();

        if ($rooms->isEmpty()) {
            return;
        }

        $readers = [
            [
                'room_id' => $rooms->first()->id,
                'reader_name' => 'QR Reader - Místnost 1',
                'reader_ip' => '192.168.1.50',
                'reader_port' => 8080,
                'reader_token' => 'token_room1_' . bin2hex(random_bytes(8)),
                'enabled' => true,
                'door_lock_type' => 'relay',
                'door_lock_config' => json_encode([
                    'url' => 'http://192.168.1.100/relay/1/on',
                    'pin' => 1,
                    'duration' => 5,
                ]),
            ],
            [
                'room_id' => $rooms->skip(1)->first()->id ?? $rooms->first()->id,
                'reader_name' => 'QR Reader - Místnost 2',
                'reader_ip' => '192.168.1.51',
                'reader_port' => 8080,
                'reader_token' => 'token_room2_' . bin2hex(random_bytes(8)),
                'enabled' => true,
                'door_lock_type' => 'relay',
                'door_lock_config' => json_encode([
                    'url' => 'http://192.168.1.101/relay/1/on',
                    'pin' => 1,
                    'duration' => 5,
                ]),
            ],
        ];

        foreach ($readers as $reader) {
            RoomReader::create($reader);
        }
    }
}
