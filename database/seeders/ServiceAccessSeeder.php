<?php

namespace Database\Seeders;

use App\Models\ServiceAccess;
use App\Models\User;
use Illuminate\Database\Seeder;

class ServiceAccessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', '!=', 'admin')->limit(4)->get();
        
        if ($users->isEmpty()) {
            return;
        }

        $accesses = [
            [
                'user_id' => $users->first()->id,
                'access_type' => 'cleaning',
                'access_code' => 'CLEAN-' . strtoupper(bin2hex(random_bytes(6))),
                'description' => 'Přístup pro tým údržby a úklidu',
                'allowed_rooms' => json_encode(['*']),
                'unlimited_access' => false,
                'valid_from' => now()->startOfDay(),
                'valid_until' => now()->addMonths(3)->endOfDay(),
                'usage_count' => 8,
                'last_used_at' => now()->subHours(2),
                'enabled' => true,
                'revoked' => false,
                'revoke_reason' => null,
            ],
            [
                'user_id' => $users->count() > 1 ? $users->get(1)->id : $users->first()->id,
                'access_type' => 'maintenance',
                'access_code' => 'MAINT-' . strtoupper(bin2hex(random_bytes(6))),
                'description' => 'Údržba a opravy technického vybavení',
                'allowed_rooms' => json_encode([1, 2, 3]),
                'unlimited_access' => false,
                'valid_from' => now()->startOfDay(),
                'valid_until' => now()->addMonths(6)->endOfDay(),
                'usage_count' => 3,
                'last_used_at' => now()->subDays(1),
                'enabled' => true,
                'revoked' => false,
                'revoke_reason' => null,
            ],
            [
                'user_id' => $users->count() > 2 ? $users->get(2)->id : $users->first()->id,
                'access_type' => 'admin',
                'access_code' => 'ADMIN-' . strtoupper(bin2hex(random_bytes(6))),
                'description' => 'Administrativní přístup k veškerémy zařízením',
                'allowed_rooms' => json_encode(['*']),
                'unlimited_access' => true,
                'valid_from' => now()->startOfDay(),
                'valid_until' => null,
                'usage_count' => 25,
                'last_used_at' => now()->subMinutes(30),
                'enabled' => true,
                'revoked' => false,
                'revoke_reason' => null,
            ],
            [
                'user_id' => $users->count() > 3 ? $users->get(3)->id : $users->first()->id,
                'access_type' => 'cleaning',
                'access_code' => 'CLEAN-' . strtoupper(bin2hex(random_bytes(6))),
                'description' => 'Přístup pro večerní úklid',
                'allowed_rooms' => json_encode(['*']),
                'unlimited_access' => false,
                'valid_from' => now()->startOfDay(),
                'valid_until' => now()->addMonth()->endOfDay(),
                'usage_count' => 0,
                'last_used_at' => null,
                'enabled' => false,
                'revoked' => true,
                'revoke_reason' => 'Zaměstnanec skončil',
            ],
        ];

        foreach ($accesses as $access) {
            ServiceAccess::create($access);
        }
    }
}
