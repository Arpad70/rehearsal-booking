<?php  
namespace App\Jobs;  
use Illuminate\Bus\Queueable;  
use Illuminate\Contracts\Queue\ShouldQueue;  
use Illuminate\Queue\InteractsWithQueue;  
use Illuminate\Queue\SerializesModels;  
use Illuminate\Foundation\Bus\Dispatchable;  
use Illuminate\Support\Facades\Http;  
use Illuminate\Support\Facades\Log;  
use Illuminate\Support\Facades\Mail;  
use App\Models\Reservation;  
use Throwable;  

class TurnOnShellyJob implements ShouldQueue {  
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;  
    
    public int $tries = 3;
    public int $backoff = 60;
    
    public function __construct(public Reservation $reservation) {}  
    
    public function handle(): void {  
        $reservation = $this->reservation;
        $room = $reservation->room;  
        
        if (!$room) {
            Log::warning('TurnOnShellyJob: Reservation has no associated room', [
                'reservation_id' => $reservation->id,
                'room_id' => $reservation->room_id,
            ]);
            return;  
        }
        
        if (!$room->shelly_ip) {
            Log::warning('TurnOnShellyJob: Room has no Shelly IP configured', [
                'reservation_id' => $reservation->id,
                'room_id' => $room->id,
            ]);
            return;  
        }
        
        /** @var ?string */
        $gateway = config('services.shelly.gateway_url');  
        
        if (!$gateway) {
            Log::warning('TurnOnShellyJob: No Shelly gateway URL configured', [
                'reservation_id' => $reservation->id,
            ]);
            return;  
        }
        
        try {  
            $response = Http::timeout(5)->post($gateway, [  
                'action' => 'turn_on',  
                'room_id' => $room->id,  
                'reservation_id' => $reservation->id,  
            ]);  
            
            if ($response->successful()) {
                Log::info('TurnOnShellyJob: Successfully sent turn_on request', [
                    'reservation_id' => $reservation->id,
                    'room_id' => $room->id,
                ]);
            } else {
                Log::warning('TurnOnShellyJob: Gateway returned error status', [
                    'reservation_id' => $reservation->id,
                    'room_id' => $room->id,
                    'status' => $response->status(),
                ]);
                throw new \Exception('Gateway returned status ' . $response->status());
            }
        } catch (Throwable $e) {  
            Log::error('TurnOnShellyJob error: ' . $e->getMessage(), [
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
        $message = "TurnOnShellyJob failed for reservation_id={$reservationId}: " . $exception->getMessage();
        Log::error($message, ['exception' => $exception]);

        $notify = env('SHELLY_FAILURE_NOTIFY_EMAIL');
        if ($notify) {
            try {
                Mail::raw($message, function ($m) use ($notify) {
                    $m->to($notify)->subject('Shelly turn-on job failure');
                });
            } catch (Throwable $e) {
                Log::error('Failed to send Shelly failure notification: ' . $e->getMessage());
            }
        }
    }
}