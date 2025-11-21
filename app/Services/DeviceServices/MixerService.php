<?php

namespace App\Services\DeviceServices;

use Exception;

class MixerService extends BaseDeviceService
{
    public function __construct(string $deviceId, int $port = 9800)
    {
        parent::__construct($deviceId, $port);
    }

    protected function getServiceName(): string
    {
        return 'Mixer';
    }

    /**
     * Přepsání healthCheck() pro Mixer API
     * Mixer používá /api/info místo /device-info
     */
    public function healthCheck(): array
    {
        if ($this->isCircuitOpen()) {
            return [
                'status' => 'offline',
                'message' => 'Circuit breaker is open',
                'details' => [
                    'circuit_state' => 'open',
                    'service' => $this->getServiceName()
                ]
            ];
        }

        $startTime = microtime(true);

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(config('devices.timeout', 5))
                ->get("{$this->baseUrl}/api/info");

            $responseTime = (int) ((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $this->recordSuccess();
                return [
                    'status' => 'online',
                    'response_time_ms' => $responseTime,
                    'details' => $response->json()
                ];
            }

            $this->recordFailure();
            return [
                'status' => 'offline',
                'response_time_ms' => $responseTime,
                'details' => [
                    'status_code' => $response->status(),
                    'message' => 'HTTP request failed'
                ]
            ];
        } catch (\Exception $e) {
            $this->recordFailure();
            return [
                'status' => 'error',
                'details' => [
                    'message' => $e->getMessage(),
                    'service' => $this->getServiceName()
                ]
            ];
        }
    }

    /**
     * Získat aktuální stav mixeru
     */
    public function getState(): array
    {
        return $this->makeRequest('GET', '/api/state');
    }

    /**
     * Získat informace o zařízení
     */
    public function getInfo(): array
    {
        return $this->makeRequest('GET', '/api/info');
    }

    /**
     * Získat seznam všech kanálů
     */
    public function getChannels(): array
    {
        return $this->makeRequest('GET', '/api/channels');
    }

    /**
     * Získat konfiguraci konkrétního kanálu
     */
    public function getChannel(int $channelId): array
    {
        return $this->makeRequest('GET', "/api/channel/{$channelId}");
    }

    /**
     * Nastavit parametry kanálu
     */
    public function updateChannel(int $channelId, array $params): array
    {
        return $this->makeRequest('POST', "/api/channel/{$channelId}", $params);
    }

    /**
     * Získat seznam scén
     */
    public function getScenes(): array
    {
        return $this->makeRequest('GET', '/api/scenes');
    }

    /**
     * Uložit aktuální nastavení jako scénu
     */
    public function saveScene(string $name, string $description = ''): array
    {
        return $this->makeRequest('POST', '/api/scenes/save', [
            'name' => $name,
            'description' => $description
        ]);
    }

    /**
     * Načíst scénu (Cue Recall)
     */
    public function loadScene(string $name): array
    {
        return $this->makeRequest('POST', "/api/scenes/load/{$name}");
    }

    /**
     * Smazat scénu
     */
    public function deleteScene(string $name): array
    {
        return $this->makeRequest('DELETE', "/api/scenes/{$name}");
    }

    /**
     * Získat seznam show files
     */
    public function getShows(): array
    {
        return $this->makeRequest('GET', '/api/shows');
    }

    /**
     * Nahrát show file do mixeru
     * Poznámka: Multipart upload vyžaduje speciální handling mimo makeRequest()
     */
    public function uploadShow(string $showFilePath): array
    {
        if (!file_exists($showFilePath)) {
            return ['status' => 'error', 'message' => "Show file not found: {$showFilePath}"];
        }

        // TODO: Implementovat multipart upload support v BaseDeviceService
        // Pro teď používáme přímé volání Http fasády
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(config('devices.timeout', 5))
                ->attach('file', file_get_contents($showFilePath), basename($showFilePath))
                ->post("{$this->baseUrl}/api/shows/upload");

            return $response->successful() ? $response->json() : ['status' => 'error', 'message' => 'Upload failed'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Načíst show file (aktivovat scény)
     */
    public function loadShow(string $showName, bool $loadFirstScene = true): array
    {
        return $this->makeRequest('POST', "/api/shows/load/{$showName}", [
            'loadFirstScene' => $loadFirstScene
        ]);
    }

    /**
     * Stáhnout show file z mixeru
     */
    public function downloadShow(string $showName): ?string
    {
        $result = $this->makeRequest('GET', "/api/shows/download/{$showName}");
        return !empty($result) ? $result : null;
    }

    /**
     * Smazat show file
     */
    public function deleteShow(string $showName): array
    {
        return $this->makeRequest('DELETE', "/api/shows/{$showName}");
    }

    /**
     * Zakázat webový přístup (Backend-only mode)
     */
    public function disableWebAccess(): array
    {
        return $this->makeRequest('POST', '/api/security/web/disable');
    }

    /**
     * Povolit webový přístup
     */
    public function enableWebAccess(string $password = ''): array
    {
        return $this->makeRequest('POST', '/api/security/web/enable', [
            'password' => $password
        ]);
    }

    /**
     * Nastavit heslo pro mixer
     */
    public function setPassword(string $password): array
    {
        return $this->makeRequest('POST', '/api/security/password', [
            'password' => $password
        ]);
    }

    /**
     * Získat WebSocket URL
     */
    public function getWebSocketUrl(): string
    {
        return str_replace('http://', 'ws://', $this->baseUrl);
    }

    /**
     * Vytvořit show file pro kapelu z Laravel dat
     */
    public function createShowFileFromReservation(array $reservationData): string
    {
        $showData = [
            'metadata' => [
                'formatVersion' => '1.0',
                'deviceType' => 'Soundcraft Ui24R',
                'createdAt' => now()->toIso8601String(),
                'bandName' => $reservationData['band_name'] ?? 'Unknown Band',
                'reservationId' => $reservationData['id'] ?? null,
            ],
            'scenes' => $reservationData['scenes'] ?? [
                [
                    'name' => 'Default Setup',
                    'description' => 'Basic scene',
                    'channels' => $this->getDefaultChannelSetup(),
                ]
            ]
        ];

        $filename = "show_" . ($reservationData['id'] ?? time()) . ".json";
        $path = storage_path("app/mixer_shows/{$filename}");
        
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, json_encode($showData, JSON_PRETTY_PRINT));
        
        return $path;
    }

    /**
     * Výchozí nastavení kanálů
     */
    private function getDefaultChannelSetup(): array
    {
        $channels = [];
        for ($i = 1; $i <= 24; $i++) {
            $channels[] = [
                'id' => $i,
                'name' => "CH{$i}",
                'mute' => false,
                'fader' => 0,
                'gain' => 0,
                'phantom' => false,
                'eq' => [
                    'low' => 0,
                    'mid' => 0,
                    'high' => 0
                ]
            ];
        }
        return $channels;
    }
}
