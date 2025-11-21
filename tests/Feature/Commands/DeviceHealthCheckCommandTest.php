<?php

namespace Tests\Feature\Commands;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DeviceHealthCheckCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_command_exists(): void
    {
        $commands = Artisan::all();
        
        $this->assertArrayHasKey('devices:health-check', $commands);
    }

    public function test_command_has_correct_signature(): void
    {
        $command = Artisan::all()['devices:health-check'];
        
        $this->assertEquals('devices:health-check', $command->getName());
        $this->assertStringContainsString('Perform health check', $command->getDescription());
    }

    public function test_command_has_type_option(): void
    {
        $command = Artisan::all()['devices:health-check'];
        $definition = $command->getDefinition();
        
        $this->assertTrue($definition->hasOption('type'));
    }

    public function test_device_health_service_exists(): void
    {
        $service = app(\App\Services\DeviceHealthService::class);
        
        $this->assertInstanceOf(\App\Services\DeviceHealthService::class, $service);
    }

    public function test_device_services_are_registered(): void
    {
        $services = [
            \App\Services\DeviceServices\QRReaderService::class,
            \App\Services\DeviceServices\KeypadService::class,
            \App\Services\DeviceServices\CameraService::class,
            \App\Services\DeviceServices\MixerService::class,
            \App\Services\DeviceServices\ShellyService::class,
        ];

        foreach ($services as $serviceClass) {
            $service = new $serviceClass('test-device-id', 8080);
            $this->assertInstanceOf($serviceClass, $service);
        }
    }

    public function test_base_device_service_has_circuit_breaker_methods(): void
    {
        $service = new \App\Services\DeviceServices\QRReaderService('test-device-id', 9101);
        $reflection = new \ReflectionClass($service);
        
        $this->assertTrue($reflection->hasMethod('isCircuitOpen'));
        $this->assertTrue($reflection->hasMethod('recordSuccess'));
        $this->assertTrue($reflection->hasMethod('recordFailure'));
    }

    public function test_device_models_have_required_relationships(): void
    {
        $device = new \App\Models\Device();
        
        $this->assertTrue(method_exists($device, 'healthChecks'));
        $this->assertTrue(method_exists($device, 'room'));
    }
}
