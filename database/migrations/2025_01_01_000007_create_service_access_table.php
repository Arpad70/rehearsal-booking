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
        Schema::create('service_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Service identification
            $table->enum('access_type', ['cleaning', 'maintenance', 'admin'])->comment('Type of service access');
            $table->string('access_code')->unique()->comment('Unique code embedded in QR');
            $table->text('description')->nullable()->comment('Why this access was granted');
            
            // Room access control
            $table->json('allowed_rooms')->comment('JSON array of room IDs, or ["*"] for all');
            $table->boolean('unlimited_access')->default(false)->comment('If true, no time restrictions');
            
            // Time restrictions
            $table->dateTime('valid_from')->nullable()->comment('Start of validity period');
            $table->dateTime('valid_until')->nullable()->comment('End of validity period');
            
            // Usage tracking
            $table->integer('usage_count')->default(0)->comment('Number of times access was used');
            $table->dateTime('last_used_at')->nullable();
            
            // Status
            $table->boolean('enabled')->default(true);
            $table->boolean('revoked')->default(false)->comment('Manually revoked');
            $table->text('revoke_reason')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indices
            $table->index('user_id');
            $table->index('access_type');
            $table->index('enabled');
            $table->index('valid_from');
            $table->index('valid_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_access');
    }
};
