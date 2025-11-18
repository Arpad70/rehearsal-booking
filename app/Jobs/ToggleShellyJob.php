<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Room;
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
            if (! $this->validateRoom()) {
                return;
            }

            if ($this->attemptGatewayToggle()) {
                return;
            }

            if ($this->attemptDirectToggle()) {
                return;
            }

            $this->logFailure('all attempts failed');
        } catch (Throwable $e) {
            Log::error('ToggleShellyJob error: '.$e->getMessage(), ['room_id' => $this->room->id]);
            throw $e;
        }
    }

    /**
     * Handle a job failure after all retries are exhausted.
     */
    public function failed(Throwable $exception): void
    {
        $roomId = $this->room?->id;
        $message = "ToggleShellyJob failed for room_id={$roomId}: ". $exception->getMessage();
        Log::error($message, ['exception' => $exception]);

        // Optional: notify admin email (configure SHELLY_FAILURE_NOTIFY_EMAIL in env)
        $notify = env('SHELLY_FAILURE_NOTIFY_EMAIL');
        if ($notify) {
            try {
                Mail::raw($message, function ($m) use ($notify) {
                    $m->to($notify)->subject('Shelly job failure');
                });
            } catch (Throwable $mailEx) {
                Log::error('Failed to send Shelly failure notification: '.$mailEx->getMessage());
            }
        }
    }

    /**
     * Validate that room and Shelly IP are present.
     */
    private function validateRoom(): bool
    {
        if (! $this->room || ! $this->room->shelly_ip) {
            Log::warning('ToggleShellyJob: missing room or shelly_ip', ['room_id' => $this->room?->id]);
            return false;
        }
        return true;
    }

    /**
     * Attempt to toggle device via central gateway (preferred method).
     */
    private function attemptGatewayToggle(): bool
    {
        /** @var ?string */
        $gateway = config('services.shelly.gateway_url');
        if (! $gateway) {
            return false;
        }

        try {
            Http::timeout(5)->post($gateway, [
                'action' => 'toggle',
                'room_id' => $this->room->id,
                'shelly_ip' => $this->room->shelly_ip,
            ]);
            Log::info('ToggleShellyJob: sent toggle to gateway', ['room_id' => $this->room->id]);
            return true;
        } catch (Throwable $e) {
            Log::debug('ToggleShellyJob: gateway attempt failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Attempt to toggle device directly as fallback (best-effort).
     */
    private function attemptDirectToggle(): bool
    {
        $ip = $this->room->shelly_ip;

        // Common Shelly RPC endpoints (may vary by device/firmware)
        $urls = [
            "http://{$ip}/rpc/Switch.Toggle",
            "http://{$ip}/relay/0/toggle",
            "http://{$ip}/relay/0",
        ];

        foreach ($urls as $url) {
            if ($this->tryToggleUrl($url)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Try toggling a single URL endpoint.
     */
    private function tryToggleUrl(string $url): bool
    {
        try {
            $response = Http::timeout(5)->get($url);
            if ($response->successful()) {
                Log::info('ToggleShellyJob: toggled device', ['room_id' => $this->room->id, 'url' => $url]);
                return true;
            }
        } catch (Throwable $e) {
            Log::debug('ToggleShellyJob: URL attempt failed', ['url' => $url, 'error' => $e->getMessage()]);
        }
        return false;
    }

    /**
     * Log a failure and prepare for potential retry.
     */
    private function logFailure(string $reason): void
    {
        Log::warning('ToggleShellyJob: '.$reason, ['room_id' => $this->room->id]);
    }
}
