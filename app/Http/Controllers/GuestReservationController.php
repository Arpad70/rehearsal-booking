<?php

namespace App\Http\Controllers;

use App\Models\GuestVerification;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class GuestReservationController extends Controller
{
    /**
     * Show guest reservation form
     */
    public function create(Request $request)
    {
        $roomId = $request->query('room_id');
        $room = $roomId ? Room::findOrFail($roomId) : null;
        
        return view('guest-reservation.create', compact('room'));
    }

    /**
     * Send verification codes to email and phone
     */
    public function sendVerificationCodes(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'phone' => 'required|string|min:9',
        ]);

        // Delete expired verifications
        GuestVerification::where('expires_at', '<', now())->delete();

        // Check if verification already exists and is not expired
        $existingVerification = GuestVerification::where('email', $request->email)
            ->where('phone', $request->phone)
            ->where('expires_at', '>', now())
            ->first();

        if ($existingVerification) {
            // Resend the same codes
            $this->sendEmailCode($existingVerification->email, $existingVerification->email_code);
            $this->sendPhoneCode($existingVerification->phone, $existingVerification->phone_code);
            
            return response()->json([
                'success' => true,
                'message' => 'Ověřovací kódy byly znovu odeslány.',
                'session_token' => $existingVerification->session_token,
            ]);
        }

        // Create new verification
        $emailCode = GuestVerification::generateCode();
        $phoneCode = GuestVerification::generateCode();
        $sessionToken = GuestVerification::generateSessionToken();

        $verification = GuestVerification::create([
            'email' => $request->email,
            'phone' => $request->phone,
            'email_code' => $emailCode,
            'phone_code' => $phoneCode,
            'expires_at' => now()->addMinutes(15),
            'session_token' => $sessionToken,
        ]);

        // Send codes
        $this->sendEmailCode($request->email, $emailCode);
        $this->sendPhoneCode($request->phone, $phoneCode);

        return response()->json([
            'success' => true,
            'message' => 'Ověřovací kódy byly odeslány na váš email a telefon.',
            'session_token' => $sessionToken,
        ]);
    }

    /**
     * Verify email code
     */
    public function verifyEmailCode(Request $request)
    {
        $request->validate([
            'session_token' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        $verification = GuestVerification::where('session_token', $request->session_token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verification) {
            return response()->json([
                'success' => false,
                'message' => 'Neplatná nebo vypršená relace.',
            ], 400);
        }

        if ($verification->verifyEmailCode($request->code)) {
            return response()->json([
                'success' => true,
                'message' => 'Email úspěšně ověřen.',
                'fully_verified' => $verification->isFullyVerified(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Nesprávný ověřovací kód.',
        ], 400);
    }

    /**
     * Verify phone code
     */
    public function verifyPhoneCode(Request $request)
    {
        $request->validate([
            'session_token' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        $verification = GuestVerification::where('session_token', $request->session_token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verification) {
            return response()->json([
                'success' => false,
                'message' => 'Neplatná nebo vypršená relace.',
            ], 400);
        }

        if ($verification->verifyPhoneCode($request->code)) {
            return response()->json([
                'success' => true,
                'message' => 'Telefon úspěšně ověřen.',
                'fully_verified' => $verification->isFullyVerified(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Nesprávný ověřovací kód.',
        ], 400);
    }

    /**
     * Create guest reservation after verification
     */
    public function store(Request $request)
    {
        $request->validate([
            'session_token' => 'required|string',
            'room_id' => 'required|exists:rooms,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        $verification = GuestVerification::where('session_token', $request->session_token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verification || !$verification->isFullyVerified()) {
            return response()->json([
                'success' => false,
                'message' => 'Email a telefon musí být ověřeny před dokončením rezervace.',
            ], 400);
        }

        $room = Room::findOrFail($request->room_id);

        // Calculate total amount
        $startTime = \Carbon\Carbon::parse($request->start_time);
        $endTime = \Carbon\Carbon::parse($request->end_time);
        $hours = $startTime->diffInMinutes($endTime) / 60;
        $totalAmount = $room->price_per_hour * $hours;

        // Create reservation with guest data
        $reservation = Reservation::create([
            'user_id' => null, // Guest reservation
            'room_id' => $request->room_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'guest_email' => $verification->email,
            'guest_phone' => $verification->phone,
            'guest_name' => $request->guest_name ?? 'Host',
        ]);

        // Delete verification after successful reservation
        $verification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rezervace byla úspěšně vytvořena.',
            'reservation_id' => $reservation->id,
            'redirect_url' => route('guest.reservation.payment', $reservation->id),
        ]);
    }

    /**
     * Show payment page for guest reservation
     */
    public function showPayment(Reservation $reservation)
    {
        if (!$reservation->guest_email) {
            abort(404, 'Tato rezervace není pro hosty.');
        }

        return view('guest-reservation.payment', compact('reservation'));
    }

    /**
     * Send email verification code
     */
    private function sendEmailCode(string $email, string $code): void
    {
        try {
            Mail::raw(
                "Váš ověřovací kód pro rezervaci zkušebny je: {$code}\n\nKód je platný 15 minut.",
                function ($message) use ($email) {
                    $message->to($email)
                        ->subject('Ověřovací kód - RockSpace');
                }
            );
        } catch (\Exception $e) {
            Log::error('Failed to send email verification code: ' . $e->getMessage());
        }
    }

    /**
     * Send SMS verification code
     */
    private function sendPhoneCode(string $phone, string $code): void
    {
        try {
            // TODO: Implement SMS sending via SMS gateway (Twilio, Nexmo, etc.)
            // For now, just log it
            Log::info("SMS verification code for {$phone}: {$code}");
            
            // Example with Twilio:
            // $twilio = new Client(config('services.twilio.sid'), config('services.twilio.token'));
            // $twilio->messages->create($phone, [
            //     'from' => config('services.twilio.from'),
            //     'body' => "Váš ověřovací kód: {$code}"
            // ]);
        } catch (\Exception $e) {
            Log::error('Failed to send SMS verification code: ' . $e->getMessage());
        }
    }
}
