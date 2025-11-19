<?php

namespace Database\Seeders;

use App\Models\ReaderAlert;
use App\Models\RoomReader;
use App\Models\GlobalReader;
use Illuminate\Database\Seeder;

class ReaderAlertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roomReaders = RoomReader::all();
        $globalReaders = GlobalReader::all();

        $alerts = [
            [
                'room_reader_id' => $roomReaders->isNotEmpty() ? $roomReaders->first()->id : null,
                'global_reader_id' => null,
                'reader_type' => 'room_reader',
                'alert_type' => 'connection_failed',
                'message' => 'Čtečka se nepodařila připojit k síti',
                'severity' => 'high',
                'resolved' => true,
                'resolution_notes' => 'Problém vyřešen restartem čtečky',
                'resolved_at' => now()->subHours(4),
                'metadata' => json_encode(['error' => 'Connection timeout', 'code' => 'CONN_TIMEOUT']),
            ],
            [
                'room_reader_id' => null,
                'global_reader_id' => $globalReaders->isNotEmpty() ? $globalReaders->first()->id : null,
                'reader_type' => 'global_reader',
                'alert_type' => 'offline',
                'message' => 'Globální čtečka je offline',
                'severity' => 'critical',
                'resolved' => false,
                'resolution_notes' => null,
                'resolved_at' => null,
                'metadata' => json_encode(['status' => 'offline', 'last_seen' => now()->subMinutes(30)->toIso8601String()]),
            ],
            [
                'room_reader_id' => $roomReaders->isNotEmpty() && $roomReaders->count() > 1 ? $roomReaders->get(1)->id : $roomReaders->first()?->id,
                'global_reader_id' => null,
                'reader_type' => 'room_reader',
                'alert_type' => 'configuration_error',
                'message' => 'Chyba v konfiguraci čtečky',
                'severity' => 'medium',
                'resolved' => true,
                'resolution_notes' => 'Konfigurační chyba opravena',
                'resolved_at' => now()->subMinutes(15),
                'metadata' => json_encode(['error' => 'Invalid relay config', 'field' => 'door_lock_config']),
            ],
            [
                'room_reader_id' => null,
                'global_reader_id' => $globalReaders->isNotEmpty() && $globalReaders->count() > 1 ? $globalReaders->get(1)->id : $globalReaders->first()?->id,
                'reader_type' => 'global_reader',
                'alert_type' => 'high_failure_rate',
                'message' => 'Vysoká míra selhání přístupů',
                'severity' => 'high',
                'resolved' => false,
                'resolution_notes' => null,
                'resolved_at' => null,
                'metadata' => json_encode(['failure_rate' => '25%', 'sample_size' => 100, 'threshold' => '10%']),
            ],
        ];

        foreach ($alerts as $alert) {
            if ($alert['room_reader_id'] !== null || $alert['global_reader_id'] !== null) {
                ReaderAlert::create($alert);
            }
        }
    }
}
