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
        Schema::create('global_readers', function (Blueprint $table) {
            $table->id();
            
            // Reader identification
            $table->string('reader_name')->unique()->comment('e.g., "Hlavní vchod", "Servis"');
            $table->enum('access_type', ['entrance', 'service', 'admin'])->comment('Type of global reader');
            
            // Network configuration
            $table->string('reader_ip')->comment('IP address of reader device');
            $table->integer('reader_port')->default(8080)->comment('Port of reader device');
            $table->string('reader_token')->comment('Authentication token for reader');
            $table->boolean('enabled')->default(true);
            
            // Door lock configuration
            $table->enum('door_lock_type', ['relay', 'api', 'webhook'])->default('relay');
            $table->json('door_lock_config')->comment('JSON config with lock-specific parameters');
            
            // Time window extensions for global readers (30 min before and after)
            $table->integer('access_minutes_before')->default(30)->comment('Minutes before reservation start to allow access');
            $table->integer('access_minutes_after')->default(30)->comment('Minutes after reservation end to allow access');
            
            // Service-specific settings
            $table->json('allowed_service_types')->nullable()->comment('JSON array of allowed service types (cleaning, maintenance, admin)');
            
            // Timestamps
            $table->timestamps();
            
            // Indices
            $table->index('enabled');
            $table->index('access_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_readers');
    }
};
