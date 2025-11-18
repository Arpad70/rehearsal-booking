<?php  
use Illuminate\Database\Migrations\Migration;  
use Illuminate\Database\Schema\Blueprint;  
use Illuminate\Support\Facades\Schema;  

return new class extends Migration {  
    public function up(): void {  
        Schema::create('reservations', function (Blueprint $table) {  
            $table->id();  
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();  
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();  
            $table->dateTime('start_at');  
            $table->dateTime('end_at');  
            $table->string('status')->default('pending');  
            $table->string('access_token')->nullable()->unique();  
            $table->dateTime('token_valid_from')->nullable();  
            $table->dateTime('token_expires_at')->nullable();  
            $table->dateTime('used_at')->nullable();  
            $table->timestamps();  
        });  
    }  

    public function down(): void {  
        Schema::dropIfExists('reservations');  
    }  
};