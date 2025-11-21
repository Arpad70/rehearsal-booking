<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite nepodporuje renameColumn, takže použijeme alternativu
        if (Schema::hasColumn('equipment', 'rfid_tag') && !Schema::hasColumn('equipment', 'tag_id')) {
            // MySQL a PostgreSQL
            if (DB::getDriverName() !== 'sqlite') {
                Schema::table('equipment', function (Blueprint $table) {
                    $table->renameColumn('rfid_tag', 'tag_id');
                });
            } else {
                // SQLite - vytvoříme nový sloupec a zkopírujeme data
                Schema::table('equipment', function (Blueprint $table) {
                    $table->string('tag_id')->nullable();
                });
                DB::statement('UPDATE equipment SET tag_id = rfid_tag');
                Schema::table('equipment', function (Blueprint $table) {
                    $table->dropColumn('rfid_tag');
                });
            }
        }
        
        // Přidáme sloupec pro typ tagu (rfid nebo nfc)
        if (!Schema::hasColumn('equipment', 'tag_type')) {
            Schema::table('equipment', function (Blueprint $table) {
                $table->string('tag_type')->nullable();
            });
            
            // Nastavíme všechny existující tagy jako RFID typ
            DB::statement("UPDATE equipment SET tag_type = 'rfid' WHERE tag_id IS NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('equipment', 'tag_type')) {
            Schema::table('equipment', function (Blueprint $table) {
                $table->dropColumn('tag_type');
            });
        }
        
        if (Schema::hasColumn('equipment', 'tag_id') && !Schema::hasColumn('equipment', 'rfid_tag')) {
            if (DB::getDriverName() !== 'sqlite') {
                Schema::table('equipment', function (Blueprint $table) {
                    $table->renameColumn('tag_id', 'rfid_tag');
                });
            } else {
                Schema::table('equipment', function (Blueprint $table) {
                    $table->string('rfid_tag')->nullable();
                });
                DB::statement('UPDATE equipment SET rfid_tag = tag_id');
                Schema::table('equipment', function (Blueprint $table) {
                    $table->dropColumn('tag_id');
                });
            }
        }
    }
};
