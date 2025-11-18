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
        // Přidat enabled sloupec do tabulky rooms (Shelly zařízení)
        if (Schema::hasTable('rooms') && !Schema::hasColumn('rooms', 'enabled')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->boolean('enabled')->default(true)->after('shelly_token')->comment('Je místnost (Shelly zařízení) aktivní?');
            });
        }

        // Přidat enabled sloupec do tabulky room_readers (QR čtečky v místnostech)
        if (Schema::hasTable('room_readers') && !Schema::hasColumn('room_readers', 'enabled')) {
            Schema::table('room_readers', function (Blueprint $table) {
                $table->boolean('enabled')->default(true)->after('reader_key')->comment('Je QR čtečka v místnosti aktivní?');
            });
        }

        // Přidat enabled sloupec do tabulky global_readers (Globální QR čtečky)
        if (Schema::hasTable('global_readers') && !Schema::hasColumn('global_readers', 'enabled')) {
            Schema::table('global_readers', function (Blueprint $table) {
                $table->boolean('enabled')->default(true)->after('location')->comment('Je globální QR čtečka aktivní?');
            });
        }

        // Přidat enabled sloupec do tabulky service_access (Servisní přístupy)
        if (Schema::hasTable('service_access') && !Schema::hasColumn('service_access', 'enabled')) {
            Schema::table('service_access', function (Blueprint $table) {
                $table->boolean('enabled')->default(true)->after('code')->comment('Je servisní přístup aktivní?');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'enabled')) {
                $table->dropColumn('enabled');
            }
        });

        Schema::table('room_readers', function (Blueprint $table) {
            if (Schema::hasColumn('room_readers', 'enabled')) {
                $table->dropColumn('enabled');
            }
        });

        Schema::table('global_readers', function (Blueprint $table) {
            if (Schema::hasColumn('global_readers', 'enabled')) {
                $table->dropColumn('enabled');
            }
        });

        Schema::table('service_access', function (Blueprint $table) {
            if (Schema::hasColumn('service_access', 'enabled')) {
                $table->dropColumn('enabled');
            }
        });
    }
};
