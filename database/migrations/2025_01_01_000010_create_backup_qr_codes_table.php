<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_qr_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
            $table->string('qr_code'); // Path to QR image
            $table->text('qr_data')->nullable(); // JSON data encoded in QR
            $table->integer('sequence_number')->default(1); // 1 = primary, 2 = backup, etc
            $table->enum('status', ['active', 'used', 'expired', 'revoked'])->default('active');
            $table->timestamp('used_at')->nullable();
            $table->string('used_by_reader')->nullable(); // Which reader used this QR
            $table->timestamps();
            
            $table->index(['reservation_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_qr_codes');
    }
};
