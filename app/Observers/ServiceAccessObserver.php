<?php

namespace App\Observers;

use App\Jobs\SendServiceAccessCodeEmail;
use App\Models\ServiceAccess;

class ServiceAccessObserver
{
    /**
     * Handle the ServiceAccess "created" event.
     */
    public function created(ServiceAccess $serviceAccess): void
    {
        // Automaticky odeslat přístupový kód emailem
        SendServiceAccessCodeEmail::dispatch($serviceAccess)->delay(now()->addSeconds(3));
    }

    /**
     * Handle the ServiceAccess "updated" event.
     */
    public function updated(ServiceAccess $serviceAccess): void
    {
        // Pokud byl znovu aktivován, poslat email
        if ($serviceAccess->wasChanged('enabled') && $serviceAccess->enabled && !$serviceAccess->revoked) {
            SendServiceAccessCodeEmail::dispatch($serviceAccess)->delay(now()->addSeconds(3));
        }
    }

    /**
     * Handle the ServiceAccess "deleting" event.
     */
    public function deleting(ServiceAccess $serviceAccess): void
    {
        // Cleanup QR image
        $qrPath = "service-access-{$serviceAccess->id}.png";
        if (file_exists(storage_path("app/qrcodes/{$qrPath}"))) {
            unlink(storage_path("app/qrcodes/{$qrPath}"));
        }
    }
}
