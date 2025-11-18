<?php

namespace App\Console\Commands;

use App\Services\ReaderMonitoringService;
use Illuminate\Console\Command;

class MonitorReadersCommand extends Command
{
    protected $signature = 'readers:monitor {--verbose}';

    protected $description = 'Monitor all QR readers and create alerts for issues';

    public function handle(ReaderMonitoringService $monitoring): int
    {
        $this->info('🔍 Checking all readers...');

        try {
            $monitoring->checkAllReaders();
            
            $this->info('✅ Reader monitoring completed');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Error during monitoring: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }
}
