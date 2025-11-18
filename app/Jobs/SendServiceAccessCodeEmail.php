<?php

namespace App\Jobs;

use App\Mail\ServiceAccessCodeMail;
use App\Models\ServiceAccess;
use App\Services\QRCodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendServiceAccessCodeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ServiceAccess $serviceAccess,
    ) {
        $this->onQueue('emails');
        $this->tries = 3;
        $this->maxExceptions = 1;
    }

    public function handle(QRCodeService $qrService): void
    {
        try {
            // Vygenerovat QR kód pro servisní přístup
            $qrData = [
                'type' => 'service',
                'code' => $this->serviceAccess->access_code,
                'user_id' => $this->serviceAccess->user_id,
                'access_type' => $this->serviceAccess->access_type,
                'timestamp' => now()->unix(),
            ];

            $qrImagePath = $qrService->generateQRImageFromData(
                json_encode($qrData),
                "service-access-{$this->serviceAccess->id}"
            );

            // Odeslat email
            Mail::send(
                new ServiceAccessCodeMail(
                    $this->serviceAccess,
                    $qrImagePath,
                )
            );

            Log::info("Service access code email sent for {$this->serviceAccess->access_code}");

        } catch (\Exception $e) {
            Log::error("Failed to send service access email for {$this->serviceAccess->access_code}: {$e->getMessage()}");
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::critical("SendServiceAccessCodeEmail failed for {$this->serviceAccess->access_code}", [
            'exception' => $exception->getMessage(),
        ]);
    }
}
