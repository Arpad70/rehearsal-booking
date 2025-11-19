<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\Equipment;
use Illuminate\Database\Seeder;

class RoomEquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Místnost 1 - velká konferenční místnost
        $room1 = Room::where('name', 'Místnost 1')->first();
        if ($room1) {
            $room1->equipment()->attach([
                Equipment::where('serial_number', 'MIXER-001')->first()->id => [
                    'quantity' => 1,
                    'installed' => true,
                    'status' => 'operational',
                    'condition_notes' => 'Funkční, záruka vypršela',
                    'last_inspection' => now()->subDays(30),
                ],
                Equipment::where('serial_number', 'MIC-001')->first()->id => [
                    'quantity' => 2,
                    'installed' => true,
                    'status' => 'operational',
                    'condition_notes' => 'Oba mikrofony v pořádku',
                    'last_inspection' => now()->subDays(15),
                ],
                Equipment::where('serial_number', 'TABLE-001')->first()->id => [
                    'quantity' => 1,
                    'installed' => true,
                    'status' => 'operational',
                    'condition_notes' => 'Stabilní, bez vad',
                    'last_inspection' => now()->subDays(60),
                ],
                Equipment::where('serial_number', 'AC-001')->first()->id => [
                    'quantity' => 1,
                    'installed' => true,
                    'status' => 'operational',
                    'condition_notes' => 'Funkční, potřebuje čištění filtru',
                    'last_inspection' => now()->subDays(45),
                ],
                Equipment::where('serial_number', 'ROUTER-001')->first()->id => [
                    'quantity' => 1,
                    'installed' => true,
                    'status' => 'operational',
                    'condition_notes' => 'WiFi signál v pořádku',
                    'last_inspection' => now()->subDays(7),
                ],
            ]);
        }

        // Místnost 2 - prezentační místnost
        $room2 = Room::where('name', 'Místnost 2')->first();
        if ($room2) {
            $room2->equipment()->attach([
                Equipment::where('serial_number', 'PROJ-001')->first()->id => [
                    'quantity' => 1,
                    'installed' => true,
                    'status' => 'operational',
                    'condition_notes' => 'Lampa vyměněna v říjnu',
                    'last_inspection' => now()->subDays(20),
                ],
                Equipment::where('serial_number', 'TV-001')->first()->id => [
                    'quantity' => 1,
                    'installed' => true,
                    'status' => 'operational',
                    'condition_notes' => 'Displej čistý, HDMI port v pořádku',
                    'last_inspection' => now()->subDays(35),
                ],
                Equipment::where('serial_number', 'TABLE-002')->first()->id => [
                    'quantity' => 1,
                    'installed' => true,
                    'status' => 'operational',
                    'condition_notes' => 'Zadeřená vrchní deska',
                    'last_inspection' => now()->subDays(50),
                ],
                Equipment::where('serial_number', 'LED-001')->first()->id => [
                    'quantity' => 2,
                    'installed' => true,
                    'status' => 'operational',
                    'condition_notes' => 'Oba panely svítí správně',
                    'last_inspection' => now()->subDays(25),
                ],
            ]);
        }

        // Místnost 3 - malá jednací místnost
        $room3 = Room::where('name', 'Místnost 3')->first();
        if ($room3) {
            $room3->equipment()->attach([
                Equipment::where('serial_number', 'CAM-001')->first()->id => [
                    'quantity' => 1,
                    'installed' => true,
                    'status' => 'operational',
                    'condition_notes' => 'Webkamera pro online schůze',
                    'last_inspection' => now()->subDays(10),
                ],
                Equipment::where('serial_number', 'HUM-001')->first()->id => [
                    'quantity' => 1,
                    'installed' => false,
                    'status' => 'operational',
                    'condition_notes' => 'V úschově, používá se podle potřeby',
                    'last_inspection' => now()->subDays(60),
                ],
                Equipment::where('serial_number', 'LAMP-001')->first()->id => [
                    'quantity' => 1,
                    'installed' => true,
                    'status' => 'needs_repair',
                    'condition_notes' => 'Zjizvené sklo halogenky, potřebuje výměnu',
                    'last_inspection' => now()->subDays(5),
                ],
            ]);
        }
    }
}
