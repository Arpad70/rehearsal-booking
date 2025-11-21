<?php

namespace App\Services\DeviceServices;

use Illuminate\Support\Facades\Log;
use Exception;

class ShellyService extends BaseDeviceService
{
    protected function getServiceName(): string
    {
        return 'Shelly Pro EM';
    }

    /**
     * Získat základní informace
     */
    public function getInfo(): array
    {
        return $this->makeRequest('GET', $this->baseUrl, []);
    }

    /**
     * Získat status všech kanálů
     */
    public function getStatus(): array
    {
        return $this->makeRequest('GET', "{$this->baseUrl}/status", []);
    }

    /**
     * Ovládání relé (světla) - Kanál 0
     */
    public function setRelay(int $channel, string $action, int $timer = 0): array
    {
        $params = ['turn' => $action]; // 'on', 'off', 'toggle'
        if ($timer > 0) {
            $params['timer'] = $timer;
        }
        
        // Using query parameters with GET (Shelly API style)
        $url = "{$this->baseUrl}/relay/{$channel}?" . http_build_query($params);
        return $this->makeRequest('GET', $url, []);
    }

    /**
     * Zapnout světla (Kanál 0)
     */
    public function turnLightsOn(int $duration = 0): array
    {
        return $this->setRelay(0, 'on', $duration);
    }

    /**
     * Vypnout světla (Kanál 0)
     */
    public function turnLightsOff(): array
    {
        return $this->setRelay(0, 'off');
    }

    /**
     * Toggle světla (Kanál 0)
     */
    public function toggleLights(): array
    {
        return $this->setRelay(0, 'toggle');
    }

    /**
     * Získat status switche (Gen2 RPC API)
     */
    public function getSwitchStatus(int $id = 0): array
    {
        $url = "{$this->baseUrl}/rpc/Switch.GetStatus?id={$id}";
        return $this->makeRequest('GET', $url, []);
    }

    /**
     * Získat status EM kanálu (Gen2 RPC API)
     */
    public function getEmStatus(int $id = 0): array
    {
        $url = "{$this->baseUrl}/rpc/EM1.GetStatus?id={$id}";
        return $this->makeRequest('GET', $url, []);
    }

    /**
     * Získat data měření z konkrétního kanálu
     */
    public function getMeterData(int $channel): array
    {
        return $this->makeRequest('GET', "{$this->baseUrl}/meter/{$channel}", []);
    }

    /**
     * Získat spotřebu světel (Kanál 0)
     */
    public function getLightsPower(): array
    {
        return $this->getEmStatus(0);
    }

    /**
     * Získat spotřebu zásuvek (Kanál 1)
     */
    public function getOutletsPower(): array
    {
        return $this->getEmStatus(1);
    }

    /**
     * Získat celkovou spotřebu
     */
    public function getTotalPower(): array
    {
        try {
            $status = $this->getStatus();
            if (!isset($status['em1'])) {
                return [];
            }

            $channel0 = $status['em1'][0] ?? [];
            $channel1 = $status['em1'][1] ?? [];

            return [
                'lights' => [
                    'power' => $channel0['power'] ?? 0,
                    'voltage' => $channel0['voltage'] ?? 0,
                    'current' => $channel0['current'] ?? 0,
                    'total' => $channel0['total'] ?? 0,
                ],
                'outlets' => [
                    'power' => $channel1['power'] ?? 0,
                    'voltage' => $channel1['voltage'] ?? 0,
                    'current' => $channel1['current'] ?? 0,
                    'total' => $channel1['total'] ?? 0,
                ],
                'total_power' => ($channel0['power'] ?? 0) + ($channel1['power'] ?? 0),
                'total_energy' => ($channel0['total'] ?? 0) + ($channel1['total'] ?? 0),
            ];
        } catch (Exception $e) {
            Log::error("Shelly total power error: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Získat WebSocket URL
     */
    public function getWebSocketUrl(): string
    {
        return str_replace('http://', 'ws://', $this->baseUrl);
    }

    /**
     * Vypočítat náklady na elektřinu
     */
    public function calculateCost(float $totalKwh, float $pricePerKwh = 5.5): float
    {
        return round($totalKwh * $pricePerKwh, 2);
    }
}
