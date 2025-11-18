<?php

namespace App\Services;

use App\Models\RoomReader;
use App\Models\GlobalReader;
use App\Models\ReaderAlert;
use Illuminate\Support\Carbon;

class ReaderMonitoringService
{
    /**
     * Check all readers for issues and create alerts
     */
    public function checkAllReaders(): void
    {
        // Check room readers
        RoomReader::where('enabled', true)->each(fn($reader) => $this->checkRoomReader($reader));

        // Check global readers
        GlobalReader::where('enabled', true)->each(fn($reader) => $this->checkGlobalReader($reader));
    }

    /**
     * Check individual room reader health
     */
    public function checkRoomReader(RoomReader $reader): void
    {
        // Clear old alerts for this reader
        ReaderAlert::where('alertable_type', RoomReader::class)
            ->where('alertable_id', $reader->id)
            ->whereNull('resolved_at')
            ->where('alert_type', '!=', 'offline')
            ->delete();

        // Test connection
        $connectionResult = $reader->testConnection();
        if (!$connectionResult['success']) {
            $this->createAlert($reader, 'offline', 'Reader is offline and unreachable', 'critical');
            return;
        }

        // Check for high failure rate
        $failureRate = $this->getFailureRate($reader, 'room');
        if ($failureRate > 10) {
            $this->createAlert(
                $reader,
                'high_failure_rate',
                "High failure rate detected: {$failureRate}% (last 24h)",
                'warning',
                ['failure_rate' => $failureRate]
            );
        }

        // Check for inactivity
        $lastAccess = $this->getLastAccessTime($reader, 'room');
        if ($lastAccess && $lastAccess->diffInHours() > 12) {
            $this->createAlert(
                $reader,
                'no_activity',
                "No activity for {$lastAccess->diffInHours()} hours",
                'info',
                ['last_activity' => $lastAccess->toDateTimeString()]
            );
        }
    }

    /**
     * Check individual global reader health
     */
    public function checkGlobalReader(GlobalReader $reader): void
    {
        // Clear old alerts
        ReaderAlert::where('alertable_type', GlobalReader::class)
            ->where('alertable_id', $reader->id)
            ->whereNull('resolved_at')
            ->where('alert_type', '!=', 'offline')
            ->delete();

        // Test connection
        $connectionResult = $reader->testConnection();
        if (!$connectionResult['success']) {
            $this->createAlert($reader, 'offline', 'Global reader is offline', 'critical');
            return;
        }

        // Check failure rate
        $failureRate = $this->getFailureRate($reader, 'global');
        if ($failureRate > 15) {
            $this->createAlert(
                $reader,
                'high_failure_rate',
                "High failure rate: {$failureRate}%",
                'warning',
                ['failure_rate' => $failureRate]
            );
        }
    }

    /**
     * Get failure rate for reader (last 24h)
     */
    private function getFailureRate($reader, string $type): float
    {
        $total = \App\Models\AccessLog::where('reader_type', $type)
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        if ($total === 0) {
            return 0;
        }

        $successful = \App\Models\AccessLog::where('reader_type', $type)
            ->where('access_granted', true)
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        return round((($ total - $successful) / $total) * 100, 2);
    }

    /**
     * Get last access time for reader
     */
    private function getLastAccessTime($reader, string $type): ?Carbon
    {
        $log = \App\Models\AccessLog::where('reader_type', $type)
            ->orderBy('created_at', 'desc')
            ->first();

        return $log?->created_at;
    }

    /**
     * Create alert for reader
     */
    private function createAlert($reader, string $type, string $message, string $severity = 'warning', array $metadata = []): void
    {
        // Check if alert already exists (to avoid duplicates)
        $existing = ReaderAlert::where('alertable_type', get_class($reader))
            ->where('alertable_id', $reader->id)
            ->where('alert_type', $type)
            ->whereNull('resolved_at')
            ->first();

        if ($existing) {
            return; // Alert already exists
        }

        ReaderAlert::create([
            'alertable_type' => get_class($reader),
            'alertable_id' => $reader->id,
            'alert_type' => $type,
            'message' => $message,
            'severity' => $severity,
            'metadata' => $metadata,
        ]);
    }
}
