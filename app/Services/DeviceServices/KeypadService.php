<?php

namespace App\Services\DeviceServices;

class KeypadService extends BaseDeviceService
{
    public function __construct(string $deviceId, int $port = 9401)
    {
        parent::__construct($deviceId, $port);
    }

    protected function getServiceName(): string
    {
        return 'Keypad';
    }

    /**
     * Získat informace o zařízení
     */
    public function getDeviceInfo(): array
    {
        return $this->makeRequest('GET', '/device-info');
    }

    /**
     * Získat status
     */
    public function getStatus(): array
    {
        return $this->makeRequest('GET', '/status');
    }

    /**
     * Získat diagnostiku
     */
    public function getDiagnostics(): array
    {
        return $this->makeRequest('GET', '/diagnostics');
    }

    /**
     * Simulovat RFID scan
     */
    public function simulateRfidScan(string $cardId): array
    {
        return $this->makeRequest('POST', '/rfid-scan', [
            'cardId' => $cardId
        ]);
    }

    /**
     * Simulovat PIN entry
     */
    public function simulatePinEntry(string $pin): array
    {
        return $this->makeRequest('POST', '/pin-entry', [
            'pin' => $pin
        ]);
    }

    /**
     * Autorizovat přístup
     */
    public function authorize(string $scanId, bool $granted, string $userName = '', string $message = ''): array
    {
        return $this->makeRequest('POST', '/authorize', [
            'scanId' => $scanId,
            'granted' => $granted,
            'userName' => $userName,
            'message' => $message
        ]);
    }

    /**
     * Ovládání RGB LED
     */
    public function setLed(string $color, string $mode = 'solid', int $duration = 3000): array
    {
        return $this->makeRequest('POST', '/control/led', [
            'color' => $color, // 'red', 'green', 'blue', 'yellow', 'off'
            'mode' => $mode,   // 'solid', 'blink', 'pulse', 'rainbow'
            'duration' => $duration
        ]);
    }

    /**
     * Ovládání relé
     */
    public function setRelay(bool $state, int $duration = 5000): array
    {
        return $this->makeRequest('POST', '/control/relay', [
            'state' => $state,
            'duration' => $duration
        ]);
    }

    /**
     * Ovládání bzučáku
     */
    public function setBuzzer(string $pattern, int $duration = 500, int $frequency = 2500): array
    {
        return $this->makeRequest('POST', '/control/buzzer', [
            'pattern' => $pattern, // 'success', 'error', 'warning', 'custom'
            'duration' => $duration,
            'frequency' => $frequency
        ]);
    }

    /**
     * Odemknout dveře
     */
    public function unlockDoor(int $duration = 5000): array
    {
        return $this->setRelay(true, $duration);
    }

    /**
     * Zamknout dveře
     */
    public function lockDoor(): array
    {
        return $this->setRelay(false, 0);
    }

    /**
     * Získat historii
     */
    public function getHistory(int $limit = 50): array
    {
        return $this->makeRequest('GET', '/history', ['limit' => $limit]);
    }

    /**
     * Získat WebSocket URL
     */
    public function getWebSocketUrl(): string
    {
        return str_replace('http://', 'ws://', $this->baseUrl);
    }
}
