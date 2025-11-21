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
        Schema::table('users', function (Blueprint $table) {
            $table->string('rfid_card_id')->nullable()->unique()->after('email');
            $table->string('pin_hash')->nullable()->after('password');
            $table->string('band_name')->nullable()->after('name');
            $table->text('mixer_preferences')->nullable()->after('band_name'); // JSON
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'rfid_card_id',
                'pin_hash',
                'band_name',
                'mixer_preferences',
            ]);
        });
    }
};
