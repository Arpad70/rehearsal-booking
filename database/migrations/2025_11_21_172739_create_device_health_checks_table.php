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
        Schema::create('device_health_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            
            $table->enum('status', ['online', 'offline', 'error', 'degraded'])
                  ->default('offline')
                  ->comment('Status zařízení');
            
            $table->integer('response_time_ms')->nullable()->comment('Doba odezvy (ms)');
            $table->json('diagnostics')->nullable()->comment('Diagnostické informace z device-info');
            $table->text('error_message')->nullable()->comment('Chybová zpráva při selhání');
            $table->timestamp('checked_at')->comment('Čas kontroly');
            $table->timestamps();
            
            $table->index(['device_id', 'checked_at']);
            $table->index('status');
            $table->index('checked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_health_checks');
    }
};
