<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('devices', function (Blueprint $table) {
            // Add room_id foreign key
            $table->foreignId('room_id')->nullable()->constrained()->cascadeOnDelete();
            
            // Index for finding devices by room
            $table->index('room_id', 'idx_devices_room_id');
        });
    }

    public function down(): void {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropForeignKey(['room_id']);
            $table->dropIndex('idx_devices_room_id');
            $table->dropColumn('room_id');
        });
    }
};
