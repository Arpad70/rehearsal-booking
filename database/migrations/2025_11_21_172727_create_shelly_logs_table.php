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
        Schema::create('shelly_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_id')->nullable()->constrained()->onDelete('set null');
            
            // Kanál 0 - Světla (s relé)
            $table->decimal('lights_power', 10, 3)->default(0)->comment('Aktuální příkon světel (W)');
            $table->decimal('lights_energy', 12, 6)->default(0)->comment('Celková spotřeba světel (kWh)');
            $table->decimal('lights_voltage', 6, 2)->default(0)->comment('Napětí světel (V)');
            $table->decimal('lights_current', 8, 3)->default(0)->comment('Proud světel (A)');
            
            // Kanál 1 - Zásuvky (pouze monitoring)
            $table->decimal('outlets_power', 10, 3)->default(0)->comment('Aktuální příkon zásuvek (W)');
            $table->decimal('outlets_energy', 12, 6)->default(0)->comment('Celková spotřeba zásuvek (kWh)');
            $table->decimal('outlets_voltage', 6, 2)->default(0)->comment('Napětí zásuvek (V)');
            $table->decimal('outlets_current', 8, 3)->default(0)->comment('Proud zásuvek (A)');
            
            // Celkové hodnoty
            $table->decimal('total_power', 10, 3)->default(0)->comment('Celkový příkon (W)');
            $table->decimal('total_energy', 12, 6)->default(0)->comment('Celková spotřeba (kWh)');
            $table->decimal('cost', 10, 2)->default(0)->comment('Vypočtená cena (Kč)');
            
            $table->timestamp('measured_at')->comment('Čas měření');
            $table->timestamps();
            
            $table->index(['device_id', 'measured_at']);
            $table->index(['room_id', 'measured_at']);
            $table->index('measured_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shelly_logs');
    }
};
