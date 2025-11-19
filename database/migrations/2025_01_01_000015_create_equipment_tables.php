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
        // Equipment types table
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Jméno vybavení (mikrofon, projektor, atd.)');
            $table->text('description')->nullable()->comment('Popis vybavení');
            $table->string('category')->comment('Kategorie: audio, video, furniture, climate, lighting, other');
            $table->string('model')->nullable()->comment('Model/označení vybavení');
            $table->string('serial_number')->nullable()->unique()->comment('Sériové číslo');
            $table->decimal('quantity_available', 8, 2)->default(1)->comment('Počet kusů');
            $table->boolean('is_critical')->default(false)->comment('Kritické vybavení (klíčové pro funkci místnosti)');
            $table->string('location')->nullable()->comment('Umístění/poznámka k umístění');
            $table->date('purchase_date')->nullable()->comment('Datum nákupu');
            $table->date('warranty_expiry')->nullable()->comment('Konec záruky');
            $table->text('maintenance_notes')->nullable()->comment('Poznámky o údržbě');
            $table->timestamps();
            $table->softDeletes();

            $table->index('category');
            $table->index('is_critical');
        });

        // Room-Equipment association (pivot table with extra attributes)
        Schema::create('room_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->foreignId('equipment_id')->constrained('equipment')->onDelete('cascade');
            $table->integer('quantity')->default(1)->comment('Počet kusů v místnosti');
            $table->boolean('installed')->default(true)->comment('Je vybavení nainstalováno?');
            $table->text('condition_notes')->nullable()->comment('Poznámky ke stavu vybavení');
            $table->dateTime('last_inspection')->nullable()->comment('Poslední kontrola vybavení');
            $table->string('status')->default('operational')->comment('Status: operational, needs_repair, maintenance, removed');
            $table->timestamps();

            $table->unique(['room_id', 'equipment_id']);
            $table->index('status');
            $table->index('installed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_equipment');
        Schema::dropIfExists('equipment');
    }
};
