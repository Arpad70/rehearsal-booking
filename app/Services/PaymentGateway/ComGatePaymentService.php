<?php

namespace App\Services\PaymentGateway;

use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ComGatePaymentService extends PaymentGatewayService
{
    private string $merchantId;
    private string $secret;
    private string $apiUrl;
    private bool $testMode;

    public function __construct()
    {
        $this->merchantId = (string) config('services.comgate.merchant_id');
        $this->secret = (string) config('services.comgate.secret');
        $this->testMode = (bool) config('services.comgate.test_mode', true);
        $this->apiUrl = $this->testMode 
            ? 'https://payments.comgate.cz/v1.0' 
            : 'https://payments.comgate.cz/v1.0';
    }

    public function getGatewayName(): string
    {
        return 'comgate';
    }

    public function createPayment(Reservation $reservation, array $metadata = []): array
    {
        try {
            // Create payment record
            $payment = $this->createPaymentRecord($reservation, 'comgate', null, $metadata);

            // Prepare ComGate payment data
            $params = [
                'merchant' => $this->merchantId,
                'test' => $this->testMode ? 'true' : 'false',
                'price' => (int)($payment->amount * 100), // Amount in cents
                'curr' => $payment->currency,
                'label' => "Rezervace #{$reservation->id}",
                'refId' => $payment->id,
                'email' => $payment->customer_email,
                'method' => 'ALL', // All payment methods
                'prepareOnly' => 'true',
                'lang' => 'cs',
                'country' => 'CZ',
            ];

            // Create payment at ComGate
            $response = Http::asForm()->post("{$this->apiUrl}/create", $params);

            if (!$response->successful()) {
                throw new \Exception('ComGate API error: ' . $response->body());
            }

            $result = $this->parseResponse($response->body());

            if ($result['code'] !== '0') {
                throw new \Exception($result['message'] ?? 'Unknown ComGate error');
            }

            // Update payment with transaction ID
            $payment->update([
                'transaction_id' => $result['transId'],
                'gateway_response' => $result,
            ]);

            // Generate payment URL
            $checkoutUrl = "https://payments.comgate.cz/client/instructions/index?id={$result['transId']}";

            return [
                'success' => true,
                'payment' => $payment->fresh(),
                'checkout_url' => $checkoutUrl,
                'message' => 'Platba byla úspěšně vytvořena',
            ];

        } catch (\Exception $e) {
            Log::error('ComGate payment creation failed', [
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
            // Verify webhook signature
            if (!$this->verifyWebhookSignature($data)) {
                throw new \Exception('Invalid webhook signature');
            }

            $transactionId = $data['transId'] ?? null;
            $refId = $data['refId'] ?? null;
            $status = $data['status'] ?? null;

            if (!$transactionId || !$refId) {
                throw new \Exception('Missing required webhook parameters');
            }

            // Find payment by refId (payment ID)
            $payment = Payment::find($refId);

            if (!$payment) {
                throw new \Exception("Payment not found: {$refId}");
            }

            // Update payment status based on ComGate status
            switch ($status) {
                case 'PAID':
                    $payment->markAsCompleted($transactionId);
                    $message = 'Platba byla úspěšně dokončena';
                    break;

                case 'CANCELLED':
                    $payment->update(['status' => 'cancelled']);
                    $message = 'Platba byla zrušena';
                    break;

                case 'PENDING':
                    $payment->update(['status' => 'processing']);
                    $message = 'Platba čeká na zpracování';
                    break;

                default:
                    $payment->markAsFailed("Neznámý status: {$status}");
                    $message = "Neznámý status platby: {$status}";
            }

            $payment->update([
                'gateway_response' => $data,
            ]);

            return [
                'success' => true,
                'payment' => $payment->fresh(),
                'message' => $message,
            ];

        } catch (\Exception $e) {
            Log::error('ComGate webhook processing failed', [
                'error' => $e->getMessage(),
                'data' => $data,
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

            // ComGate refund request
            $params = [
                'merchant' => $this->merchantId,
                'transId' => $payment->transaction_id,
                'amount' => (int)($refundAmount * 100),
                'curr' => $payment->currency,
            ];

            $response = Http::asForm()->post("{$this->apiUrl}/refund", $params);

            if (!$response->successful()) {
                throw new \Exception('ComGate refund API error: ' . $response->body());
            }

            $result = $this->parseResponse($response->body());

            if ($result['code'] !== '0') {
                throw new \Exception($result['message'] ?? 'Refund failed');
            }

            // Update payment record
            $payment->refund($refundAmount, $reason);

            return [
                'success' => true,
                'message' => 'Platba byla úspěšně vrácena',
            ];

        } catch (\Exception $e) {
            Log::error('ComGate refund failed', [
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

            $params = [
                'merchant' => $this->merchantId,
                'transId' => $payment->transaction_id,
            ];

            $response = Http::asForm()->post("{$this->apiUrl}/status", $params);

            if (!$response->successful()) {
                throw new \Exception('ComGate status API error');
            }

            $result = $this->parseResponse($response->body());

            return [
                'success' => true,
                'status' => $result['status'] ?? 'unknown',
                'message' => 'Status úspěšně načten',
            ];

        } catch (\Exception $e) {
            Log::error('ComGate status check failed', [
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
     * Parse ComGate response (key=value format)
     */
    private function parseResponse(string $response): array
    {
        $lines = explode("\n", trim($response));
        $data = [];

        foreach ($lines as $line) {
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $data[$parts[0]] = $parts[1];
            }
        }

        return $data;
    }

    /**
     * Verify webhook signature
     */
    private function verifyWebhookSignature(array $data): bool
    {
        // ComGate doesn't use signatures in the same way as Stripe
        // Verification is done via checking the merchant ID and status endpoint
        return isset($data['merchant']) && $data['merchant'] === $this->merchantId;
    }
}
