<?php

namespace Database\Seeders;

use App\Models\Promotion;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Promotion::create([
            'title' => '🎉 Registrace = 20% sleva!',
            'description' => 'Zaregistrujte se ještě dnes a získejte exkluzivní slevu 20% na vaši první rezervaci zkušebny. Nabídka platná pouze pro nové uživatele!',
            'type' => 'registration_discount',
            'discount_code' => 'WELCOME20',
            'discount_percentage' => 20,
            'is_active' => true,
            'is_permanent' => false,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonth(),
            'priority' => 10,
            'target_audience' => ['guest'],
            'max_displays' => null,
            'show_once_per_session' => true,
            'image_url' => 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=800&q=80',
            'button_text' => 'Registrovat se se slevou',
            'button_url' => null,
        ]);

        Promotion::create([
            'title' => '🎸 Víkendová akce -30%',
            'description' => 'Tento víkend si rezervujte zkušebnu s 30% slevou! Platí pro všechny zkušebny v sobotu a neděli. Použijte slevový kód při rezervaci.',
            'type' => 'event_discount',
            'discount_code' => 'WEEKEND30',
            'discount_percentage' => 30,
            'is_active' => true,
            'is_permanent' => false,
            'start_date' => Carbon::now()->startOfWeek()->addDays(5), // Pátek
            'end_date' => Carbon::now()->endOfWeek(), // Neděle
            'priority' => 8,
            'target_audience' => ['all'],
            'max_displays' => 1000,
            'show_once_per_session' => true,
            'image_url' => 'https://images.unsplash.com/photo-1498038432885-c6f3f1b912ee?w=800&q=80',
            'button_text' => 'Chci víkendovou slevu',
            'button_url' => '/rooms',
        ]);

        Promotion::create([
            'title' => '⚡ Nové studiové vybavení!',
            'description' => 'Právě jsme nainstalovali nové profesionální vybavení ve všech zkušebnách - špičkové aktivní monitory Yamaha HS8 a mixážní pult Behringer X32. Pojďte si to vyzkoušet!',
            'type' => 'announcement',
            'is_active' => true,
            'is_permanent' => false,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addWeek(),
            'priority' => 5,
            'target_audience' => ['registered'],
            'max_displays' => null,
            'show_once_per_session' => true,
            'image_url' => 'https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?w=800&q=80',
            'button_text' => 'Super, beru na vědomí',
            'button_url' => null,
        ]);

        Promotion::create([
            'title' => '📢 Novinky v rezervačním systému',
            'description' => 'Nyní můžete rezervovat zkušebnu i bez registrace! Stačí vyplnit e-mail a telefon, ověřit je a zaplatit. Jednodušeji to už nejde. 🎵',
            'type' => 'general_info',
            'is_active' => false, // Neaktivní pro testování
            'is_permanent' => true,
            'priority' => 3,
            'target_audience' => ['all'],
            'max_displays' => null,
            'show_once_per_session' => true,
            'image_url' => 'https://images.unsplash.com/photo-1571330735066-03aaa9429d89?w=800&q=80',
            'button_text' => 'Chci vědět víc',
            'button_url' => '/guest-reservation',
        ]);
    }
}
