<?php

namespace App\Services\PaymentGateway;

use App\Models\Payment;
use App\Models\Reservation;

abstract class PaymentGatewayService
{
    /**
     * Create a payment intent/transaction
     * 
     * @param Reservation $reservation
     * @param array $metadata Additional metadata
     * @return array ['success' => bool, 'payment' => Payment, 'checkout_url' => string|null, 'message' => string]
     */
    abstract public function createPayment(Reservation $reservation, array $metadata = []): array;

    /**
     * Process webhook/callback from payment gateway
     * 
     * @param array $data Webhook data
     * @return array ['success' => bool, 'payment' => Payment|null, 'message' => string]
     */
    abstract public function handleWebhook(array $data): array;

    /**
     * Refund a payment
     * 
     * @param Payment $payment
     * @param float $amount Amount to refund (null for full refund)
     * @param string|null $reason Refund reason
     * @return array ['success' => bool, 'message' => string]
     */
    abstract public function refundPayment(Payment $payment, ?float $amount = null, ?string $reason = null): array;

    /**
     * Get payment status from gateway
     * 
     * @param Payment $payment
     * @return array ['success' => bool, 'status' => string, 'message' => string]
     */
    abstract public function getPaymentStatus(Payment $payment): array;

    /**
     * Get gateway name
     */
    abstract public function getGatewayName(): string;

    /**
     * Create a payment record in database
     */
    protected function createPaymentRecord(
        Reservation $reservation,
        string $gateway,
        ?string $transactionId = null,
        array $metadata = []
    ): Payment {
        return Payment::create([
            'reservation_id' => $reservation->id,
            'amount' => $reservation->price,
            'currency' => 'CZK',
            'payment_gateway' => $gateway,
            'transaction_id' => $transactionId,
            'status' => 'pending',
            'customer_email' => $metadata['email'] ?? $reservation->user->email ?? null,
            'customer_name' => $metadata['name'] ?? $reservation->user->name ?? null,
            'ip_address' => $metadata['ip_address'] ?? request()->ip(),
        ]);
    }

    /**
     * Generate return URL for payment callback
     */
    protected function getReturnUrl(Payment $payment): string
    {
        return route('payment.callback', ['payment' => $payment->id]);
    }

    /**
     * Generate cancel URL for payment cancellation
     */
    protected function getCancelUrl(Payment $payment): string
    {
        return route('payment.cancel', ['payment' => $payment->id]);
    }

    /**
     * Generate webhook URL for payment gateway
     */
    protected function getWebhookUrl(): string
    {
        return route('payment.webhook', ['gateway' => $this->getGatewayName()]);
    }
}
