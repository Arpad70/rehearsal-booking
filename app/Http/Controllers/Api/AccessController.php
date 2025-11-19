<?php  
namespace App\Http\Controllers\Api;  
use App\Http\Controllers\Controller;  
use Illuminate\Http\Request;  
use App\Models\Reservation;  
use App\Models\AccessLog;  
use Illuminate\Support\Facades\Log;

class AccessController extends Controller 
{  
    /**
     * Validate access token for room entry
     *
     * @param Request $r
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateAccess(Request $r): \Illuminate\Http\JsonResponse
    {  
        /** @var array{token: string, room_id: int} */
        $data = $r->validate(['token' => 'required|string', 'room_id' => 'required|integer']);
        $token = $data['token'];
        $roomId = $data['room_id'];

        // Validate token format (should be 64-char hex string)
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            AccessLog::create([
                'reservation_id' => null,
                'user_id' => null,
                'location' => "room:{$roomId}",
                'action' => 'scan',
                'result' => 'invalid_token_format',
                'ip' => $r->ip()
            ]);  
            Log::info('Access validation failed: invalid_token_format', ['token' => $token, 'room' => $roomId]);
            return response()->json(['allowed' => false, 'reason' => 'invalid_token'], 403);  
        }

        /** @var ?Reservation $reservation */
        $reservation = Reservation::where('access_token', $token)
            ->where('room_id', $roomId)
            ->first();  

        if (!$reservation) {  
            AccessLog::create([
                'reservation_id' => null,
                'user_id' => null,
                'location' => "room:{$roomId}",
                'action' => 'scan',
                'result' => 'invalid_token',
                'ip' => $r->ip()
            ]);  
            Log::info('Access validation failed: reservation not found', ['token' => $token, 'room' => $roomId]);
            return response()->json(['allowed' => false, 'reason' => 'invalid_token'], 403);  
        }  

        if (!$reservation->isTokenValid($token)) {  
            AccessLog::create([
                'reservation_id' => $reservation->id,
                'user_id' => $reservation->user_id,
                'location' => "room:{$roomId}",
                'action' => 'scan',
                'result' => 'expired_or_outside_window',
                'ip' => $r->ip()
            ]);  
            $details = ['reservation_id' => $reservation->id, 'now' => now()->toDateTimeString(), 'valid_from' => $reservation->token_valid_from?->toDateTimeString(), 'expires_at' => $reservation->token_expires_at?->toDateTimeString()];
            Log::info('Access validation failed: token not valid (expired or outside window)', $details);
            return response()->json(['allowed' => false, 'reason' => 'expired_or_outside_window'], 403);  
        }  

        // Mark token as used
        $reservation->update(['used_at' => now()]);

        AccessLog::create([
            'reservation_id' => $reservation->id,
            'user_id' => $reservation->user_id,
            'location' => "room:{$roomId}",
            'action' => 'scan',
            'result' => 'allowed',
            'ip' => $r->ip()
        ]);  

        return response()->json(['allowed' => true, 'action' => 'unlock', 'duration_seconds' => 15]);  
    }  

    /**
     * Log an access attempt
     * 
     * @param Request $r
     * @return \Illuminate\Http\JsonResponse
     */
    public function logAccess(Request $r): \Illuminate\Http\JsonResponse
    {  
        /** @var array{reservation_id: int, action: string, result: string, location: string} */
        $data = $r->validate([
            'reservation_id' => 'required|integer',
            'action' => 'required|string',
            'result' => 'required|string',
            'location' => 'required|string'
        ]);
        $reservationId = $data['reservation_id'];
        /** @var ?Reservation $reservation */
        $reservation = Reservation::find($reservationId);  

        $userId = $reservation ? $reservation->user_id : null;  

        AccessLog::create([  
            'reservation_id'=>$reservationId,  
            'user_id'=>$userId,  
            'location'=>$r->input('location'),  
            'action'=>$r->input('action'),  
            'result'=>$r->input('result'),  
            'ip'=>$r->ip()  
        ]);  

        return response()->json(['status'=>'logged']);  
    }
}