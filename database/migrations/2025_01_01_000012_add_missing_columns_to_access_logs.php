<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('access_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('access_logs', 'access_granted')) {
                $table->boolean('access_granted')->default(false)->after('result');
            }
            
            if (!Schema::hasColumn('access_logs', 'failure_reason')) {
                $table->string('failure_reason')->nullable()->after('access_granted');
            }
            
            if (!Schema::hasColumn('access_logs', 'room_id')) {
                $table->unsignedBigInteger('room_id')->nullable()->after('reservation_id');
                $table->foreign('room_id')->references('id')->on('rooms')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('access_logs', function (Blueprint $table) {
            if (Schema::hasColumn('access_logs', 'room_id')) {
                $table->dropForeign(['room_id']);
                $table->dropColumn('room_id');
            }
            
            if (Schema::hasColumn('access_logs', 'failure_reason')) {
                $table->dropColumn('failure_reason');
            }
            
            if (Schema::hasColumn('access_logs', 'access_granted')) {
                $table->dropColumn('access_granted');
            }
        });
    }
};
