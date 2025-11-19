<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Room;
use App\Services\ShellyGen2Service;
use Throwable;

class ToggleShellyJob implements ShouldQueue
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

    public function __construct(public Room $room)
    {
    }

    public function handle(): void
    {
        try {
            if (!$this->validateRoom()) {
                return;
            }

            $device = $this->room->devices()->where('type', 'shelly')->first();

            if (!$device) {
                Log::warning('ToggleShellyJob: Room has no Shelly device', ['room_id' => $this->room->id]);
                return;
            }

            $shellyService = new ShellyGen2Service();

            // Check if device is reachable
            if (!$shellyService->isReachable($device)) {
                throw new \Exception('Shelly device is not reachable at ' . $device->ip);
            }

            // Get channel from device metadata
            $channel = $device->meta['channel'] ?? 0;

            // Toggle the relay
            if ($shellyService->toggle($device, $channel)) {
                Log::info('ToggleShellyJob: Successfully toggled relay', [
                    'room_id' => $this->room->id,
                    'device_ip' => $device->ip,
                    'channel' => $channel,
                ]);
            } else {
                throw new \Exception('Failed to toggle relay');
            }
        } catch (Throwable $e) {
            Log::error('ToggleShellyJob error: ' . $e->getMessage(), ['room_id' => $this->room->id]);
            throw $e;
        }
    }

    /**
     * Handle a job failure after all retries are exhausted.
     */
    public function failed(Throwable $exception): void
    {
        $roomId = $this->room?->id;
        $message = "ToggleShellyJob failed for room_id={$roomId}: " . $exception->getMessage();
        Log::error($message, ['exception' => $exception]);

        // Optional: notify admin email (configure SHELLY_FAILURE_NOTIFY_EMAIL in env)
        $notify = env('SHELLY_FAILURE_NOTIFY_EMAIL');
        if ($notify) {
            try {
                Mail::raw($message, function ($m) use ($notify) {
                    $m->to($notify)->subject('Shelly job failure');
                });
            } catch (Throwable $mailEx) {
                Log::error('Failed to send Shelly failure notification: ' . $mailEx->getMessage());
            }
        }
    }

    /**
     * Validate that room exists.
     */
    private function validateRoom(): bool
    {
        if (!$this->room) {
            Log::warning('ToggleShellyJob: missing room', ['room_id' => $this->room?->id]);
            return false;
        }
        return true;
    }
}
