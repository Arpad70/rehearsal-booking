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
        Schema::table('access_logs', function (Blueprint $table) {
            // Přidat nové sloupce pro QR systém
            // Poznámka: stará tabulka má: reservation_id, user_id, location, action, result, ip
            // Přidáváme specifické QR sloupce
            
            // Pokud sloupec 'result' existuje, bude se používat jako 'validation_result'
            // Přidáváme jen nové sloupce potřebné pro QR systém
            $table->string('access_code')->nullable()->comment('Code for why access was granted/denied');
            $table->enum('access_type', ['reservation', 'service'])->default('reservation')->comment('Type of access');
            $table->enum('reader_type', ['room', 'global'])->default('room')->comment('Type of reader');
            $table->foreignId('global_reader_id')->nullable()->comment('Reference to global reader');
            
            // Přidáme sloupce pro nové tracking
            $table->string('ip_address')->nullable()->comment('IP address of requester');
            $table->string('user_agent')->nullable()->comment('User agent of requester');
            $table->dateTime('validated_at')->nullable()->comment('When validation occurred');
            
            // Indices
            $table->index('access_code');
            $table->index('reader_type');
            $table->index('global_reader_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('access_logs', function (Blueprint $table) {
            $table->dropIndex(['access_code']);
            $table->dropIndex(['reader_type']);
            $table->dropIndex(['global_reader_id']);
            $table->dropForeignKey(['global_reader_id']);
            $table->dropColumn([
                'access_code', 
                'access_type', 
                'reader_type', 
                'global_reader_id',
                'ip_address',
                'user_agent',
                'validated_at'
            ]);
        });
    }
};
