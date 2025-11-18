<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('reservations', function (Blueprint $table) {
            // Composite index for overlap detection (most used query)
            $table->index(['room_id', 'start_at', 'end_at', 'status'], 'idx_reservations_overlap');
            
            // Index for finding reservations by user
            $table->index('user_id', 'idx_reservations_user_id');
            
            // Index for token lookup
            $table->index('access_token', 'idx_reservations_access_token');
            
            // Index for finding used reservations
            $table->index('used_at', 'idx_reservations_used_at');
        });

        Schema::table('access_logs', function (Blueprint $table) {
            // Index for finding logs by reservation
            $table->index('reservation_id', 'idx_access_logs_reservation_id');
            
            // Index for finding logs by user
            $table->index('user_id', 'idx_access_logs_user_id');
            
            // Index for finding logs by creation time (for cleanup)
            $table->index('created_at', 'idx_access_logs_created_at');
            
            // Composite index for audit trail
            $table->index(['user_id', 'created_at'], 'idx_access_logs_user_created');
        });
    }

    public function down(): void {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex('idx_reservations_overlap');
            $table->dropIndex('idx_reservations_user_id');
            $table->dropIndex('idx_reservations_access_token');
            $table->dropIndex('idx_reservations_used_at');
        });

        Schema::table('access_logs', function (Blueprint $table) {
            $table->dropIndex('idx_access_logs_reservation_id');
            $table->dropIndex('idx_access_logs_user_id');
            $table->dropIndex('idx_access_logs_created_at');
            $table->dropIndex('idx_access_logs_user_created');
        });
    }
};
