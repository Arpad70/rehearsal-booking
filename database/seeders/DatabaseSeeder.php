<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Core data
            RoomSeeder::class,
            UserSeeder::class,
            
            // Devices system
            DeviceSeeder::class,
            
            // Equipment system
            EquipmentSeeder::class,
            RoomEquipmentSeeder::class,
            
            // Reader system
            GlobalReaderSeeder::class,
            RoomReaderSeeder::class,
            
            // Access and reservations
            ReservationSeeder::class,
            ServiceAccessSeeder::class,
            
            // QR codes and alerts
            BackupQRCodeSeeder::class,
            ReaderAlertSeeder::class,
            
            // Access logs and audit trail
            AccessLogSeeder::class,
            AuditLogSeeder::class,
            
            // Power monitoring data
            PowerMonitoringSeeder::class,
        ]);
    }
}
