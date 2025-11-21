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

        // Create data spanning 30 days with varying intervals
        // Days 0-7: every 5 minutes (high resolution)
        // Days 8-14: every 15 minutes (medium resolution)
        // Days 15-29: every 30 minutes (lower resolution)
        $now = now();
        $recordsCreated = 0;

        foreach ($devices as $device) {
            // Days 0-7 (last 7 days): Every 5 minutes = 12 per hour * 24 hours * 7 days = 2016 records
            for ($day = 0; $day < 7; $day++) {
                for ($hour = 0; $hour < 24; $hour++) {
                    for ($minute = 0; $minute < 60; $minute += 5) {
                        $timestamp = $now->copy()->subDays($day)->startOfDay()->addHours($hour)->addMinutes($minute);
                        
                        $power = $this->generatePowerValue($hour, $day);
                        $temperature = $this->generateTemperature($hour, $day);
                        $energyTotal = $this->calculateEnergy($day, $hour, $minute, $power);

                        PowerMonitoring::create([
                            'device_id' => $device->id,
                            'room_id' => $device->room_id,
                            'channel' => 0,
                            'channel_name' => 'Channel 0',
                            'voltage' => 230 + rand(-5, 5),
                            'current' => round($power / 230, 3),
                            'power' => $power,
                            'power_factor' => 0.95 + (rand(-5, 5) / 100),
                            'energy_total' => $energyTotal,
                            'energy_today' => ($hour * 60 + $minute) * ($power / 1000 / 60),
                            'energy_month' => $energyTotal * 30,
                            'is_on' => $power > 50,
                            'temperature' => $temperature,
                            'temperature_limit' => 80,
                            'status' => $this->getStatus($temperature),
                            'status_message' => $this->getStatusMessage($temperature),
                            'raw_data' => json_encode(['voltage' => 230, 'power' => $power, 'temperature' => $temperature]),
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ]);

                        $recordsCreated++;
                    }
                }
            }
            
            // Days 8-14 (week 2): Every 15 minutes = 4 per hour * 24 hours * 7 days = 672 records
            for ($day = 7; $day < 14; $day++) {
                for ($hour = 0; $hour < 24; $hour++) {
                    for ($minute = 0; $minute < 60; $minute += 15) {
                        $timestamp = $now->copy()->subDays($day)->startOfDay()->addHours($hour)->addMinutes($minute);
                        
                        $power = $this->generatePowerValue($hour, $day);
                        $temperature = $this->generateTemperature($hour, $day);
                        $energyTotal = $this->calculateEnergy($day, $hour, $minute, $power);

                        PowerMonitoring::create([
                            'device_id' => $device->id,
                            'room_id' => $device->room_id,
                            'channel' => 0,
                            'channel_name' => 'Channel 0',
                            'voltage' => 230 + rand(-5, 5),
                            'current' => round($power / 230, 3),
                            'power' => $power,
                            'power_factor' => 0.95 + (rand(-5, 5) / 100),
                            'energy_total' => $energyTotal,
                            'energy_today' => ($hour * 60 + $minute) * ($power / 1000 / 60),
                            'energy_month' => $energyTotal * 30,
                            'is_on' => $power > 50,
                            'temperature' => $temperature,
                            'temperature_limit' => 80,
                            'status' => $this->getStatus($temperature),
                            'status_message' => $this->getStatusMessage($temperature),
                            'raw_data' => json_encode(['voltage' => 230, 'power' => $power, 'temperature' => $temperature]),
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ]);

                        $recordsCreated++;
                    }
                }
            }
            
            // Days 15-29 (weeks 3-4): Every 30 minutes = 2 per hour * 24 hours * 15 days = 720 records
            for ($day = 14; $day < 30; $day++) {
                for ($hour = 0; $hour < 24; $hour++) {
                    for ($minute = 0; $minute < 60; $minute += 30) {
                        $timestamp = $now->copy()->subDays($day)->startOfDay()->addHours($hour)->addMinutes($minute);
                        
                        $power = $this->generatePowerValue($hour, $day);
                        $temperature = $this->generateTemperature($hour, $day);
                        $energyTotal = $this->calculateEnergy($day, $hour, $minute, $power);

                        PowerMonitoring::create([
                            'device_id' => $device->id,
                            'room_id' => $device->room_id,
                            'channel' => 0,
                            'channel_name' => 'Channel 0',
                            'voltage' => 230 + rand(-5, 5),
                            'current' => round($power / 230, 3),
                            'power' => $power,
                            'power_factor' => 0.95 + (rand(-5, 5) / 100),
                            'energy_total' => $energyTotal,
                            'energy_today' => ($hour * 60 + $minute) * ($power / 1000 / 60),
                            'energy_month' => $energyTotal * 30,
                            'is_on' => $power > 50,
                            'temperature' => $temperature,
                            'temperature_limit' => 80,
                            'status' => $this->getStatus($temperature),
                            'status_message' => $this->getStatusMessage($temperature),
                            'raw_data' => json_encode(['voltage' => 230, 'power' => $power, 'temperature' => $temperature]),
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ]);

                        $recordsCreated++;
                    }
                }
            }

            $totalRecordsPerDevice = (7 * 24 * 12) + (7 * 24 * 4) + (15 * 24 * 2);
            echo "✓ Created {$totalRecordsPerDevice} records for device: {$device->ip}\n";
        }

        echo "\n✅ PowerMonitoring seeding completed. Created {$recordsCreated} records.\n";
    }
    
    private function generatePowerValue(int $hour, int $day): float
    {
        // Base load varies by time of day
        $baseLoad = match(true) {
            $hour >= 6 && $hour < 9 => rand(300, 600),    // Morning peak
            $hour >= 9 && $hour < 17 => rand(200, 400),   // Day usage
            $hour >= 17 && $hour < 22 => rand(400, 800),  // Evening peak
            default => rand(50, 150),                      // Night/off-peak
        };
        
        // Add day-to-day variation
        $dayVariation = sin($day / 7 * M_PI) * 100;
        
        // Add random noise
        $noise = rand(-50, 50);
        
        return max(0, round($baseLoad + $dayVariation + $noise, 2));
    }
    
    private function generateTemperature(int $hour, int $day): float
    {
        // Base temperature
        $baseTemp = 25;
        
        // Daily cycle
        $dailyVariation = sin(($hour - 6) / 24 * M_PI * 2) * 5;
        
        // Day-to-day variation
        $dayVariation = sin($day / 30 * M_PI * 2) * 3;
        
        // Random noise
        $noise = rand(-20, 20) / 10;
        
        return round($baseTemp + $dailyVariation + $dayVariation + $noise, 2);
    }
    
    private function calculateEnergy(int $day, int $hour, int $minute, float $power): float
    {
        // Calculate total energy in Wh
        $totalMinutes = ($day * 24 * 60) + ($hour * 60) + $minute;
        return round(($totalMinutes / 60) * ($power / 1000) * 1000, 3); // Convert to Wh
    }
    
    private function getStatus(float $temperature): string
    {
        return match(true) {
            $temperature > 75 => 'alert',
            $temperature > 65 => 'warning',
            default => 'normal',
        };
    }
    
    private function getStatusMessage(float $temperature): ?string
    {
        return match(true) {
            $temperature > 75 => 'Temperature critical',
            $temperature > 65 => 'Temperature elevated',
            default => null,
        };
    }
}
