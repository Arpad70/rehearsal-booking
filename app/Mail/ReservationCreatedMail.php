<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReservationCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public \App\Models\Reservation $reservation;

    public function __construct(\App\Models\Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    /**
     * Get the message content definition.
     */
    public function content(): \Illuminate\Mail\Mailables\Content
    {
        // Pre-render both HTML and plain-text views to native strings so
        // Symfony's Mime components receive plain strings (not HtmlString).
        $html = view('emails.reservation-created', [
            'reservation' => $this->reservation,
        ])->render();

        $text = view('emails.reservation-created-text', [
            'reservation' => $this->reservation,
        ])->render();

        // Provide the text as a Closure so the mailer treats it as renderable
        // content (not a view name). The Closure will receive the view data
        // when the mailer renders the message.
    $textClosure = fn ($data = []) => new \Illuminate\Support\HtmlString($text);

        $content = new \Illuminate\Mail\Mailables\Content();
        $content->htmlString = $html;
        $content->text = $textClosure; // Closure used so mailer won't treat as view name
        $content->with = [
            'reservation' => $this->reservation,
        ];

        return $content;
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<\Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        // Ensure the generator result is cast to a native string. The QR library
        // may return an HtmlString instance; casting prevents passing HtmlString
        // into Symfony's DataPart which expects a string/resource/File.
        $qr = (string) QrCode::format('png')->size(300)->generate($this->reservation->access_token);
        
        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(
                fn () => $qr,
                'qr.png'
            )->withMime('image/png'),
        ];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): \Illuminate\Mail\Mailables\Envelope
    {
        return new \Illuminate\Mail\Mailables\Envelope(
            subject: 'Potvrzení rezervace',
        );
    }
}