<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Payment gateway information
            $table->string('payment_gateway')->default('manual')->after('currency')->comment('stripe, comgate, gopay, manual');
            $table->string('transaction_id')->nullable()->after('payment_gateway')->comment('External transaction ID');
            $table->string('payment_method')->nullable()->after('transaction_id')->comment('card, bank_transfer, etc.');
            
            // Payment status
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded', 'cancelled'])
                ->default('pending')
                ->after('payment_method');
            
            // Gateway response
            $table->json('gateway_response')->nullable()->after('status')->comment('Raw gateway response');
            $table->text('error_message')->nullable()->after('gateway_response');
            
            // Refund information
            $table->decimal('refund_amount', 10, 2)->nullable()->after('error_message');
            $table->timestamp('refunded_at')->nullable()->after('refund_amount');
            $table->text('refund_reason')->nullable()->after('refunded_at');
            
            // Additional metadata
            $table->string('customer_email')->nullable()->after('refund_reason');
            $table->string('customer_name')->nullable()->after('customer_email');
            $table->ipAddress('ip_address')->nullable()->after('customer_name');
            
            // Indexes
            $table->index('status');
            $table->index('payment_gateway');
            $table->index('transaction_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'payment_gateway',
                'transaction_id',
                'payment_method',
                'status',
                'gateway_response',
                'error_message',
                'refund_amount',
                'refunded_at',
                'refund_reason',
                'customer_email',
                'customer_name',
                'ip_address',
            ]);
        });
    }
};
