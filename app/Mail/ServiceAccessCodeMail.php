<?php

namespace App\Mail;

use App\Models\ServiceAccess;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceAccessCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ServiceAccess $serviceAccess,
        public ?string $qrImagePath = null,
    ) {
    }

    public function envelope(): Envelope
    {
        $typeNames = [
            'cleaning' => 'Čištění',
            'maintenance' => 'Údržba',
            'admin' => 'Admin',
        ];
        
        $typeName = $typeNames[$this->serviceAccess->access_type] ?? $this->serviceAccess->access_type;

        return new Envelope(
            subject: "Váš přístupový kód - {$typeName}",
        );
    }

    public function content(): Content
    {
        $accessType = [
            'cleaning' => 'čistění',
            'maintenance' => 'údržbu',
            'admin' => 'administraci',
        ][$this->serviceAccess->access_type] ?? $this->serviceAccess->access_type;

        $allowedRooms = $this->serviceAccess->unlimited_access 
            ? null 
            : $this->serviceAccess->getAllowedRoomsWithNames();

        return new Content(
            view: 'emails.service-access-code',
            with: [
                'serviceAccess' => $this->serviceAccess,
                'user' => $this->serviceAccess->user,
                'accessType' => $accessType,
                'allowedRooms' => $allowedRooms,
                'isActive' => $this->serviceAccess->isValid(),
            ],
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        if ($this->qrImagePath && file_exists(storage_path("app/{$this->qrImagePath}"))) {
            $attachments[] = Attachment::fromPath(storage_path("app/{$this->qrImagePath}"))
                ->as('access-code.png')
                ->withMime('image/png');
        }

        return $attachments;
    }
}
