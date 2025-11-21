<?php

namespace App\Services\DeviceServices;

class CameraService extends BaseDeviceService
{
    public function __construct(string $deviceId, int $port = 9201)
    {
        parent::__construct($deviceId, $port);
    }

    protected function getServiceName(): string
    {
        return 'Camera';
    }

    /**
     * Získat informace o zařízení
     */
    public function getDeviceInfo(): array
    {
        return $this->makeRequest('GET', '/device-info');
    }

    /**
     * Získat status kamery
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
     * Získat snapshot (obrázek)
     * Vrací raw binary data (JPEG)
     */
    public function getSnapshot(int $width = 1920, int $height = 1080): ?string
    {
        $result = $this->makeRequest('GET', '/snapshot', [
            'width' => $width,
            'height' => $height
        ]);
        
        // Pro binary response vracíme raw data místo JSON
        return !empty($result) ? $result : null;
    }

    /**
     * Získat URL pro MJPEG stream
     */
    public function getMjpegStreamUrl(): string
    {
        return "{$this->baseUrl}/stream";
    }

    /**
     * Získat RTSP stream info
     */
    public function getRtspInfo(): array
    {
        return $this->makeRequest('GET', '/rtsp');
    }

    /**
     * Získat ONVIF capabilities
     */
    public function getOnvifCapabilities(): array
    {
        return $this->makeRequest('GET', '/onvif');
    }

    /**
     * Získat nastavení kamery
     */
    public function getSettings(): array
    {
        return $this->makeRequest('GET', '/settings');
    }

    /**
     * Nastavit konfiguraci kamery
     */
    public function updateSettings(array $settings): array
    {
        return $this->makeRequest('POST', '/settings', $settings);
    }

    /**
     * Ovládání IR (noční vidění)
     */
    public function setIr(string $mode): array
    {
        return $this->makeRequest('POST', '/control/ir', [
            'mode' => $mode // 'auto', 'on', 'off', 'schedule'
        ]);
    }

    /**
     * Spustit nahrávání
     */
    public function startRecording(): array
    {
        return $this->makeRequest('POST', '/recording/start');
    }

    /**
     * Zastavit nahrávání
     */
    public function stopRecording(): array
    {
        return $this->makeRequest('POST', '/recording/stop');
    }

    /**
     * Získat log nahrávání
     */
    public function getRecordingLog(): array
    {
        return $this->makeRequest('GET', '/recording/log');
    }

    /**
     * Získat motion detection nastavení
     */
    public function getMotionDetection(): array
    {
        return $this->makeRequest('GET', '/analytics/motion');
    }

    /**
     * Nastavit motion detection
     */
    public function setMotionDetection(bool $enabled, int $sensitivity = 50): array
    {
        return $this->makeRequest('POST', '/analytics/motion', [
            'enabled' => $enabled,
            'sensitivity' => $sensitivity
        ]);
    }

    /**
     * Získat statistiky analytics
     */
    public function getAnalyticsStats(): array
    {
        return $this->makeRequest('GET', '/analytics/stats');
    }

    /**
     * Získat state log
     */
    public function getStateLog(int $limit = 100): array
    {
        return $this->makeRequest('GET', '/state-log', ['limit' => $limit]);
    }

    /**
     * Simulovat událost
     */
    public function simulate(string $action): array
    {
        return $this->makeRequest('POST', '/simulate', [
            'action' => $action // 'motion', 'offline', 'online', 'tampering', etc.
        ]);
    }

    /**
     * Získat WebSocket URL
     */
    public function getWebSocketUrl(): string
    {
        return str_replace('http://', 'ws://', $this->baseUrl);
    }
}
