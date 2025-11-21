<?php

namespace App\Services\DeviceServices;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseDeviceService
{
    protected string $deviceId;
    protected string $baseUrl;
    protected int $timeout;
    
    // Circuit Breaker config
    protected int $failureThreshold;
    protected int $recoveryTimeout;
    
    abstract protected function getServiceName(): string;
    
    /**
     * Konstruktor s automatickou detekcí hostu
     */
    public function __construct(string $deviceId, int $port)
    {
        $this->deviceId = $deviceId;
        
        // ✅ OPRAVA: Použít správný host pro Docker
        $host = config('devices.simulator_host', '172.17.0.1');
        
        $this->baseUrl = "http://{$host}:{$port}";
        $this->timeout = (int) config('devices.timeout', 5);
        $this->failureThreshold = (int) config('devices.circuit_breaker.failure_threshold', 3);
        $this->recoveryTimeout = (int) config('devices.circuit_breaker.recovery_timeout', 60);
        
        Log::debug("[{$this->deviceId}] {$this->getServiceName()} initialized with base URL: {$this->baseUrl}");
    }
    
    /**
     * HTTP request s circuit breaker protection
     */
    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $cacheKey = "circuit_breaker:{$this->deviceId}";
        
        // Check circuit breaker state
        if ($this->isCircuitOpen($cacheKey)) {
            Log::warning("[{$this->deviceId}] Circuit breaker OPEN - device unavailable");
            return [
                'status' => 'unavailable',
                'message' => 'Device temporarily unavailable due to repeated failures'
            ];
        }
        
        try {
            $response = match($method) {
                'GET' => Http::timeout($this->timeout)->get($endpoint),
                'POST' => Http::timeout($this->timeout)->post($endpoint, $data),
                'PUT' => Http::timeout($this->timeout)->put($endpoint, $data),
                'DELETE' => Http::timeout($this->timeout)->delete($endpoint),
                default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}")
            };
            
            if ($response->successful()) {
                $this->recordSuccess($cacheKey);
                return $response->json() ?? ['status' => 'ok'];
            }
            
            $this->recordFailure($cacheKey);
            
            Log::warning("[{$this->deviceId}] HTTP {$response->status()}: {$response->body()}");
            
            return [
                'status' => 'error',
                'code' => $response->status(),
                'message' => $response->body()
            ];
            
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("[{$this->deviceId}] Connection failed: {$e->getMessage()}");
            $this->recordFailure($cacheKey);
            
            return [
                'status' => 'connection_error',
                'message' => "Failed to connect to device: {$e->getMessage()}"
            ];
            
        } catch (\Exception $e) {
            Log::error("[{$this->deviceId}] Request error: {$e->getMessage()}");
            $this->recordFailure($cacheKey);
            
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Check if circuit breaker is open
     */
    protected function isCircuitOpen(?string $key = null): bool
    {
        $key = $key ?? "circuit_breaker:{$this->deviceId}";
        $failures = Cache::get("{$key}:failures", 0);
        $lastFailure = Cache::get("{$key}:last_failure");
        
        if ($failures >= $this->failureThreshold) {
            if ($lastFailure && (time() - $lastFailure) < $this->recoveryTimeout) {
                return true; // Circuit still open
            }
            
            // Recovery timeout passed - reset and try again
            Log::info("[{$this->deviceId}] Circuit breaker entering HALF-OPEN state");
            Cache::forget("{$key}:failures");
            Cache::forget("{$key}:last_failure");
        }
        
        return false;
    }
    
    /**
     * Record successful request
     */
    protected function recordSuccess(?string $key = null): void
    {
        $key = $key ?? "circuit_breaker:{$this->deviceId}";
        if (Cache::has("{$key}:failures")) {
            Log::info("[{$this->deviceId}] Circuit breaker CLOSED - device recovered");
        }
        
        Cache::forget("{$key}:failures");
        Cache::forget("{$key}:last_failure");
    }
    
    /**
     * Record failed request
     */
    protected function recordFailure(?string $key = null): void
    {
        $key = $key ?? "circuit_breaker:{$this->deviceId}";
        $failures = Cache::increment("{$key}:failures");
        Cache::put("{$key}:last_failure", time(), $this->recoveryTimeout * 2);
        
        if ($failures >= $this->failureThreshold) {
            Log::alert("[{$this->deviceId}] Circuit breaker OPENED after {$failures} consecutive failures");
        } else {
            Log::warning("[{$this->deviceId}] Failure {$failures}/{$this->failureThreshold}");
        }
    }
    
    /**
     * Health check endpoint
     */
    public function healthCheck(): array
    {
        $start = microtime(true);
        $result = $this->makeRequest('GET', "{$this->baseUrl}/device-info");
        $responseTime = round((microtime(true) - $start) * 1000); // ms
        
        return [
            'device_id' => $this->deviceId,
            'service' => $this->getServiceName(),
            'status' => isset($result['status']) && $result['status'] === 'ok' ? 'online' : 'offline',
            'response_time_ms' => $responseTime,
            'base_url' => $this->baseUrl,
            'details' => $result
        ];
    }
    
    /**
     * Get diagnostic information
     */
    public function getDiagnostics(): array
    {
        return $this->makeRequest('GET', "{$this->baseUrl}/diagnostics");
    }
    
    /**
     * Get device information
     */
    public function getDeviceInfo(): array
    {
        return $this->makeRequest('GET', "{$this->baseUrl}/device-info");
    }
}
