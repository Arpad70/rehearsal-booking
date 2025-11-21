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
        // Add missing columns to access_logs
        Schema::table('access_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('access_logs', 'room_id')) {
                $table->foreignId('room_id')->nullable()->after('reservation_id')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('access_logs', 'access_granted')) {
                $table->boolean('access_granted')->default(false)->after('user_id');
            }
            if (!Schema::hasColumn('access_logs', 'failure_reason')) {
                $table->string('failure_reason')->nullable()->after('access_granted');
            }
            if (!Schema::hasColumn('access_logs', 'access_type')) {
                $table->enum('access_type', ['entry', 'exit', 'admin'])->default('entry')->after('action');
            }
            if (!Schema::hasColumn('access_logs', 'access_method')) {
                $table->enum('access_method', ['qr', 'rfid', 'pin', 'admin_override'])->nullable()->after('access_type');
            }
            if (!Schema::hasColumn('access_logs', 'device_id')) {
                $table->string('device_id')->nullable()->after('access_method');
            }
            if (!Schema::hasColumn('access_logs', 'scan_id')) {
                $table->string('scan_id')->nullable()->unique()->after('device_id');
            }
        });

        // Rename columns in rooms table to match AccessControlService expectations
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'device_id_qr_entry') && !Schema::hasColumn('rooms', 'qr_reader_device_id')) {
                $table->renameColumn('device_id_qr_entry', 'qr_reader_device_id');
            }
            if (Schema::hasColumn('rooms', 'device_id_keypad') && !Schema::hasColumn('rooms', 'keypad_device_id')) {
                $table->renameColumn('device_id_keypad', 'keypad_device_id');
            }
            if (Schema::hasColumn('rooms', 'device_id_camera') && !Schema::hasColumn('rooms', 'camera_device_id')) {
                $table->renameColumn('device_id_camera', 'camera_device_id');
            }
            if (Schema::hasColumn('rooms', 'device_id_shelly') && !Schema::hasColumn('rooms', 'shelly_device_id')) {
                $table->renameColumn('device_id_shelly', 'shelly_device_id');
            }
            if (Schema::hasColumn('rooms', 'device_id_mixer') && !Schema::hasColumn('rooms', 'mixer_device_id')) {
                $table->renameColumn('device_id_mixer', 'mixer_device_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('access_logs', function (Blueprint $table) {
            $table->dropColumn(['room_id', 'access_granted', 'failure_reason', 'access_type', 'access_method', 'device_id', 'scan_id']);
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->renameColumn('qr_reader_device_id', 'device_id_qr_entry');
            $table->renameColumn('keypad_device_id', 'device_id_keypad');
            $table->renameColumn('camera_device_id', 'device_id_camera');
            $table->renameColumn('shelly_device_id', 'device_id_shelly');
            $table->renameColumn('mixer_device_id', 'device_id_mixer');
        });
    }
};
