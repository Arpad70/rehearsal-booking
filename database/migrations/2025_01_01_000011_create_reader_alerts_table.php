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
            
            // Reader reference
            $table->unsignedBigInteger('room_reader_id')->nullable();
            $table->unsignedBigInteger('global_reader_id')->nullable();
            $table->enum('reader_type', ['room_reader', 'global_reader'])->default('room_reader');
            
            // Alert details
            $table->enum('alert_type', [
                'connection_failed',
                'high_failure_rate',
                'offline',
                'configuration_error',
            ])->default('offline');
            
            $table->text('message');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            
            // Resolution tracking
            $table->boolean('resolved')->default(false);
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['reader_type', 'resolved']);
            $table->index(['severity', 'resolved']);
            $table->index('created_at');
            
            // Foreign keys
            $table->foreign('room_reader_id')
                ->references('id')
                ->on('room_readers')
                ->nullOnDelete();
                
            $table->foreign('global_reader_id')
                ->references('id')
                ->on('global_readers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reader_alerts');
    }
};
