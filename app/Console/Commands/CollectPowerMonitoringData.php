<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PowerMonitoringService;

class CollectPowerMonitoringData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'power-monitoring:collect {--device-id= : Specific device ID to collect data from}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collect power monitoring data from all Shelly devices';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $service = new PowerMonitoringService();

        if ($this->option('device-id')) {
            $deviceId = $this->option('device-id');
            $device = \App\Models\Device::findOrFail($deviceId);

            $this->info("Collecting power monitoring data for device {$deviceId}...");

            if ($service->collectDeviceData($device)) {
                $this->info('✓ Data collected successfully');
                return Command::SUCCESS;
            } else {
                $this->error('✗ Failed to collect data');
                return Command::FAILURE;
            }
        }

        $this->info('Collecting power monitoring data from all devices...');

        $collected = $service->collectAllData();

        $this->info("✓ Data collected from {$collected} devices");

        return Command::SUCCESS;
    }
}
