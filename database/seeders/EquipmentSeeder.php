<?php

namespace Database\Seeders;

use App\Models\Equipment;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $equipment = [
            // Audio equipment
            [
                'name' => 'Mikrofon Shure SM58',
                'description' => 'Dynamický mikrofon pro vokál a bicí',
                'category' => 'audio',
                'model' => 'SM58',
                'serial_number' => 'MIC-001',
                'quantity_available' => 5,
                'is_critical' => true,
                'location' => 'Úschovna - regál A',
                'purchase_date' => '2023-01-15',
                'warranty_expiry' => '2025-01-15',
                'maintenance_notes' => 'Pravidelná kontrola kabelů, poslední servis 11/2024',
            ],
            [
                'name' => 'Mikrofon Shure SM7B',
                'description' => 'Studiový dynamický mikrofon',
                'category' => 'audio',
                'model' => 'SM7B',
                'serial_number' => 'MIC-002',
                'quantity_available' => 2,
                'is_critical' => false,
                'location' => 'Úschovna - regál A',
                'purchase_date' => '2022-06-20',
                'warranty_expiry' => '2024-06-20',
                'maintenance_notes' => 'Kontrola nutná, záruční doba vypršela',
            ],
            [
                'name' => 'Sluchátka Audio-Technica ATH-M50x',
                'description' => 'Profesionální monitorovací sluchátka',
                'category' => 'audio',
                'model' => 'ATH-M50x',
                'serial_number' => 'HP-001,HP-002,HP-003',
                'quantity_available' => 3,
                'is_critical' => false,
                'location' => 'Úschovna - police B',
                'purchase_date' => '2023-03-10',
                'warranty_expiry' => '2025-03-10',
                'maintenance_notes' => 'Pravidelná kontrola sluchátek',
            ],
            [
                'name' => 'Mixážní pult Behringer Xenyx X2222',
                'description' => '22-kanálový analogový mixážní pult',
                'category' => 'audio',
                'model' => 'Xenyx X2222',
                'serial_number' => 'MIXER-001',
                'quantity_available' => 1,
                'is_critical' => true,
                'location' => 'Místnost 1 - stůl',
                'purchase_date' => '2021-09-05',
                'warranty_expiry' => '2023-09-05',
                'maintenance_notes' => 'Funkční, ale záruka vypršela. Doporučeno naplánovat preventivní kontrolu.',
            ],

            // Video equipment
            [
                'name' => 'Projektor BenQ MH534',
                'description' => '1080p projektor s jasností 3600 lumen',
                'category' => 'video',
                'model' => 'MH534',
                'serial_number' => 'PROJ-001',
                'quantity_available' => 1,
                'is_critical' => true,
                'location' => 'Místnost 2 - strop',
                'purchase_date' => '2022-01-20',
                'warranty_expiry' => '2024-01-20',
                'maintenance_notes' => 'Výměna lampy každých 2000 hodin. Poslední výměna 10/2024',
            ],
            [
                'name' => 'Webkamera Logitech C920',
                'description' => 'Full HD webkamera pro online vysílání',
                'category' => 'video',
                'model' => 'C920',
                'serial_number' => 'CAM-001,CAM-002',
                'quantity_available' => 2,
                'is_critical' => false,
                'location' => 'Úschovna - police C',
                'purchase_date' => '2023-05-12',
                'warranty_expiry' => '2025-05-12',
                'maintenance_notes' => 'Čištění čoček při použití',
            ],
            [
                'name' => 'TV Samsung 65"',
                'description' => 'Smart TV 4K pro prezentace',
                'category' => 'video',
                'model' => 'QN65Q80D',
                'serial_number' => 'TV-001',
                'quantity_available' => 1,
                'is_critical' => false,
                'location' => 'Místnost 3 - stěna',
                'purchase_date' => '2023-08-15',
                'warranty_expiry' => '2025-08-15',
                'maintenance_notes' => 'Pravidelné čištění displeje',
            ],

            // Furniture
            [
                'name' => 'Konferenční stůl 200x100cm',
                'description' => 'Dřevěný konferenční stůl s nastavitelnou výškou',
                'category' => 'furniture',
                'model' => 'KS-200',
                'serial_number' => 'TABLE-001,TABLE-002',
                'quantity_available' => 2,
                'is_critical' => false,
                'location' => 'Místnosti 1, 2 - střed',
                'purchase_date' => '2020-11-08',
                'warranty_expiry' => null,
                'maintenance_notes' => 'Kontrola stabilnosti a mechu. Poslední oprava: březen 2024',
            ],
            [
                'name' => 'Kancelářská židle ergonomická',
                'description' => 'Ergonomická kancelářská židle s nastavením',
                'category' => 'furniture',
                'model' => 'ERC-100',
                'serial_number' => 'CHAIR-001-010',
                'quantity_available' => 10,
                'is_critical' => false,
                'location' => 'Místnosti - rozptýleno',
                'purchase_date' => '2022-04-15',
                'warranty_expiry' => '2024-04-15',
                'maintenance_notes' => 'Kontrola polomů, výměna poškozených součástek',
            ],

            // Climate
            [
                'name' => 'Klimatizace Daikin Stylish',
                'description' => 'Inverterní klimatizace s výhřevem',
                'category' => 'climate',
                'model' => 'FTXA25AT',
                'serial_number' => 'AC-001',
                'quantity_available' => 1,
                'is_critical' => true,
                'location' => 'Místnost 1 - zeď',
                'purchase_date' => '2021-05-10',
                'warranty_expiry' => '2023-05-10',
                'maintenance_notes' => 'Servis 2x ročně. Poslední údržba: březen 2024. Filtr do výměny.',
            ],
            [
                'name' => 'Přenosný zvlhčovač vzduchu',
                'description' => 'Ultrazvukový zvlhčovač s kapacitou 2.5L',
                'category' => 'climate',
                'model' => 'HUM-250',
                'serial_number' => 'HUM-001,HUM-002,HUM-003',
                'quantity_available' => 3,
                'is_critical' => false,
                'location' => 'Úschovna - police D',
                'purchase_date' => '2023-09-01',
                'warranty_expiry' => '2025-09-01',
                'maintenance_notes' => 'Čištění každý týden během používání',
            ],

            // Lighting
            [
                'name' => 'LED panel 60x60cm',
                'description' => 'Regulovatelný LED panel s barevným spektrem',
                'category' => 'lighting',
                'model' => 'LED-600',
                'serial_number' => 'LED-001,LED-002,LED-003,LED-004',
                'quantity_available' => 4,
                'is_critical' => false,
                'location' => 'Úschovna - regál E',
                'purchase_date' => '2023-02-20',
                'warranty_expiry' => '2025-02-20',
                'maintenance_notes' => 'Kontrola konektorů a chlazení',
            ],
            [
                'name' => 'Stojánová halogenová lampa 500W',
                'description' => 'Profesionální halogenová lampa na stojanu',
                'category' => 'lighting',
                'model' => 'HAL-500',
                'serial_number' => 'LAMP-001,LAMP-002',
                'quantity_available' => 2,
                'is_critical' => false,
                'location' => 'Úschovna - regál F',
                'purchase_date' => '2022-07-15',
                'warranty_expiry' => '2024-07-15',
                'maintenance_notes' => 'Pozor na horkou lampu, pozor na celistvost skla',
            ],

            // Other
            [
                'name' => 'Wifi router TP-Link Archer AX6',
                'description' => 'WiFi 6 router s dobrým pokrytím',
                'category' => 'other',
                'model' => 'Archer AX6',
                'serial_number' => 'ROUTER-001',
                'quantity_available' => 1,
                'is_critical' => true,
                'location' => 'Místnost 1 - pod stropem',
                'purchase_date' => '2023-03-30',
                'warranty_expiry' => '2025-03-30',
                'maintenance_notes' => 'Reboot 1x měsíčně, kontrola konektivity',
            ],
            [
                'name' => 'Přenosná banka napájení 20000mAh',
                'description' => 'USB-C powerbank pro mobilní zařízení',
                'category' => 'other',
                'model' => 'PB-20000',
                'serial_number' => 'PB-001-005',
                'quantity_available' => 5,
                'is_critical' => false,
                'location' => 'Úschovna - zásuvka G',
                'purchase_date' => '2023-06-12',
                'warranty_expiry' => '2025-06-12',
                'maintenance_notes' => 'Měsíční nabíjení pro udržení kapacity',
            ],
        ];

        foreach ($equipment as $item) {
            Equipment::create($item);
        }
    }
}
