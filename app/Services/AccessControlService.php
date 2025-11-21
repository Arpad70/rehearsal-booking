<?php

namespace App\Services;

use App\Models\AccessLog;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Services\DeviceServices\QRReaderService;
use App\Services\DeviceServices\KeypadService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AccessControlService
{
    /**
     * Ověřit QR kód a autorizovat přístup
     */
    public function authorizeQRAccess(string $qrCode, string $deviceId, string $scanId): array
    {
        try {
            // Parsovat QR kód - formát: "RESERVATION_{id}_{user_id}_{room_id}"
            $parts = explode('_', $qrCode);
            
            if (count($parts) < 4 || $parts[0] !== 'RESERVATION') {
                return $this->denyAccess($scanId, $deviceId, 'Neplatný formát QR kódu');
            }

            $reservationId = (int) $parts[1];
            $userId = (int) $parts[2];
            $roomId = (int) $parts[3];

            // Ověřit existenci rezervace
            $reservation = Reservation::with(['user', 'room'])
                ->where('id', $reservationId)
                ->where('user_id', $userId)
                ->where('room_id', $roomId)
                ->first();

            if (!$reservation) {
                return $this->denyAccess($scanId, $deviceId, 'Rezervace nenalezena');
            }

            // Ověřit časové okno rezervace (15 minut před - 15 minut po)
            $now = Carbon::now();
            $startWithBuffer = $reservation->start_at->copy()->subMinutes(15);
            $endWithBuffer = $reservation->end_at->copy()->addMinutes(15);

            if (!$now->between($startWithBuffer, $endWithBuffer)) {
                return $this->denyAccess(
                    $scanId, 
                    $deviceId, 
                    'Rezervace je mimo časové okno',
                    $reservation
                );
            }

            // Ověřit, že není zrušená
            if ($reservation->status === 'cancelled') {
                return $this->denyAccess($scanId, $deviceId, 'Rezervace je zrušená', $reservation);
            }

            // Přístup povolen
            return $this->grantAccess($scanId, $deviceId, $reservation);

        } catch (\Exception $e) {
            Log::error("QR Access authorization error: {$e->getMessage()}");
            return $this->denyAccess($scanId, $deviceId, 'Systémová chyba');
        }
    }

    /**
     * Ověřit RFID kartu nebo PIN kód
     */
    public function authorizeRFIDAccess(string $cardId, string $deviceId, string $scanId, ?string $pin = null): array
    {
        try {
            // Najít uživatele podle RFID
            $user = User::where('rfid_card_id', $cardId)->first();

            if (!$user) {
                return $this->denyAccess($scanId, $deviceId, 'RFID karta neregistrována');
            }

            // Pokud je PIN vyžadován, ověřit
            if ($pin !== null && !password_verify($pin, $user->pin_hash ?? '')) {
                return $this->denyAccess($scanId, $deviceId, 'Nesprávný PIN', null, $user);
            }

            // Najít místnost podle device ID
            $room = $this->getRoomByDeviceId($deviceId);
            
            if (!$room) {
                return $this->denyAccess($scanId, $deviceId, 'Místnost nenalezena', null, $user);
            }

            // Najít aktivní rezervaci uživatele pro tuto místnost
            $now = Carbon::now();
            $reservation = Reservation::where('user_id', $user->id)
                ->where('room_id', $room->id)
                ->where('start_at', '<=', $now->copy()->addMinutes(15))
                ->where('end_at', '>=', $now->copy()->subMinutes(15))
                ->where('status', '!=', 'cancelled')
                ->first();

            if (!$reservation) {
                // Admin může vstoupit kdykoliv
                if ($user->role === 'admin') {
                    return $this->grantAdminAccess($scanId, $deviceId, $user, $room);
                }

                return $this->denyAccess($scanId, $deviceId, 'Nemáte aktivní rezervaci', null, $user);
            }

            return $this->grantAccess($scanId, $deviceId, $reservation);

        } catch (\Exception $e) {
            Log::error("RFID Access authorization error: {$e->getMessage()}");
            return $this->denyAccess($scanId, $deviceId, 'Systémová chyba');
        }
    }

    /**
     * Povolit přístup
     */
    private function grantAccess(string $scanId, string $deviceId, Reservation $reservation): array
    {
        // Zalogovat přístup
        AccessLog::create([
            'user_id' => $reservation->user_id,
            'room_id' => $reservation->room_id,
            'reservation_id' => $reservation->id,
            'access_type' => 'entry',
            'access_method' => $this->getAccessMethod($deviceId),
            'device_id' => $deviceId,
            'scan_id' => $scanId,
            'access_granted' => true,
            'action' => 'grant',
            'result' => 'granted',
        ]);

        // Aktualizovat status rezervace na "active"
        $reservation->update(['status' => 'active']);

        // Odemknout dveře přes device service
        $this->unlockDoor($deviceId);

        // Zapnout světla přes Shelly
        $this->turnOnLights($reservation->room_id);

        // Spustit nahrávání na kameře (pokud je)
        $this->startCameraRecording($reservation->room_id);

        return [
            'granted' => true,
            'message' => "Vítejte, {$reservation->user->name}!",
            'userName' => $reservation->user->name,
            'roomName' => $reservation->room->name,
            'reservationEnd' => $reservation->end_at->format('H:i'),
        ];
    }

    /**
     * Povolit admin přístup
     */
    private function grantAdminAccess(string $scanId, string $deviceId, User $user, Room $room): array
    {
        AccessLog::create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'access_type' => 'admin',
            'access_method' => $this->getAccessMethod($deviceId),
            'device_id' => $deviceId,
            'scan_id' => $scanId,
            'access_granted' => true,
            'action' => 'admin_grant',
            'result' => 'granted_admin',
        ]);

        $this->unlockDoor($deviceId);
        $this->turnOnLights($room->id);

        return [
            'granted' => true,
            'message' => "Vítejte, {$user->name} (Admin)",
            'userName' => $user->name,
            'roomName' => $room->name,
        ];
    }

    /**
     * Zamítnout přístup
     */
    private function denyAccess(
        string $scanId, 
        string $deviceId, 
        string $reason,
        ?Reservation $reservation = null,
        ?User $user = null
    ): array {
        AccessLog::create([
            'user_id' => $user?->id ?? $reservation?->user_id,
            'room_id' => $reservation?->room_id ?? $this->getRoomByDeviceId($deviceId)?->id,
            'reservation_id' => $reservation?->id,
            'access_type' => 'entry',
            'access_method' => $this->getAccessMethod($deviceId),
            'device_id' => $deviceId,
            'scan_id' => $scanId,
            'access_granted' => false,
            'failure_reason' => $reason,
            'action' => 'deny',
            'result' => 'denied',
        ]);

        // Signalizovat zamítnutí na zařízení
        $this->signalDenied($deviceId);

        return [
            'granted' => false,
            'message' => $reason,
        ];
    }

    /**
     * Odemknout dveře
     */
    private function unlockDoor(string $deviceId): void
    {
        try {
            $port = $this->getDevicePort($deviceId);
            $type = $this->getDeviceType($deviceId);

            if ($type === 'qr_reader') {
                $service = new QRReaderService($deviceId, $port);
                $service->unlockDoor(5000); // 5 sekund
                $service->setLed('green', 'solid', 3000);
                $service->setBuzzer('success');
            } elseif ($type === 'keypad') {
                $service = new KeypadService($deviceId, $port);
                $service->unlockDoor(5000);
                $service->setLed('green', 'solid', 3000);
                $service->setBuzzer('success');
            }
        } catch (\Exception $e) {
            Log::error("Unlock door error: {$e->getMessage()}");
        }
    }

    /**
     * Signalizovat zamítnutí
     */
    private function signalDenied(string $deviceId): void
    {
        try {
            $port = $this->getDevicePort($deviceId);
            $type = $this->getDeviceType($deviceId);

            if ($type === 'qr_reader') {
                $service = new QRReaderService($deviceId, $port);
                $service->setLed('red', 'blink', 3000);
                $service->setBuzzer('error');
            } elseif ($type === 'keypad') {
                $service = new KeypadService($deviceId, $port);
                $service->setLed('red', 'blink', 3000);
                $service->setBuzzer('error');
            }
        } catch (\Exception $e) {
            Log::error("Signal denied error: {$e->getMessage()}");
        }
    }

    /**
     * Zapnout světla
     */
    private function turnOnLights(int $roomId): void
    {
        try {
            $room = Room::find($roomId);
            if (!$room || !$room->shelly_device_id) {
                return;
            }

            $port = 9500 + $roomId; // Shelly porty 9501-9506
            $shellyService = new \App\Services\DeviceServices\ShellyService($room->shelly_device_id, $port);
            $shellyService->turnLightsOn();
        } catch (\Exception $e) {
            Log::error("Turn on lights error: {$e->getMessage()}");
        }
    }

    /**
     * Spustit nahrávání
     */
    private function startCameraRecording(int $roomId): void
    {
        try {
            $room = Room::find($roomId);
            if (!$room || !$room->camera_device_id) {
                return;
            }

            $port = 9200 + $roomId; // Camera porty 9201-9206
            $cameraService = new \App\Services\DeviceServices\CameraService($room->camera_device_id, $port);
            $cameraService->startRecording();
        } catch (\Exception $e) {
            Log::error("Start camera recording error: {$e->getMessage()}");
        }
    }

    /**
     * Získat místnost podle device ID
     */
    private function getRoomByDeviceId(string $deviceId): ?Room
    {
        return Room::where('qr_reader_device_id', $deviceId)
            ->orWhere('keypad_device_id', $deviceId)
            ->first();
    }

    /**
     * Získat port zařízení
     */
    private function getDevicePort(string $deviceId): int
    {
        // QR readers: 9101-9110, Keypads: 9401-9410
        if (str_starts_with($deviceId, 'qr-reader-')) {
            $num = (int) str_replace('qr-reader-', '', $deviceId);
            return 9100 + $num;
        }
        if (str_starts_with($deviceId, 'keypad-')) {
            $num = (int) str_replace('keypad-', '', $deviceId);
            return 9400 + $num;
        }
        return 9101;
    }

    /**
     * Získat typ zařízení
     */
    private function getDeviceType(string $deviceId): string
    {
        if (str_starts_with($deviceId, 'qr-reader-')) {
            return 'qr_reader';
        }
        if (str_starts_with($deviceId, 'keypad-')) {
            return 'keypad';
        }
        return 'unknown';
    }

    /**
     * Získat metodu přístupu
     */
    private function getAccessMethod(string $deviceId): string
    {
        $type = $this->getDeviceType($deviceId);
        return match($type) {
            'qr_reader' => 'qr_code',
            'keypad' => 'rfid_card',
            default => 'unknown',
        };
    }
}
