<?php

namespace Tests\Unit\Services;

use App\Services\DeviceHealthService;
use Tests\TestCase;

class DeviceHealthServiceTest extends TestCase
{
    private DeviceHealthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DeviceHealthService::class);
    }

    public function test_get_device_service_returns_correct_service_for_qr_reader(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getDeviceService');
        
        $service = $method->invoke($this->service, 'qr_reader');
        
        $this->assertInstanceOf(\App\Services\DeviceServices\QRReaderService::class, $service);
    }

    public function test_get_device_service_returns_correct_service_for_keypad(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getDeviceService');
        
        $service = $method->invoke($this->service, 'keypad');
        
        $this->assertInstanceOf(\App\Services\DeviceServices\KeypadService::class, $service);
    }

    public function test_get_device_service_returns_correct_service_for_camera(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getDeviceService');
        
        $service = $method->invoke($this->service, 'camera');
        
        $this->assertInstanceOf(\App\Services\DeviceServices\CameraService::class, $service);
    }

    public function test_get_device_service_returns_correct_service_for_mixer(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getDeviceService');
        
        $service = $method->invoke($this->service, 'mixer');
        
        $this->assertInstanceOf(\App\Services\DeviceServices\MixerService::class, $service);
    }

    public function test_get_device_service_throws_exception_for_unknown_type(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unknown device type: unknown');
        
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getDeviceService');
        
        $method->invoke($this->service, 'unknown');
    }

    public function test_get_device_port_extracts_port_from_meta(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getDevicePort');
        
        $device = new \App\Models\Device([
            'ip' => '192.168.1.100',
            'meta' => json_encode(['port' => 9101]),
        ]);
        
        $port = $method->invoke($this->service, $device);
        
        $this->assertEquals(9101, $port);
    }

    public function test_get_device_port_parses_port_from_ip(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getDevicePort');
        
        $device = new \App\Models\Device([
            'ip' => '192.168.1.100:8080',
            'meta' => null,
        ]);
        
        $port = $method->invoke($this->service, $device);
        
        $this->assertEquals(8080, $port);
    }

    public function test_get_device_port_returns_null_when_no_port(): void
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getDevicePort');
        
        $device = new \App\Models\Device([
            'ip' => '192.168.1.100',
            'meta' => null,
        ]);
        
        $port = $method->invoke($this->service, $device);
        
        $this->assertNull($port);
    }
}
