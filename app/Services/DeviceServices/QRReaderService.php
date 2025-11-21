<?php

namespace App\Services\DeviceServices;

use Illuminate\Support\Facades\Log;
use Exception;

class QRReaderService extends BaseDeviceService
{
    protected function getServiceName(): string
    {
        return 'QR Reader';
    }

    /**
     * Simulovat scan QR kódu (pro testování)
     */
    public function simulateScan(string $code): array
    {
        Log::info("[{$this->deviceId}] Simulating QR scan: {$code}");
        
        return $this->makeRequest('POST', "{$this->baseUrl}/scan", [
            'code' => $code
        ]);
    }

    /**
     * Autorizovat přístup (backend odpověď po ověření rezervace)
     */
    public function authorize(string $scanId, bool $granted, string $message = '', int $unlockDuration = 5): array
    {
        Log::info("[{$this->deviceId}] Authorizing scan {$scanId}: " . ($granted ? 'GRANTED' : 'DENIED'));
        
        return $this->makeRequest('POST', "{$this->baseUrl}/authorize", [
            'scanId' => $scanId,
            'authorized' => $granted,
            'message' => $message,
            'unlockDuration' => $unlockDuration
        ]);
    }

    /**
     * Ovládání LED diody
     */
    public function setLed(string $color, string $mode = 'solid', int $duration = 3000): array
    {
        return $this->makeRequest('POST', "{$this->baseUrl}/control/led", [
            'color' => $color, // 'red', 'green', 'blue', 'yellow', 'cyan', 'magenta', 'white', 'off'
            'mode' => $mode,   // 'solid', 'blink', 'pulse'
            'duration' => $duration
        ]);
    }

    /**
     * Ovládání bzučáku
     */
    public function setBuzzer(string $pattern, int $duration = 500): array
    {
        return $this->makeRequest('POST', "{$this->baseUrl}/control/buzzer", [
            'pattern' => $pattern, // 'success', 'error', 'warning', 'custom'
            'duration' => $duration
        ]);
    }

    /**
     * Ovládání relé (odemčení/zamčení dveří)
     */
    public function setRelay(bool $state, int $duration = 5000): array
    {
        return $this->makeRequest('POST', "{$this->baseUrl}/control/relay", [
            'state' => $state,
            'duration' => $duration
        ]);
    }

    /**
     * Odemknout dveře
     */
    public function unlockDoor(int $duration = 5000): array
    {
        return $this->makeRequest('POST', "{$this->baseUrl}/door/unlock", [
            'duration' => $duration
        ]);
    }

    /**
     * Zamknout dveře
     */
    public function lockDoor(): array
    {
        return $this->makeRequest('POST', "{$this->baseUrl}/door/lock", []);
    }

    /**
     * Získat stav dveří
     */
    public function getDoorStatus(): array
    {
        return $this->makeRequest('GET', "{$this->baseUrl}/door", []);
    }

    /**
     * Získat historii skenů
     */
    public function getHistory(int $limit = 50): array
    {
        return $this->makeRequest('GET', "{$this->baseUrl}/history", [
            'limit' => $limit
        ]);
    }

    /**
     * Získat access log
     */
    public function getAccessLog(int $limit = 50): array
    {
        return $this->makeRequest('GET', "{$this->baseUrl}/access-log", [
            'limit' => $limit
        ]);
    }

    /**
     * Získat WebSocket URL pro real-time monitoring
     */
    public function getWebSocketUrl(): string
    {
        return str_replace('http://', 'ws://', $this->baseUrl);
    }
}
