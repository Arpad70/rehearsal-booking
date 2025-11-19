<?php

namespace App\Services;

use App\Models\Device;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShellyGen2Service
{
    private string $baseUrl;
    private string $authToken;

    /**
     * Initialize Shelly Gen.2 Service
     * 
     * @param string|null $baseUrl Shelly Gateway URL from config
     * @param string|null $authToken Authentication token for Shelly device
     */
    public function __construct(string $baseUrl = null, string $authToken = null)
    {
        $this->baseUrl = $baseUrl ?? config('services.shelly.gateway_url', env('SHELLY_GATEWAY_URL'));
        $this->authToken = $authToken ?? config('services.shelly.auth_token');
    }

    /**
     * Turn on a relay/output
     * 
     * @param Device $device
     * @param int $channel Channel/relay number (0-based)
     * @return bool
     */
    public function turnOn(Device $device, int $channel = 0): bool
    {
        return $this->setRelay($device, $channel, true);
    }

    /**
     * Turn off a relay/output
     * 
     * @param Device $device
     * @param int $channel Channel/relay number (0-based)
     * @return bool
     */
    public function turnOff(Device $device, int $channel = 0): bool
    {
        return $this->setRelay($device, $channel, false);
    }

    /**
     * Toggle relay state
     * 
     * @param Device $device
     * @param int $channel Channel/relay number (0-based)
     * @return bool
     */
    public function toggle(Device $device, int $channel = 0): bool
    {
        try {
            $status = $this->getStatus($device, $channel);
            if ($status === null) {
                return false;
            }
            
            return $this->setRelay($device, $channel, !$status);
        } catch (\Exception $e) {
            Log::error('Shelly toggle error: ' . $e->getMessage(), [
                'device_id' => $device->id,
                'ip' => $device->ip,
                'channel' => $channel,
            ]);
            return false;
        }
    }

    /**
     * Set relay state (on/off)
     * 
     * @param Device $device
     * @param int $channel Channel/relay number (0-based)
     * @param bool $on
     * @return bool
     */
    private function setRelay(Device $device, int $channel = 0, bool $on = true): bool
    {
        try {
            $endpoint = "http://{$device->ip}/rpc/Switch.Set";
            $params = [
                'id' => $channel,
                'on' => $on,
            ];

            $response = Http::timeout(10)
                ->get($endpoint, $params);

            if ($response->failed()) {
                Log::error('Shelly API error', [
                    'device_id' => $device->id,
                    'ip' => $device->ip,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return false;
            }

            $data = $response->json();
            
            // Shelly returns { "was_on": bool } or error
            if (isset($data['was_on'])) {
                Log::info('Shelly relay updated', [
                    'device_id' => $device->id,
                    'channel' => $channel,
                    'state' => $on ? 'on' : 'off',
                    'previous_state' => $data['was_on'],
                ]);
                return true;
            }

            if (isset($data['error'])) {
                Log::error('Shelly error response', [
                    'device_id' => $device->id,
                    'error' => $data['error'],
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Shelly relay error: ' . $e->getMessage(), [
                'device_id' => $device->id,
                'ip' => $device->ip,
                'channel' => $channel,
            ]);
            return false;
        }
    }

    /**
     * Get current status of a relay/output
     * 
     * @param Device $device
     * @param int $channel Channel/relay number (0-based)
     * @return bool|null True if on, false if off, null if error
     */
    public function getStatus(Device $device, int $channel = 0): ?bool
    {
        try {
            $endpoint = "http://{$device->ip}/rpc/Switch.GetStatus";
            $params = ['id' => $channel];

            $response = Http::timeout(10)
                ->get($endpoint, $params);

            if ($response->failed()) {
                Log::error('Shelly status request failed', [
                    'device_id' => $device->id,
                    'ip' => $device->ip,
                    'status' => $response->status(),
                ]);
                return null;
            }

            $data = $response->json();
            
            if (isset($data['output'])) {
                return (bool) $data['output'];
            }

            if (isset($data['on'])) {
                return (bool) $data['on'];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Shelly status error: ' . $e->getMessage(), [
                'device_id' => $device->id,
                'ip' => $device->ip,
            ]);
            return null;
        }
    }

    /**
     * Get full device information
     * 
     * @param Device $device
     * @return array|null
     */
    public function getInfo(Device $device): ?array
    {
        try {
            $endpoint = "http://{$device->ip}/rpc/Shelly.GetInfo";

            $response = Http::timeout(10)
                ->get($endpoint);

            if ($response->failed()) {
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Shelly info error: ' . $e->getMessage(), [
                'device_id' => $device->id,
                'ip' => $device->ip,
            ]);
            return null;
        }
    }

    /**
     * Get all switch statuses
     * 
     * @param Device $device
     * @return array|null Array of switch statuses indexed by channel
     */
    public function getAllSwitches(Device $device): ?array
    {
        try {
            $endpoint = "http://{$device->ip}/rpc/Switch.GetStatus";

            $switches = [];
            
            // Typically Shelly devices have 1-4 switches
            for ($i = 0; $i < 4; $i++) {
                $response = Http::timeout(10)
                    ->get($endpoint, ['id' => $i]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['output']) || isset($data['on'])) {
                        $switches[$i] = [
                            'on' => $data['output'] ?? $data['on'] ?? false,
                            'power' => $data['power'] ?? null,
                            'energy' => $data['aenergy'] ?? null,
                        ];
                    }
                } else {
                    break; // No more switches
                }
            }

            return !empty($switches) ? $switches : null;
        } catch (\Exception $e) {
            Log::error('Shelly all switches error: ' . $e->getMessage(), [
                'device_id' => $device->id,
                'ip' => $device->ip,
            ]);
            return null;
        }
    }

    /**
     * Check if device is reachable
     * 
     * @param Device $device
     * @return bool
     */
    public function isReachable(Device $device): bool
    {
        try {
            $endpoint = "http://{$device->ip}/rpc/Shelly.GetStatus";

            $response = Http::timeout(5)
                ->get($endpoint);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Reboot the device
     * 
     * @param Device $device
     * @return bool
     */
    public function reboot(Device $device): bool
    {
        try {
            $endpoint = "http://{$device->ip}/rpc/Shelly.Reboot";

            $response = Http::timeout(10)
                ->post($endpoint);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Shelly reboot error: ' . $e->getMessage(), [
                'device_id' => $device->id,
                'ip' => $device->ip,
            ]);
            return false;
        }
    }

    /**
     * Get power consumption data
     * 
     * @param Device $device
     * @param int $channel
     * @return array|null Array with 'power' and 'energy' data
     */
    public function getPowerData(Device $device, int $channel = 0): ?array
    {
        try {
            $endpoint = "http://{$device->ip}/rpc/Switch.GetStatus";
            $params = ['id' => $channel];

            $response = Http::timeout(10)
                ->get($endpoint, $params);

            if ($response->failed()) {
                return null;
            }

            $data = $response->json();
            
            return [
                'power' => $data['power'] ?? null,
                'energy' => $data['aenergy'] ?? null,
                'voltage' => $data['voltage'] ?? null,
                'current' => $data['current'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Shelly power data error: ' . $e->getMessage(), [
                'device_id' => $device->id,
                'ip' => $device->ip,
            ]);
            return null;
        }
    }
}
