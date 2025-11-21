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
            // Odstranit shelly_ip
            if (Schema::hasColumn('rooms', 'shelly_ip')) {
                $table->dropColumn('shelly_ip');
            }
            
            // Adresa místnosti (pro Google mapy)
            $table->string('address')->nullable()->after('location');
            $table->decimal('latitude', 10, 8)->nullable()->after('address');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            
            // Obrázek místnosti
            $table->string('image')->nullable()->after('longitude');
            
            // Power monitoring nastavení
            $table->boolean('power_monitoring_enabled')->default(false)->after('image');
            $table->enum('power_monitoring_type', ['lights', 'outlets', 'both'])->nullable()->after('power_monitoring_enabled');
            
            // Automatické rozsvícení
            $table->boolean('auto_lights_enabled')->default(false)->after('power_monitoring_type');
            $table->boolean('auto_outlets_enabled')->default(false)->after('auto_lights_enabled');
            
            // Vstupní zařízení
            $table->enum('access_control_device', ['qr_reader', 'keypad', 'both'])->nullable()->after('auto_outlets_enabled');
            
            // Režim přístupu (strict = přesný čas, lenient = benevolentní)
            $table->enum('access_mode', ['strict', 'lenient'])->default('lenient')->after('access_control_device');
            
            // Aktivace zařízení
            $table->boolean('camera_enabled')->default(false)->after('access_mode');
            $table->boolean('mixer_enabled')->default(false)->after('camera_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn([
                'address',
                'latitude',
                'longitude',
                'image',
                'power_monitoring_enabled',
                'power_monitoring_type',
                'auto_lights_enabled',
                'auto_outlets_enabled',
                'access_control_device',
                'access_mode',
                'camera_enabled',
                'mixer_enabled',
            ]);
            
            // Vrátit shelly_ip
            $table->string('shelly_ip')->nullable();
        });
    }
};
