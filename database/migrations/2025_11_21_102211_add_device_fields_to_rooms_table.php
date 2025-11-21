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
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('qr_reader_device_id')->nullable()->after('capacity');
            $table->string('keypad_device_id')->nullable()->after('qr_reader_device_id');
            $table->string('camera_device_id')->nullable()->after('keypad_device_id');
            $table->string('shelly_device_id')->nullable()->after('camera_device_id');
            $table->string('mixer_device_id')->nullable()->after('shelly_device_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn([
                'qr_reader_device_id',
                'keypad_device_id',
                'camera_device_id',
                'shelly_device_id',
                'mixer_device_id',
            ]);
        });
    }
};
