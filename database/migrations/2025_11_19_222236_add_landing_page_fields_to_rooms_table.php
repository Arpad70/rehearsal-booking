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
            $table->decimal('price_per_hour', 10, 2)->default(200.00)->after('capacity');
            $table->boolean('is_public')->default(true)->after('price_per_hour');
            $table->text('description')->nullable()->after('is_public');
            $table->string('image_url')->nullable()->after('description');
            $table->string('size')->nullable()->after('image_url'); // small, medium, large
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['price_per_hour', 'is_public', 'description', 'image_url', 'size']);
        });
    }
};
