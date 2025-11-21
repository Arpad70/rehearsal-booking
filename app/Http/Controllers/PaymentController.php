<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Reservation;
use App\Services\PaymentGateway\ComGatePaymentService;
use App\Services\PaymentGateway\StripePaymentService;
use App\Services\PaymentGateway\GoPayPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Initiate payment for a reservation
     */
    public function create(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'gateway' => 'required|in:stripe,comgate,gopay',
            'email' => 'nullable|email',
            'name' => 'nullable|string',
        ]);

        $gateway = match($validated['gateway']) {
            'stripe' => new StripePaymentService(),
            'comgate' => new ComGatePaymentService(),
            'gopay' => new GoPayPaymentService(),
        };

        $metadata = [
            'email' => $validated['email'] ?? $reservation->user->email ?? null,
            'name' => $validated['name'] ?? $reservation->user->name ?? null,
            'ip_address' => $request->ip(),
        ];

        $result = $gateway->createPayment($reservation, $metadata);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'payment_id' => $result['payment']->id,
                'checkout_url' => $result['checkout_url'],
                'message' => $result['message'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 400);
    }

    /**
     * Handle payment callback (success)
     */
    public function callback(Request $request, Payment $payment)
    {
        // Update payment status based on session_id (Stripe) or transaction verification
        $sessionId = $request->query('session_id');

        if ($sessionId && $payment->payment_gateway === 'stripe') {
            $gateway = new StripePaymentService();
            $status = $gateway->getPaymentStatus($payment);

            if ($status['success'] && $status['status'] === 'paid') {
                $payment->markAsCompleted();
            }
        }

        return view('payment.success', [
            'payment' => $payment->fresh(),
            'reservation' => $payment->reservation,
        ]);
    }

    /**
     * Handle payment cancellation
     */
    public function cancel(Payment $payment)
    {
        if ($payment->isPending()) {
            $payment->update(['status' => 'cancelled']);
        }

        return view('payment.cancelled', [
            'payment' => $payment,
            'reservation' => $payment->reservation,
        ]);
    }

    /**
     * Handle webhook from payment gateway
     */
    public function webhook(Request $request, string $gateway)
    {
        Log::info("Payment webhook received", [
            'gateway' => $gateway,
            'data' => $request->all(),
        ]);

        $service = match($gateway) {
            'stripe' => new StripePaymentService(),
            'comgate' => new ComGatePaymentService(),
            'gopay' => new GoPayPaymentService(),
            default => null,
        };

        if (!$service) {
            return response()->json(['error' => 'Invalid gateway'], 400);
        }

        $result = $service->handleWebhook($request->all());

        if ($result['success']) {
            return response()->json(['success' => true, 'message' => $result['message']]);
        }

        return response()->json(['success' => false, 'message' => $result['message']], 400);
    }

    /**
     * Refund a payment
     */
    public function refund(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0|max:' . $payment->amount,
            'reason' => 'nullable|string|max:500',
        ]);

        if (!$payment->isCompleted()) {
            return response()->json([
                'success' => false,
                'message' => 'Lze vrátit pouze dokončenou platbu',
            ], 400);
        }

        $gateway = match($payment->payment_gateway) {
            'stripe' => new StripePaymentService(),
            'comgate' => new ComGatePaymentService(),
            'gopay' => new GoPayPaymentService(),
            default => null,
        };

        if (!$gateway) {
            return response()->json([
                'success' => false,
                'message' => 'Neplatná platební brána',
            ], 400);
        }

        $result = $gateway->refundPayment(
            $payment,
            $validated['amount'] ?? null,
            $validated['reason'] ?? null
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get payment status
     */
    public function status(Payment $payment)
    {
        $gateway = match($payment->payment_gateway) {
            'stripe' => new StripePaymentService(),
            'comgate' => new ComGatePaymentService(),
            'gopay' => new GoPayPaymentService(),
            default => null,
        };

        if (!$gateway) {
            return response()->json([
                'success' => false,
                'message' => 'Neplatná platební brána',
            ], 400);
        }

        $result = $gateway->getPaymentStatus($payment);

        return response()->json([
            'success' => $result['success'],
            'status' => $payment->status,
            'gateway_status' => $result['status'] ?? null,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'paid_at' => $payment->paid_at,
        ]);
    }
}
