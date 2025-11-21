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
        // Rozšíření enum type o nové typy zařízení: qr_reader, keypad, camera, mixer
        \Illuminate\Support\Facades\DB::statement("
            ALTER TABLE devices 
            MODIFY COLUMN type ENUM('shelly', 'lock', 'reader', 'qr_reader', 'keypad', 'camera', 'mixer') 
            NOT NULL DEFAULT 'shelly'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Vrátit enum na původní stav
        \Illuminate\Support\Facades\DB::statement("
            ALTER TABLE devices 
            MODIFY COLUMN type ENUM('shelly', 'lock', 'reader') 
            NOT NULL DEFAULT 'shelly'
        ");
    }
};
