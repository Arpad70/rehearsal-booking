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
        Schema::table('reservations', function (Blueprint $table) {
            // QR code generation - access_token již existuje, přidáváme jen QR specifické sloupce
            $table->string('qr_code')->nullable()->comment('Path to generated QR code image');
            $table->dateTime('qr_generated_at')->nullable()->comment('When QR was generated');
            $table->dateTime('qr_sent_at')->nullable()->comment('When QR was sent to user (via email)');
            
            // Indices
            $table->index('qr_generated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex(['qr_generated_at']);
            $table->dropColumn(['qr_code', 'qr_generated_at', 'qr_sent_at']);
        });
    }
};
