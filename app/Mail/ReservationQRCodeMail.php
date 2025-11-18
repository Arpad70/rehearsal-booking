<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationQRCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation,
        public ?string $qrImagePath = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Váš QR kód k rezervaci - {$this->reservation->room->name}",
        );
    }

    public function content(): Content
    {
        $accessWindow = $this->reservation->getQRAccessWindow();
        
        return new Content(
            view: 'emails.reservation-qr-code',
            with: [
                'reservation' => $this->reservation,
                'room' => $this->reservation->room,
                'user' => $this->reservation->user,
                'accessWindow' => $accessWindow,
                'qrValid' => $this->reservation->isQRValid(),
            ],
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        // Připojit QR kód, pokud existuje
        if ($this->qrImagePath && file_exists(storage_path("app/{$this->qrImagePath}"))) {
            $attachments[] = Attachment::fromPath(storage_path("app/{$this->qrImagePath}"))
                ->as('qr-code.png')
                ->withMime('image/png');
        }

        return $attachments;
    }
}
