<?php

namespace App\Services;

use App\Models\GlobalReader;
use App\Models\Room;
use App\Models\RoomReader;

class DoorLockService
{
    /**
     * Unlock room by room reader
     */
    public function unlockRoom(RoomReader $reader): array
    {
        if (!$reader->enabled) {
            return [
                'success' => false,
                'message' => 'Reader is disabled',
            ];
        }

        $config = $reader->getLockConfig();

        return match ($reader->door_lock_type) {
            'relay' => $this->unlockViaRelay($reader),
            'api' => $this->unlockViaAPI($reader, $config),
            'webhook' => $this->unlockViaWebhook($reader, $config),
            default => [
                'success' => false,
                'message' => 'Unknown lock type: ' . $reader->door_lock_type,
            ],
        };
    }

    /**
     * Unlock global entrance/service
     */
    public function unlockGlobalReader(GlobalReader $reader): array
    {
        if (!$reader->enabled) {
            return [
                'success' => false,
                'message' => 'Global reader is disabled',
            ];
        }

        $config = $reader->getLockConfig();

        return match ($reader->door_lock_type) {
            'relay' => $this->unlockViaRelayGlobal($reader),
            'api' => $this->unlockViaAPIGlobal($reader, $config),
            'webhook' => $this->unlockViaWebhookGlobal($reader, $config),
            default => [
                'success' => false,
                'message' => 'Unknown lock type: ' . $reader->door_lock_type,
            ],
        };
    }

