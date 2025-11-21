<?php

namespace App\Console\Commands;

use App\Services\DeviceHealthService;
use Illuminate\Console\Command;

class DeviceHealthCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'devices:health-check
                            {--device= : Specific device ID to check}
                            {--type= : Check only devices of specific type (qr_reader, shelly, camera, keypad, mixer)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform health check on IoT devices';

    /**
     * Execute the console command.
     */
    public function handle(DeviceHealthService $healthService)
    {
        $this->info('🔍 Device Health Check');
        $this->newLine();

        $deviceId = $this->option('device');
        $type = $this->option('type');

        if ($deviceId) {
            // Check specific device
            $device = \App\Models\Device::find($deviceId);
            
            if (!$device) {
                $this->error("❌ Device with ID {$deviceId} not found");
                return Command::FAILURE;
            }
            
            $this->checkDevice($device, $healthService);
        } elseif ($type) {
            // Check devices of specific type
            $devices = \App\Models\Device::where('type', $type)->get();
            
            if ($devices->isEmpty()) {
                $this->warn("⚠️  No devices found with type: {$type}");
                return Command::SUCCESS;
            }
            
            foreach ($devices as $device) {
                $this->checkDevice($device, $healthService);
            }
        } else {
            // Check all devices
            $devices = \App\Models\Device::all();
            
            if ($devices->isEmpty()) {
                $this->warn('⚠️  No devices registered in database');
                return Command::SUCCESS;
            }
            
            foreach ($devices as $device) {
                $this->checkDevice($device, $healthService);
            }
        }

        $this->newLine();
        $this->displayStats($healthService);

        return Command::SUCCESS;
    }

    /**
     * Check single device
     */
    private function checkDevice(\App\Models\Device $device, DeviceHealthService $healthService): void
    {
        $name = $device->name;
        $type = $device->type;
        
        $this->info("Checking: {$name} ({$type})");
        
        $result = $healthService->performHealthCheck($device);
        
        if ($result['status'] === 'online') {
            $responseTime = $result['response_time_ms'];
            $this->line("  <fg=green>✅ ONLINE</> - Response time: {$responseTime}ms");
        } else {
            $message = $result['message'] ?? 'Unknown error';
            $this->line("  <fg=red>❌ OFFLINE</> - {$message}");
        }
    }

    /**
     * Display overall statistics
     */
    private function displayStats(DeviceHealthService $healthService): void
    {
        $stats = $healthService->getAvailabilityStats();
        
        $this->info('📊 Overall Statistics:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Devices', $stats['total']],
                ['Online', "<fg=green>{$stats['online']}</>"],
                ['Offline', "<fg=red>{$stats['offline']}</>"],
                ['Availability', "<fg=cyan>{$stats['availability_percentage']}%</>"],
            ]
        );
    }
}
