<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\PowerMonitoringService;
use Throwable;

class CollectPowerMonitoringDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function handle(): void
    {
        try {
            $service = new PowerMonitoringService();

            // Collect data from all devices
            $collected = $service->collectAllData();

            Log::info('CollectPowerMonitoringDataJob: Data collection completed', [
                'devices_collected' => $collected,
            ]);
        } catch (Throwable $e) {
            Log::error('CollectPowerMonitoringDataJob error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('CollectPowerMonitoringDataJob failed: ' . $exception->getMessage(), [
            'exception' => $exception,
        ]);
    }
}