    /**
     * Unlock via Relay (GPIO, Arduino, Shelly)
     */
    private function unlockViaRelay(RoomReader $reader): array
    {
        try {
            $config = $reader->getLockConfig();
            $relayPin = $config['relay_pin'] ?? 1;
            $duration = $config['duration'] ?? 5;

            $url = "http://{$reader->reader_ip}:{$reader->reader_port}/relay/{$relayPin}/on?duration={$duration}";

            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'header' => "Authorization: Bearer {$reader->reader_token}",
                ],
            ]);

            $result = @file_get_contents($url, false, $context);

            if ($result !== false) {
                return [
                    'success' => true,
                    'message' => "Room unlocked ({$duration}s)",
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to communicate with relay',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Relay error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Unlock via Smart Lock API
     */
    private function unlockViaAPI(RoomReader $reader, array $config): array
    {
        try {
            $apiUrl = $config['api_url'] ?? null;
            $apiKey = $config['api_key'] ?? $reader->reader_token;
            $lockId = $config['lock_id'] ?? null;
            $duration = $config['duration'] ?? 5;

            if (!$apiUrl || !$lockId) {
                return [
                    'success' => false,
                    'message' => 'API configuration incomplete',
                ];
            }

            $payload = json_encode([
                'action' => 'unlock',
                'lock_id' => $lockId,
                'duration' => $duration,
            ]);

            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . $apiKey,
                        'Content-Length: ' . strlen($payload),
                    ],
                    'content' => $payload,
                    'timeout' => 5,
                ],
            ]);

            $result = @file_get_contents($apiUrl, false, $context);
            $response = json_decode($result, true);

            if ($response && ($response['success'] ?? false)) {
                return [
                    'success' => true,
                    'message' => 'Room unlocked via API',
                ];
            }

            return [
                'success' => false,
                'message' => 'API unlock failed',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'API error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Unlock via Webhook
     */
    private function unlockViaWebhook(RoomReader $reader, array $config): array
    {
        try {
            $webhookUrl = $config['webhook_url'] ?? null;
            $secret = $config['webhook_secret'] ?? $reader->reader_token;

            if (!$webhookUrl) {
                return [
                    'success' => false,
                    'message' => 'Webhook URL not configured',
                ];
            }

            $payload = json_encode([
                'room_id' => $reader->room_id,
                'reader_id' => $reader->id,
                'action' => 'unlock',
                'timestamp' => time(),
            ]);

            $signature = hash_hmac('sha256', $payload, $secret);

            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => [
                        'Content-Type: application/json',
                        'X-Signature: sha256=' . $signature,
                        'Content-Length: ' . strlen($payload),
                    ],
                    'content' => $payload,
                    'timeout' => 5,
                ],
            ]);

            $result = @file_get_contents($webhookUrl, false, $context);

            if ($result !== false) {
                return [
                    'success' => true,
                    'message' => 'Room unlocked via webhook',
                ];
            }

            return [
                'success' => false,
                'message' => 'Webhook unlock failed',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Webhook error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Unlock global reader via Relay
     */
    private function unlockViaRelayGlobal(GlobalReader $reader): array
    {
        try {
            $config = $reader->getLockConfig();
            $relayPin = $config['relay_pin'] ?? 1;
            $duration = $config['duration'] ?? 10; // Longer for main entrance

            $url = "http://{$reader->reader_ip}:{$reader->reader_port}/relay/{$relayPin}/on?duration={$duration}";

            $context = stream_context_create([
                'http' => [
                    'timeout' => 5,
                    'header' => "Authorization: Bearer {$reader->reader_token}",
                ],
            ]);

            $result = @file_get_contents($url, false, $context);

            if ($result !== false) {
                return [
                    'success' => true,
                    'message' => "Global access unlocked ({$duration}s)",
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to communicate with global relay',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Global relay error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Unlock global reader via API
     */
    private function unlockViaAPIGlobal(GlobalReader $reader, array $config): array
    {
        try {
            $apiUrl = $config['api_url'] ?? null;
            $apiKey = $config['api_key'] ?? $reader->reader_token;
            $lockId = $config['lock_id'] ?? null;
            $duration = $config['duration'] ?? 10;

            if (!$apiUrl || !$lockId) {
                return [
                    'success' => false,
                    'message' => 'Global API configuration incomplete',
                ];
            }

            $payload = json_encode([
                'action' => 'unlock',
                'lock_id' => $lockId,
                'duration' => $duration,
                'location' => $reader->access_type,
            ]);

            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . $apiKey,
                        'Content-Length: ' . strlen($payload),
                    ],
                    'content' => $payload,
                    'timeout' => 5,
                ],
            ]);

            $result = @file_get_contents($apiUrl, false, $context);
            $response = json_decode($result, true);

            if ($response && ($response['success'] ?? false)) {
                return [
                    'success' => true,
                    'message' => 'Global access unlocked via API',
                ];
            }

            return [
                'success' => false,
                'message' => 'Global API unlock failed',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Global API error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Unlock global reader via Webhook
     */
    private function unlockViaWebhookGlobal(GlobalReader $reader, array $config): array
    {
        try {
            $webhookUrl = $config['webhook_url'] ?? null;
            $secret = $config['webhook_secret'] ?? $reader->reader_token;

            if (!$webhookUrl) {
                return [
                    'success' => false,
                    'message' => 'Global webhook URL not configured',
                ];
            }

            $payload = json_encode([
                'global_reader_id' => $reader->id,
                'reader_name' => $reader->reader_name,
                'access_type' => $reader->access_type,
                'action' => 'unlock',
                'timestamp' => time(),
            ]);

            $signature = hash_hmac('sha256', $payload, $secret);

            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => [
                        'Content-Type: application/json',
                        'X-Signature: sha256=' . $signature,
                        'Content-Length: ' . strlen($payload),
                    ],
                    'content' => $payload,
                    'timeout' => 5,
                ],
            ]);

            $result = @file_get_contents($webhookUrl, false, $context);

            if ($result !== false) {
                return [
                    'success' => true,
                    'message' => 'Global access unlocked via webhook',
                ];
            }

            return [
                'success' => false,
                'message' => 'Global webhook unlock failed',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Global webhook error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Test connection to reader
     */
    public function testConnection(RoomReader | GlobalReader $reader): array
    {
        return $reader->testConnection();
    }
}
