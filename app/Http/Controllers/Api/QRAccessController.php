<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccessLog;
use App\Models\Reservation;
use App\Models\RoomReader;
use App\Services\DoorLockService;
use App\Services\QRCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QRAccessController extends Controller
{
    public function __construct(
        private QRCodeService $qrService,
        private DoorLockService $doorService,
    ) {}

    /**
     * Validate QR code and unlock room
     * 
     * POST /api/v1/qr/validate
     * 
     * Request:
     * {
     *   "qr_data": "...",        // JSON from QR code
     *   "room_id": 1,            // Room ID
     *   "reader_token": "..."    // Reader authentication token
     * }
     */
    public function validateQRAccess(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr_data' => 'required|string|json',
            'room_id' => 'required|integer|exists:rooms,id',
            'reader_token' => 'required|string',
        ]);

        $room = \App\Models\Room::findOrFail($validated['room_id']);

        // Authenticate reader for this room
        $reader = $room->readers()
            ->where('enabled', true)
            ->where('reader_token', $validated['reader_token'])
            ->first();

        if (!$reader) {
            AccessLog::logAttempt(
                null,
                'failed',
                $request->ip(),
                $request->userAgent(),
                'UNAUTHORIZED_READER',
                'reservation',
                'room'
            );

            return response()->json([
                'access' => false,
                'code' => 'UNAUTHORIZED_READER',
                'message' => 'Unauthorized QR reader',
            ], 401);
        }

        // Parse QR data
        $qrData = $validated['qr_data'];
        $data = json_decode($qrData, true);

        if (!$data || !isset($data['rid'])) {
            AccessLog::logAttempt(
                null,
                'failed',
                $request->ip(),
                $request->userAgent(),
                'INVALID_QR',
                'reservation',
                'room'
            );

            return response()->json([
                'access' => false,
                'code' => 'INVALID_QR',
                'message' => 'Invalid QR code format',
            ], 400);
        }

        $reservationId = $data['rid'];
        $reservation = Reservation::find($reservationId);

        if (!$reservation) {
            AccessLog::logAttempt(
                null,
                'failed',
                $request->ip(),
                $request->userAgent(),
                'RESERVATION_NOT_FOUND',
                'reservation',
                'room'
            );

            return response()->json([
                'access' => false,
                'code' => 'RESERVATION_NOT_FOUND',
                'message' => 'Reservation not found',
            ], 404);
        }

        // Validate QR for this reservation
        $validation = $this->qrService->validateQRData($qrData, $reservation);

        if (!$validation['valid']) {
            AccessLog::logAttempt(
                $reservation->user_id,
                'failed',
                $request->ip(),
                $request->userAgent(),
                $validation['code'],
                'reservation',
                'room',
                null
            );

            return response()->json([
                'access' => false,
                'code' => $validation['code'],
                'message' => $validation['message'],
            ], 400);
        }

        // Unlock the door
        $unlockResult = $this->doorService->unlockRoom($reader);

        AccessLog::logAttempt(
            $reservation->user_id,
            $unlockResult['success'] ? 'success' : 'failed',
            $request->ip(),
            $request->userAgent(),
            'QR_ACCESS_' . ($unlockResult['success'] ? 'GRANTED' : 'FAILED'),
            'reservation',
            'room',
            null
        );

        return response()->json([
            'access' => true,
            'code' => 'QR_ACCESS_GRANTED',
            'message' => $unlockResult['message'],
            'door_unlocked' => $unlockResult['success'],
            'reservation' => [
                'id' => $reservation->id,
                'user_name' => $reservation->user->name,
                'room_name' => $reservation->room->name,
                'start_at' => $reservation->start_at,
                'end_at' => $reservation->end_at,
            ],
        ]);
    }

    /**
     * Health check endpoint for readers
     * 
     * GET /api/v1/qr/status
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'status' => 'online',
            'timestamp' => now(),
            'server_time' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Heartbeat for monitoring
     * 
     * GET /api/v1/qr/heartbeat
     */
    public function heartbeat(): JsonResponse
    {
        return response()->json([
            'alive' => true,
            'timestamp' => time(),
        ]);
    }

    /**
     * Test connection to room reader
     * 
     * POST /api/v1/rooms/{id}/readers/{readerId}/test
     */
    public function testReaderConnection(int $roomId, int $readerId): JsonResponse
    {
        $reader = RoomReader::where('room_id', $roomId)
            ->where('id', $readerId)
            ->firstOrFail();

        $result = $this->doorService->testConnection($reader);

        return response()->json($result);
    }
}
