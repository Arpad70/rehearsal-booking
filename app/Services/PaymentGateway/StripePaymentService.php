<?php

namespace App\Services\PaymentGateway;

use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Support\Facades\Log;

class StripePaymentService extends PaymentGatewayService
{
    private string $apiKey;
    private string $webhookSecret;

    public function __construct()
    {
        $this->apiKey = (string) config('services.stripe.secret');
        $this->webhookSecret = (string) config('services.stripe.webhook_secret');
        \Stripe\Stripe::setApiKey($this->apiKey);
    }

    public function getGatewayName(): string
    {
        return 'stripe';
    }

    public function createPayment(Reservation $reservation, array $metadata = []): array
    {
        try {
            // Create payment record
            $payment = $this->createPaymentRecord($reservation, 'stripe', null, $metadata);

            // Create Stripe Checkout Session
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($payment->currency),
                        'unit_amount' => (int)($payment->amount * 100), // Amount in cents
                        'product_data' => [
                            'name' => "Rezervace #{$reservation->id}",
                            'description' => "Místnost: {$reservation->room->name}, " .
                                           "Datum: {$reservation->start->format('d.m.Y H:i')}",
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $this->getReturnUrl($payment) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $this->getCancelUrl($payment),
                'customer_email' => $payment->customer_email,
                'metadata' => [
                    'payment_id' => $payment->id,
                    'reservation_id' => $reservation->id,
                ],
            ]);

            // Update payment with session ID
            $payment->update([
                'transaction_id' => $session->id,
                'gateway_response' => [
                    'session_id' => $session->id,
                    'payment_intent' => $session->payment_intent,
                ],
            ]);

            return [
                'success' => true,
                'payment' => $payment->fresh(),
                'checkout_url' => $session->url,
                'message' => 'Platba byla úspěšně vytvořena',
            ];

        } catch (\Exception $e) {
            Log::error('Stripe payment creation failed', [
                'error' => $e->getMessage(),
                'reservation_id' => $reservation->id,
            ]);

            if (isset($payment)) {
                $payment->markAsFailed($e->getMessage());
            }

            return [
                'success' => false,
                'payment' => $payment ?? null,
                'checkout_url' => null,
                'message' => 'Chyba při vytváření platby: ' . $e->getMessage(),
            ];
        }
    }

    public function handleWebhook(array $data): array
    {
        try {
            $payload = request()->getContent();
            $sigHeader = request()->header('Stripe-Signature');

            // Verify webhook signature
            /** @var \Stripe\Event $event */
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $this->webhookSecret
            );

            Log::info('Stripe webhook received', ['type' => $event->type]);

            switch ($event->type) {
                case 'checkout.session.completed':
                    return $this->handleCheckoutCompleted($event->data->object);

                case 'payment_intent.succeeded':
                    return $this->handlePaymentSucceeded($event->data->object);

                case 'payment_intent.payment_failed':
                    return $this->handlePaymentFailed($event->data->object);

                default:
                    return [
                        'success' => true,
                        'payment' => null,
                        'message' => 'Webhook event processed (not handled)',
                    ];
            }

        } catch (\Exception $e) {
            Log::error('Stripe webhook processing failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'payment' => null,
                'message' => 'Chyba při zpracování webhook: ' . $e->getMessage(),
            ];
        }
    }

    public function refundPayment(Payment $payment, ?float $amount = null, ?string $reason = null): array
    {
        try {
            if (!$payment->transaction_id) {
                throw new \Exception('Payment has no transaction ID');
            }

            $refundAmount = $amount ?? $payment->amount;

            // Get payment intent from session
            /** @var \Stripe\Checkout\Session $session */
            $session = \Stripe\Checkout\Session::retrieve($payment->transaction_id);
            $paymentIntentId = $session->payment_intent;

            // Create refund
            /** @var \Stripe\Refund $refund */
            $refund = \Stripe\Refund::create([
                'payment_intent' => $paymentIntentId,
                'amount' => (int)($refundAmount * 100),
                'reason' => $reason ? 'requested_by_customer' : 'duplicate',
                'metadata' => [
                    'payment_id' => $payment->id,
                    'reason' => $reason,
                ],
            ]);

            // Update payment record
            $payment->refund($refundAmount, $reason);
            $payment->update([
                'gateway_response' => array_merge(
                    $payment->gateway_response ?? [],
                    ['refund_id' => $refund->id]
                ),
            ]);

            return [
                'success' => true,
                'message' => 'Platba byla úspěšně vrácena',
            ];

        } catch (\Exception $e) {
            Log::error('Stripe refund failed', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id,
            ]);

            return [
                'success' => false,
                'message' => 'Chyba při vracení platby: ' . $e->getMessage(),
            ];
        }
    }

    public function getPaymentStatus(Payment $payment): array
    {
        try {
            if (!$payment->transaction_id) {
                throw new \Exception('Payment has no transaction ID');
            }

            /** @var \Stripe\Checkout\Session $session */
            $session = \Stripe\Checkout\Session::retrieve($payment->transaction_id);

            return [
                'success' => true,
                'status' => $session->payment_status,
                'message' => 'Status úspěšně načten',
            ];

        } catch (\Exception $e) {
            Log::error('Stripe status check failed', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id,
            ]);

            return [
                'success' => false,
                'status' => 'unknown',
                'message' => 'Chyba při kontrole statusu: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle checkout session completed event
     */
    private function handleCheckoutCompleted($session): array
    {
        $paymentId = $session->metadata->payment_id ?? null;

        if (!$paymentId) {
            throw new \Exception('Payment ID not found in session metadata');
        }

        $payment = Payment::find($paymentId);

        if (!$payment) {
            throw new \Exception("Payment not found: {$paymentId}");
        }

        $payment->markAsCompleted($session->id);

        return [
            'success' => true,
            'payment' => $payment->fresh(),
            'message' => 'Platba byla úspěšně dokončena',
        ];
    }

    /**
     * Handle payment intent succeeded event
     */
    private function handlePaymentSucceeded($paymentIntent): array
    {
        // Find payment by payment intent ID
        $payment = Payment::where('gateway_response->payment_intent', $paymentIntent->id)->first();

        if (!$payment) {
            Log::warning('Payment not found for PaymentIntent', ['id' => $paymentIntent->id]);
            return [
                'success' => true,
                'payment' => null,
                'message' => 'Payment not found',
            ];
        }

        if (!$payment->isCompleted()) {
            $payment->markAsCompleted($payment->transaction_id);
        }

        return [
            'success' => true,
            'payment' => $payment->fresh(),
            'message' => 'Platba byla úspěšně dokončena',
        ];
    }

    /**
     * Handle payment intent failed event
     */
    private function handlePaymentFailed($paymentIntent): array
    {
        $payment = Payment::where('gateway_response->payment_intent', $paymentIntent->id)->first();

        if (!$payment) {
            Log::warning('Payment not found for failed PaymentIntent', ['id' => $paymentIntent->id]);
            return [
                'success' => true,
                'payment' => null,
                'message' => 'Payment not found',
            ];
        }

        $payment->markAsFailed($paymentIntent->last_payment_error->message ?? 'Payment failed');

        return [
            'success' => true,
            'payment' => $payment->fresh(),
            'message' => 'Platba selhala',
        ];
    }
}
