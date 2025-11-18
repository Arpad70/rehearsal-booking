<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\AccessLog;

class ArchiveAccessLogsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum number of attempts.
     */
    public int $tries = 3;

    /**
     * Seconds to wait before retrying.
     */
    public int $backoff = 60;

    public function handle(): void
    {
        // Archive logs older than 1 year
        $oneYearAgo = now()->subYear();

        $deletedCount = AccessLog::where('created_at', '<', $oneYearAgo)
            ->delete();

        Log::info('ArchiveAccessLogsJob: Deleted old access logs', [
            'deleted_count' => $deletedCount,
            'cutoff_date' => $oneYearAgo->toDateString(),
        ]);
    }
}
