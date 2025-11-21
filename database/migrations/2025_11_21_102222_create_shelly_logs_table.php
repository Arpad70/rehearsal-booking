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
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->string('device_id');
            $table->string('channel'); // 'lights' nebo 'outlets'
            $table->decimal('voltage', 8, 2);
            $table->decimal('current', 8, 3);
            $table->decimal('power', 10, 2); // W
            $table->decimal('energy', 12, 3); // kWh
            $table->decimal('power_factor', 3, 2)->nullable();
            $table->decimal('temperature', 5, 2)->nullable();
            $table->boolean('relay_state')->default(false);
            $table->timestamp('measured_at');
            $table->timestamps();

            $table->index(['room_id', 'measured_at']);
            $table->index(['device_id', 'measured_at']);
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
