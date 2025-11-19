<?php

namespace Database\Seeders;

use App\Models\PowerMonitoring;
use App\Models\Device;
use Illuminate\Database\Seeder;

class PowerMonitoringSeeder extends Seeder
{
    public function run(): void
    {
        // Get all Shelly devices
        $devices = Device::where('type', 'shelly')->get();

        if ($devices->isEmpty()) {
            echo "⚠️  No Shelly devices found. Please create devices first.\n";
            return;
        }

        echo "Creating power monitoring data for " . $devices->count() . " devices...\n";

        // Create 288 records per device (24 hours * 12 measurements per hour)
        $now = now();
        $recordsCreated = 0;

        foreach ($devices as $device) {
            // Simulate 24 hours of data
            for ($hours = 23; $hours >= 0; $hours--) {
                // Create 12 measurements per hour (every 5 minutes)
                for ($minutes = 0; $minutes < 60; $minutes += 5) {
                    $timestamp = $now->copy()->subHours($hours)->addMinutes($minutes);

                    // Simulate varying power consumption
                    $baseLoad = rand(50, 150); // Base load in watts
                    $variableLoad = sin($hours / 24 * M_PI * 2) * 500; // Sinusoidal variation
                    $randomNoise = rand(-100, 100); // Random noise
                    $power = max(0, $baseLoad + $variableLoad + $randomNoise);

                    // Simulate energy consumption
                    $energyDelta = ($power / 60) * 5 / 1000; // kWh for 5 minute interval
                    $totalEnergy = ($now->diffInMinutes($timestamp) / 60) * ($power / 1000); // Total since midnight

                    // Simulate temperature
                    $temperature = 25 + (sin($hours / 24 * M_PI * 2) * 10) + rand(-2, 2);

                    PowerMonitoring::create([
                        'device_id' => $device->id,
                        'room_id' => $device->room_id,
                        'channel' => 0,
                        'channel_name' => 'Channel 0',
                        'voltage' => 230 + rand(-5, 5),
                        'current' => ($power / 230),
                        'power' => $power,
                        'power_factor' => 0.95 + (rand(-5, 5) / 100),
                        'energy_total' => $totalEnergy * 1000, // Convert back to Wh
                        'energy_today' => $totalEnergy * 1000,
                        'energy_month' => $totalEnergy * 30 * 1000, // Estimate monthly
                        'is_on' => $power > 50,
                        'temperature' => $temperature,
                        'temperature_limit' => 80,
                        'status' => $temperature > 75 ? 'alert' : ($temperature > 65 ? 'warning' : 'normal'),
                        'status_message' => $temperature > 75 ? 'Temperature critical' : ($temperature > 65 ? 'Temperature elevated' : null),
                        'raw_data' => json_encode([
                            'voltage' => 230 + rand(-5, 5),
                            'current' => ($power / 230),
                            'power' => $power,
                            'energy' => $totalEnergy * 1000,
                            'temperature' => $temperature,
                        ]),
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]);

                    $recordsCreated++;
                }
            }

            echo "✓ Created " . (24 * 12) . " records for device: {$device->ip}\n";
        }

        echo "\n✅ PowerMonitoring seeding completed. Created {$recordsCreated} records.\n";
    }
}
