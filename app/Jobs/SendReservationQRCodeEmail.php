<?php

namespace App\Jobs;

use App\Mail\ReservationQRCodeMail;
use App\Models\Reservation;
use App\Services\QRCodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendReservationQRCodeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $maxExceptions = 1;

    public function __construct(
        public Reservation $reservation,
    ) {
        $this->onQueue('emails');
    }

    public function handle(QRCodeService $qrService): void
    {
        try {
            // Vygenerovat QR kód, pokud ještě neexistuje
            if (!$this->reservation->qr_code) {
                $qrImagePath = $qrService->generateForReservation($this->reservation);
                $this->reservation->update([
                    'qr_code' => $qrImagePath,
                    'qr_generated_at' => now(),
                ]);
            }

            // Odeslat email pouze pokud máme příjemce
            $recipient = $this->reservation->user->email ?? $this->reservation->email ?? null;
            if ($recipient) {
                Mail::to($recipient)->send(
                    new ReservationQRCodeMail(
                        $this->reservation,
                        $this->reservation->qr_code,
                    )
                );
            } else {
                Log::warning("No email recipient for reservation {$this->reservation->id}, skipping send");
            }

            // Zaznamenat čas odeslání
            $this->reservation->update(['qr_sent_at' => now()]);

            Log::info("QR code email sent for reservation {$this->reservation->id}");

        } catch (\Exception $e) {
            Log::error("Failed to send QR code email for reservation {$this->reservation->id}: {$e->getMessage()}");
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::critical("SendReservationQRCodeEmail failed for reservation {$this->reservation->id}", [
            'exception' => $exception->getMessage(),
        ]);

        // Zde můžete přidat notifikaci admina
    }
}
