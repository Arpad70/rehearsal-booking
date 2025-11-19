<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Reservation;
use App\Services\ShellyGen2Service;
use Throwable;

class TurnOffShellyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(public Reservation $reservation) {}

    public function handle(): void
    {
        $reservation = $this->reservation;
        $room = $reservation->room;

        if (!$room) {
            Log::warning('TurnOffShellyJob: Reservation has no associated room', [
                'reservation_id' => $reservation->id,
                'room_id' => $reservation->room_id,
            ]);
            return;
        }

        // Get device associated with room
        $device = $room->devices()->where('type', 'shelly')->first();

        if (!$device) {
            Log::warning('TurnOffShellyJob: Room has no Shelly device configured', [
                'reservation_id' => $reservation->id,
                'room_id' => $room->id,
            ]);
            return;
        }

        try {
            $shellyService = new ShellyGen2Service();

            // Check if device is reachable
            if (!$shellyService->isReachable($device)) {
                throw new \Exception('Shelly device is not reachable at ' . $device->ip);
            }

            // Get channel from device metadata
            $channel = $device->meta['channel'] ?? 0;

            // Turn off the relay
            if ($shellyService->turnOff($device, $channel)) {
                Log::info('TurnOffShellyJob: Successfully turned off relay', [
                    'reservation_id' => $reservation->id,
                    'room_id' => $room->id,
                    'device_ip' => $device->ip,
                    'channel' => $channel,
                ]);
            } else {
                throw new \Exception('Failed to turn off relay');
            }
        } catch (Throwable $e) {
            Log::error('TurnOffShellyJob error: ' . $e->getMessage(), [
                'reservation_id' => $reservation->id,
                'room_id' => $room->id,
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        $reservationId = $this->reservation->id;
        $message = "TurnOffShellyJob failed for reservation_id={$reservationId}: " . $exception->getMessage();
        Log::error($message, ['exception' => $exception]);

        $notify = env('SHELLY_FAILURE_NOTIFY_EMAIL');
        if ($notify) {
            try {
                Mail::raw($message, function ($m) use ($notify) {
                    $m->to($notify)->subject('Shelly turn-off job failure');
                });
            } catch (Throwable $e) {
                Log::error('Failed to send Shelly failure notification: ' . $e->getMessage());
            }
        }
    }
}
