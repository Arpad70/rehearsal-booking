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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['registration_discount', 'event_discount', 'general_info', 'announcement'])->default('general_info');
            $table->string('discount_code')->nullable();
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->string('image_url')->nullable();
            $table->string('button_text')->default('Chci slevu');
            $table->string('button_url')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_permanent')->default(false);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('priority')->default(0); // Higher priority shows first
            $table->json('target_audience')->nullable(); // guest, registered, all
            $table->integer('max_displays')->nullable(); // Max times to show
            $table->boolean('show_once_per_session')->default(false);
            $table->timestamps();
            
            $table->index('is_active');
            $table->index('type');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
