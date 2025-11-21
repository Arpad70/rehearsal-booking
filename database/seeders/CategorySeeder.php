<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Smazat existující kategorie
        Category::query()->delete();

        $categories = [
            [
                'name' => 'Audio - Mikrofony a reproboxy',
                'slug' => 'audio',
                'description' => 'Mikrofony, reproboxy, sluchátka a další audio zařízení pro ozvučení',
                'icon' => '🔊',
                'color' => '#3B82F6', // modrá
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Hudební nástroje',
                'slug' => 'instrument',
                'description' => 'Kytary, bicí soupravy, klávesy, baskytary a další hudební nástroje',
                'icon' => '🎸',
                'color' => '#EF4444', // červená
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Osvětlení',
                'slug' => 'lighting',
                'description' => 'LED světla, reflektory, moving heads a světelné efekty',
                'icon' => '💡',
                'color' => '#FBBF24', // žlutá
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Nahrávací technika',
                'slug' => 'recording',
                'description' => 'Audio interface, rekordéry, mikrofony pro nahrávání',
                'icon' => '🎙️',
                'color' => '#8B5CF6', // fialová
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Mixážní pulty',
                'slug' => 'mixer',
                'description' => 'Analogové a digitální mixážní pulty různých velikostí',
                'icon' => '🎚️',
                'color' => '#10B981', // zelená
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Příslušenství a kabely',
                'slug' => 'accessory',
                'description' => 'Kabely, stojany, pouzdra, adaptéry a další drobné příslušenství',
                'icon' => '🔌',
                'color' => '#6B7280', // šedá
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Nábytek a stage prvky',
                'slug' => 'furniture',
                'description' => 'Židle, stoly, rack skříně, pódium a další nábytek',
                'icon' => '🪑',
                'color' => '#92400E', // hnědá
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Ostatní vybavení',
                'slug' => 'other',
                'description' => 'Power kondicionéry, DI boxy a další specializované vybavení',
                'icon' => '📦',
                'color' => '#64748B', // slate
                'sort_order' => 8,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        $this->command->info('✓ Vytvořeno ' . count($categories) . ' kategorií');
    }
}
