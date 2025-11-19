<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class AuditLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        
        if ($users->isEmpty()) {
            return;
        }

        $admin = $users->where('email', 'admin@example.com')->first() ?? $users->first();

        $logs = [
            [
                'action' => 'created',
                'model_type' => 'Room',
                'model_id' => 1,
                'user_id' => $admin->id,
                'old_values' => null,
                'new_values' => json_encode(['name' => 'Studio A', 'capacity' => 20]),
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'action' => 'updated',
                'model_type' => 'Device',
                'model_id' => 1,
                'user_id' => $admin->id,
                'old_values' => json_encode(['status' => 'off']),
                'new_values' => json_encode(['status' => 'on']),
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64)',
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
            [
                'action' => 'created',
                'model_type' => 'Reservation',
                'model_id' => 1,
                'user_id' => $users->where('email', 'user@example.com')->first()?->id ?? $users->skip(1)->first()?->id,
                'old_values' => null,
                'new_values' => json_encode(['status' => 'confirmed', 'room_id' => 1]),
                'ip_address' => '192.168.1.150',
                'user_agent' => 'Mozilla/5.0 (Android)',
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'action' => 'updated',
                'model_type' => 'Equipment',
                'model_id' => 1,
                'user_id' => $admin->id,
                'old_values' => json_encode(['is_critical' => false, 'quantity_available' => 2]),
                'new_values' => json_encode(['is_critical' => true, 'quantity_available' => 3]),
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X)',
                'created_at' => now()->subHours(12),
                'updated_at' => now()->subHours(12),
            ],
            [
                'action' => 'created',
                'model_type' => 'GlobalReader',
                'model_id' => 1,
                'user_id' => $admin->id,
                'old_values' => null,
                'new_values' => json_encode(['enabled' => true, 'reader_ip' => '192.168.1.60', 'access_type' => 'entrance']),
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'created_at' => now()->subHours(6),
                'updated_at' => now()->subHours(6),
            ],
        ];

        foreach ($logs as $log) {
            AuditLog::create($log);
        }
    }
}
