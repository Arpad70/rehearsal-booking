<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomLandingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rooms = [
            // Praha - Žižkov (5 místností)
            [
                'name' => 'Studio A - Malá zkušebna',
                'location' => 'Praha - Žižkov, Přízemí, místnost 101',
                'capacity' => 3,
                'price_per_hour' => 200.00,
                'is_public' => true,
                'description' => 'Ideální pro menší kapely nebo sólové hudebníky. Vybaveno základním bicím setem, kytarovým a basovým zesilovačem.',
                'size' => 'Malá (15 m²)',
                'image_url' => 'https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?w=600&h=400&fit=crop',
            ],
            [
                'name' => 'Studio B - Střední zkušebna',
                'location' => 'Praha - Žižkov, 1. patro, místnost 201',
                'capacity' => 5,
                'price_per_hour' => 350.00,
                'is_public' => true,
                'description' => 'Profesionální zkušebna s kvalitním zvukovým systémem. Bicí souprava Tama, zesilovače Marshall a Orange, PA systém.',
                'size' => 'Střední (30 m²)',
                'image_url' => 'https://images.unsplash.com/photo-1511379938547-c1f69419868d?w=600&h=400&fit=crop',
            ],
            [
                'name' => 'Studio C - Velká zkušebna',
                'location' => 'Praha - Žižkov, 1. patro, místnost 202',
                'capacity' => 8,
                'price_per_hour' => 500.00,
                'is_public' => true,
                'description' => 'Naše největší zkušebna s profesionálním nahrávacím vybavením. Perfektní pro větší kapely, koncertní zkoušky nebo nahrávání dem.',
                'size' => 'Velká (50 m²)',
                'image_url' => 'https://images.unsplash.com/photo-1510915361894-db8b60106cb1?w=600&h=400&fit=crop',
            ],
            [
                'name' => 'Recording Studio',
                'location' => 'Praha - Žižkov, 2. patro, místnost 301',
                'capacity' => 6,
                'price_per_hour' => 800.00,
                'is_public' => true,
                'description' => 'Profesionální nahrávací studio s izolovanou kabinou, mixážním pultem a špičkovými mikrofony. Možnost mixování a masteringu.',
                'size' => 'Střední (35 m²)',
                'image_url' => 'https://images.unsplash.com/photo-1598653222000-6b7b7a552625?w=600&h=400&fit=crop',
            ],
            [
                'name' => 'Jam Room',
                'location' => 'Praha - Žižkov, Suterén, místnost B01',
                'capacity' => 4,
                'price_per_hour' => 250.00,
                'is_public' => true,
                'description' => 'Pohodová zkušebna pro jam sessions a neformální hraní. Vintage vybavení včetně analogových zesilovačů.',
                'size' => 'Malá (20 m²)',
                'image_url' => 'https://images.unsplash.com/photo-1571330735066-03aaa9429d89?w=600&h=400&fit=crop',
            ],
            
            // Praha - Karlovo náměstí (1 místnost)
            [
                'name' => 'Studio Premium - Karlovo náměstí',
                'location' => 'Praha - Karlovo náměstí, 3. patro',
                'capacity' => 6,
                'price_per_hour' => 600.00,
                'is_public' => true,
                'description' => 'Luxusní zkušebna v centru Prahy s výhledem na Karlovo náměstí. Klimatizace, odpočinková zóna a top vybavení. Dva bicí sety, modularní PA systém, professional lighting.',
                'size' => 'Velká (45 m²)',
                'image_url' => 'https://images.unsplash.com/photo-1614963366795-8b4664d3e84b?w=600&h=400&fit=crop',
            ],
        ];

        foreach ($rooms as $roomData) {
            Room::create($roomData);
        }
        
        $this->command->info('Vytvořeno ' . count($rooms) . ' zkušeben: 5x Praha-Žižkov, 1x Praha-Karlovo náměstí');
    }
}
