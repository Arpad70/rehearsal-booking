<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action'); // 'created', 'updated', 'deleted'
            $table->string('model_type'); // 'Reservation', 'Room', etc.
            $table->unsignedBigInteger('model_id');
            $table->unsignedBigInteger('user_id')->nullable(); // Who performed the action
            $table->json('old_values')->nullable(); // Old data before change
            $table->json('new_values')->nullable(); // New data after change
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            // Indexes for efficient querying
            $table->index(['model_type', 'model_id'], 'idx_audit_model');
            $table->index('user_id', 'idx_audit_user_id');
            $table->index('action', 'idx_audit_action');
            $table->index('created_at', 'idx_audit_created_at');
        });
    }

    public function down(): void {
        Schema::dropIfExists('audit_logs');
    }
};
