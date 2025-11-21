<?php

namespace App\Services\PaymentGateway;

use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoPayPaymentService extends PaymentGatewayService
{
    private string $goId;
    private string $clientId;
    private string $clientSecret;
    private string $apiUrl;
    private bool $testMode;
    private ?string $accessToken = null;

    public function __construct()
    {
        $this->goId = (string) config('services.gopay.goid');
        $this->clientId = (string) config('services.gopay.client_id');
        $this->clientSecret = (string) config('services.gopay.client_secret');
        $this->testMode = (bool) config('services.gopay.test_mode', true);
        $this->apiUrl = $this->testMode 
            ? 'https://gw.sandbox.gopay.com' 
            : 'https://gate.gopay.cz';
    }

    public function getGatewayName(): string
    {
        return 'gopay';
    }

    public function createPayment(Reservation $reservation, array $metadata = []): array
    {
        try {
            // Get access token
            $this->authenticate();

            // Create payment record
            $payment = $this->createPaymentRecord($reservation, 'gopay', null, $metadata);

            // Prepare GoPay payment data
            $paymentData = [
                'payer' => [
                    'default_payment_instrument' => 'PAYMENT_CARD',
                    'allowed_payment_instruments' => ['PAYMENT_CARD', 'BANK_ACCOUNT'],
                    'contact' => [
                        'first_name' => $payment->customer_name ? explode(' ', $payment->customer_name)[0] : '',
                        'last_name' => $payment->customer_name ? (explode(' ', $payment->customer_name)[1] ?? '') : '',
                        'email' => $payment->customer_email,
                    ],
                ],
                'target' => [
                    'type' => 'ACCOUNT',
                    'goid' => $this->goId,
                ],
                'amount' => (int)($payment->amount * 100), // Amount in cents
                'currency' => $payment->currency,
                'order_number' => "RES-{$reservation->id}-{$payment->id}",
                'order_description' => "Rezervace místnosti #{$reservation->id}",
                'items' => [
                    [
                        'name' => $reservation->room->name,
                        'amount' => (int)($payment->amount * 100),
                        'count' => 1,
                    ],
                ],
                'callback' => [
                    'return_url' => $this->getReturnUrl($payment),
                    'notification_url' => $this->getWebhookUrl(),
                ],
                'lang' => 'CS',
            ];

            // Create payment at GoPay
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->accessToken}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post("{$this->apiUrl}/api/payments/payment", $paymentData);

            if (!$response->successful()) {
                throw new \Exception('GoPay API error: ' . $response->body());
            }

            $result = $response->json();

            if ($result['state'] === 'PAYMENT_METHOD_CHOSEN' || $result['state'] === 'CREATED') {
                // Update payment with GoPay ID
                $payment->update([
                    'transaction_id' => $result['id'],
                    'gateway_response' => $result,
                ]);

                return [
                    'success' => true,
                    'payment' => $payment->fresh(),
                    'checkout_url' => $result['gw_url'],
                    'message' => 'Platba byla úspěšně vytvořena',
                ];
            }

            throw new \Exception('Unexpected payment state: ' . ($result['state'] ?? 'unknown'));

        } catch (\Exception $e) {
            Log::error('GoPay payment creation failed', [
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
            $paymentId = $data['id'] ?? null;

            if (!$paymentId) {
                throw new \Exception('Missing payment ID in webhook');
            }

            // Get payment details from GoPay
            $this->authenticate();
            
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->accessToken}",
                'Accept' => 'application/json',
            ])->get("{$this->apiUrl}/api/payments/payment/{$paymentId}");

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch payment details from GoPay');
            }

            $gopayPayment = $response->json();

            // Find payment by transaction ID
            $payment = Payment::where('transaction_id', $paymentId)->first();

            if (!$payment) {
                throw new \Exception("Payment not found: {$paymentId}");
            }

            // Update payment status based on GoPay state
            $message = $this->updatePaymentStatus($payment, $gopayPayment);

            return [
                'success' => true,
                'payment' => $payment->fresh(),
                'message' => $message,
            ];

        } catch (\Exception $e) {
            Log::error('GoPay webhook processing failed', [
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

            $this->authenticate();

            $refundAmount = $amount ?? $payment->amount;

            // Create refund request
            $refundData = [
                'amount' => (int)($refundAmount * 100),
            ];

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->accessToken}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post("{$this->apiUrl}/api/payments/payment/{$payment->transaction_id}/refund", $refundData);

            if (!$response->successful()) {
                throw new \Exception('GoPay refund API error: ' . $response->body());
            }

            $result = $response->json();

            if ($result['result'] === 'FINISHED') {
                // Update payment record
                $payment->refund($refundAmount, $reason);
                $payment->update([
                    'gateway_response' => array_merge(
                        $payment->gateway_response ?? [],
                        ['refund' => $result]
                    ),
                ]);

                return [
                    'success' => true,
                    'message' => 'Platba byla úspěšně vrácena',
                ];
            }

            throw new \Exception('Refund failed with result: ' . ($result['result'] ?? 'unknown'));

        } catch (\Exception $e) {
            Log::error('GoPay refund failed', [
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

            $this->authenticate();

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->accessToken}",
                'Accept' => 'application/json',
            ])->get("{$this->apiUrl}/api/payments/payment/{$payment->transaction_id}");

            if (!$response->successful()) {
                throw new \Exception('GoPay status API error');
            }

            $result = $response->json();

            return [
                'success' => true,
                'status' => $result['state'] ?? 'unknown',
                'message' => 'Status úspěšně načten',
            ];

        } catch (\Exception $e) {
            Log::error('GoPay status check failed', [
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
     * Authenticate and get access token
     */
    private function authenticate(): void
    {
        if ($this->accessToken) {
            return; // Token already exists
        }

        try {
            $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
                ->asForm()
                ->post("{$this->apiUrl}/api/oauth2/token", [
                    'grant_type' => 'client_credentials',
                    'scope' => 'payment-all',
                ]);

            if (!$response->successful()) {
                throw new \Exception('GoPay authentication failed: ' . $response->body());
            }

            $result = $response->json();
            $this->accessToken = $result['access_token'];

        } catch (\Exception $e) {
            Log::error('GoPay authentication failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update payment status based on GoPay state
     */
    private function updatePaymentStatus(Payment $payment, array $gopayPayment): string
    {
        $state = $gopayPayment['state'] ?? 'UNKNOWN';

        $payment->update([
            'gateway_response' => $gopayPayment,
        ]);

        switch ($state) {
            case 'PAID':
                $payment->markAsCompleted($gopayPayment['id']);
                return 'Platba byla úspěšně dokončena';

            case 'CANCELED':
                $payment->update(['status' => 'cancelled']);
                return 'Platba byla zrušena';

            case 'TIMEOUTED':
                $payment->markAsFailed('Platba vypršela');
                return 'Platba vypršela';

            case 'REFUNDED':
                $payment->update(['status' => 'refunded']);
                return 'Platba byla vrácena';

            case 'PARTIALLY_REFUNDED':
                $payment->update(['status' => 'refunded']);
                return 'Platba byla částečně vrácena';

            case 'PAYMENT_METHOD_CHOSEN':
            case 'CREATED':
                $payment->update(['status' => 'pending']);
                return 'Platba čeká na zpracování';

            default:
                Log::warning('Unknown GoPay payment state', [
                    'state' => $state,
                    'payment_id' => $payment->id,
                ]);
                return "Neznámý status platby: {$state}";
        }
    }

    /**
     * Generate webhook URL for GoPay notifications
     */
    protected function getWebhookUrl(): string
    {
        return route('payment.webhook', ['gateway' => 'gopay']);
    }
}
