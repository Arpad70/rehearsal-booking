<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('power_monitoring', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            
            // Channel information
            $table->integer('channel')->default(0)->comment('Relay/switch channel number');
            $table->string('channel_name')->nullable()->comment('User-friendly channel name');
            
            // Power data
            $table->decimal('voltage', 8, 2)->nullable()->comment('Voltage in V');
            $table->decimal('current', 8, 3)->nullable()->comment('Current in A');
            $table->decimal('power', 10, 2)->nullable()->comment('Power in W');
            $table->decimal('power_factor', 3, 2)->nullable()->comment('Power factor (0-1)');
            
            // Energy consumption
            $table->decimal('energy_total', 12, 3)->nullable()->comment('Total energy in Wh');
            $table->decimal('energy_today', 10, 3)->nullable()->comment('Today energy in Wh');
            $table->decimal('energy_month', 11, 3)->nullable()->comment('This month energy in Wh');
            
            // Relay state
            $table->boolean('is_on')->default(false)->comment('Current relay state');
            $table->timestamp('last_switched_at')->nullable()->comment('When relay was last switched');
            
            // Temperature (if available)
            $table->decimal('temperature', 5, 2)->nullable()->comment('Device temperature in °C');
            $table->decimal('temperature_limit', 5, 2)->nullable()->comment('Temperature limit in °C');
            
            // Status
            $table->enum('status', ['normal', 'warning', 'alert'])->default('normal');
            $table->text('status_message')->nullable();
            
            // Metadata
            $table->json('raw_data')->nullable()->comment('Raw JSON response from Shelly');
            
            $table->timestamps();
            
            // Indexes
            $table->index('device_id');
            $table->index('room_id');
            $table->index('channel');
            $table->index('created_at');
            $table->index(['device_id', 'channel', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('power_monitoring');
    }
};
