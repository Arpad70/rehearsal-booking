<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reader_alerts', function (Blueprint $table) {
            $table->id();
            $table->morphs('alertable'); // room_reader or global_reader
            $table->enum('alert_type', [
                'offline',
                'high_failure_rate',
                'no_activity',
                'suspicious_access',
                'configuration_error',
            ]);
            $table->text('message');
            $table->json('metadata')->nullable(); // Additional context
            $table->enum('severity', ['info', 'warning', 'critical'])->default('warning');
            $table->boolean('acknowledged')->default(false);
            $table->timestamp('acknowledged_at')->nullable();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            
            $table->index(['alertable_type', 'alertable_id']);
            $table->index(['severity', 'acknowledged']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reader_alerts');
    }
};
