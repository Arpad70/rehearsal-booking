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
        Schema::create('room_readers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            
            // Reader configuration
            $table->string('reader_name')->comment('e.g., "QR Reader - Místnost 1"');
            $table->string('reader_ip')->comment('IP address of reader device');
            $table->integer('reader_port')->default(8080)->comment('Port of reader device');
            $table->string('reader_token')->comment('Authentication token for reader');
            $table->boolean('enabled')->default(true);
            
            // Door lock configuration
            $table->enum('door_lock_type', ['relay', 'api', 'webhook'])->default('relay')
                ->comment('Type of door lock: relay (GPIO/Arduino), api (smart lock), webhook (custom)');
            $table->json('door_lock_config')->comment('JSON config with lock-specific parameters (relay_pin, api_url, webhook_url, etc.)');
            
            // Timestamps
            $table->timestamps();
            
            // Indices
            $table->index('room_id');
            $table->index('enabled');
            $table->unique(['room_id', 'reader_ip'])->comment('One reader per IP per room');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_readers');
    }
};
