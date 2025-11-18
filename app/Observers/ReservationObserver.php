<?php

namespace App\Observers;

use App\Jobs\SendReservationQRCodeEmail;
use App\Models\Reservation;

class ReservationObserver
{
    /**
     * Handle the Reservation "created" event.
     */
    public function created(Reservation $reservation): void
    {
        // Automaticky zaslat QR email když je rezervace vytvořena
        SendReservationQRCodeEmail::dispatch($reservation)->delay(now()->addSeconds(5));
    }

    /**
     * Handle the Reservation "updated" event.
     */
    public function updated(Reservation $reservation): void
    {
        // Pokud se změnila místnost nebo čas, znovu vytvořit QR
        if ($reservation->isDirty(['room_id', 'start_at', 'end_at'])) {
            $reservation->update([
                'qr_code' => null,
                'qr_generated_at' => null,
                'qr_sent_at' => null,
            ]);

            SendReservationQRCodeEmail::dispatch($reservation)->delay(now()->addSeconds(5));
        }
    }

    /**
     * Handle the Reservation "deleted" event.
     */
    public function deleted(Reservation $reservation): void
    {
        // Cleanup QR image pokud byla rezervace smazána
        if ($reservation->qr_code && file_exists(storage_path("app/{$reservation->qr_code}"))) {
            unlink(storage_path("app/{$reservation->qr_code}"));
        }
    }
}
