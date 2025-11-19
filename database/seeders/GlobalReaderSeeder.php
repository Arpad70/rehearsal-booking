<?php

namespace Database\Seeders;

use App\Models\GlobalReader;
use Illuminate\Database\Seeder;

class GlobalReaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $readers = [
            [
                'reader_name' => 'Hlavní vchod',
                'access_type' => 'entrance',
                'reader_ip' => '192.168.1.60',
                'reader_port' => 8080,
                'reader_token' => 'token_entrance_' . bin2hex(random_bytes(8)),
                'enabled' => true,
                'door_lock_type' => 'relay',
                'door_lock_config' => json_encode([
                    'url' => 'http://192.168.1.100/relay/2/on',
                    'pin' => 2,
                    'duration' => 10,
                ]),
                'access_minutes_before' => 30,
                'access_minutes_after' => 30,
            ],
            [
                'reader_name' => 'Servisní vchod',
                'access_type' => 'service',
                'reader_ip' => '192.168.1.61',
                'reader_port' => 8080,
                'reader_token' => 'token_service_' . bin2hex(random_bytes(8)),
                'enabled' => true,
                'door_lock_type' => 'api',
                'door_lock_config' => json_encode([
                    'api_url' => 'https://api.smartlock.com/unlock',
                    'api_key' => 'key_service_' . bin2hex(random_bytes(16)),
                    'lock_id' => 'service_entrance',
                    'duration' => 15,
                ]),
                'access_minutes_before' => 60,
                'access_minutes_after' => 60,
                'allowed_service_types' => json_encode(['cleaning', 'maintenance']),
            ],
            [
                'reader_name' => 'Administrační přístup',
                'access_type' => 'admin',
                'reader_ip' => '192.168.1.62',
                'reader_port' => 8080,
                'reader_token' => 'token_admin_' . bin2hex(random_bytes(8)),
                'enabled' => true,
                'door_lock_type' => 'webhook',
                'door_lock_config' => json_encode([
                    'webhook_url' => 'https://webhook.example.com/unlock',
                    'secret' => 'secret_' . bin2hex(random_bytes(16)),
                    'duration' => 5,
                ]),
                'access_minutes_before' => 0,
                'access_minutes_after' => 0,
                'allowed_service_types' => json_encode(['admin']),
            ],
        ];

        foreach ($readers as $reader) {
            GlobalReader::create($reader);
        }
    }
}
