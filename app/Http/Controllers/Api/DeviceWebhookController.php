<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AccessControlService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class DeviceWebhookController extends Controller
{
    public function __construct(
        private AccessControlService $accessControlService
    ) {}

    /**
     * Webhook pro QR scan event
     * POST /api/webhooks/qr-scan
     */
    public function handleQRScan(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string',
                'deviceId' => 'required|string',
                'scanId' => 'required|string',
                'timestamp' => 'required|string',
            ]);

            Log::info("QR Scan received", $validated);

            $result = $this->accessControlService->authorizeQRAccess(
                $validated['code'],
                $validated['deviceId'],
                $validated['scanId']
            );

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error("QR Scan webhook error: {$e->getMessage()}");
            return response()->json([
                'granted' => false,
                'message' => 'Chyba při zpracování'
            ], 500);
        }
    }

    /**
     * Webhook pro RFID scan event
     * POST /api/webhooks/rfid-scan
     */
    public function handleRFIDScan(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'cardId' => 'required|string',
                'deviceId' => 'required|string',
                'scanId' => 'required|string',
                'timestamp' => 'required|string',
            ]);

            Log::info("RFID Scan received", $validated);

            $result = $this->accessControlService->authorizeRFIDAccess(
                $validated['cardId'],
                $validated['deviceId'],
                $validated['scanId']
            );

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error("RFID Scan webhook error: {$e->getMessage()}");
            return response()->json([
                'granted' => false,
                'message' => 'Chyba při zpracování'
            ], 500);
        }
    }

    /**
     * Webhook pro PIN entry event
     * POST /api/webhooks/pin-entry
     */
    public function handlePINEntry(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'cardId' => 'required|string',
                'pin' => 'required|string',
                'deviceId' => 'required|string',
                'scanId' => 'required|string',
                'timestamp' => 'required|string',
            ]);

            Log::info("PIN Entry received", [
                'cardId' => $validated['cardId'],
                'deviceId' => $validated['deviceId'],
                'scanId' => $validated['scanId'],
            ]);

            $result = $this->accessControlService->authorizeRFIDAccess(
                $validated['cardId'],
                $validated['deviceId'],
                $validated['scanId'],
                $validated['pin']
            );

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error("PIN Entry webhook error: {$e->getMessage()}");
            return response()->json([
                'granted' => false,
                'message' => 'Chyba při zpracování'
            ], 500);
        }
    }

    /**
     * Webhook pro motion detection z kamery
     * POST /api/webhooks/motion-detected
     */
    public function handleMotionDetection(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'deviceId' => 'required|string',
                'timestamp' => 'required|string',
                'confidence' => 'nullable|numeric',
                'snapshot' => 'nullable|string', // Base64 encoded image
            ]);

            Log::info("Motion detected", [
                'deviceId' => $validated['deviceId'],
                'timestamp' => $validated['timestamp'],
            ]);

            // TODO: Implementovat logiku pro motion detection
            // - Zkontrolovat, zda je místnost rezervovaná
            // - Pokud ne, poslat alert adminovi
            // - Uložit snapshot

            return response()->json([
                'status' => 'ok',
                'message' => 'Motion detection logged'
            ]);

        } catch (\Exception $e) {
            Log::error("Motion detection webhook error: {$e->getMessage()}");
            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Webhook pro power consumption update z Shelly
     * POST /api/webhooks/power-update
     */
    public function handlePowerUpdate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'deviceId' => 'required|string',
                'timestamp' => 'required|string',
                'lights' => 'required|array',
                'outlets' => 'required|array',
            ]);

            Log::debug("Power update received", [
                'deviceId' => $validated['deviceId'],
                'lights_power' => $validated['lights']['power'] ?? 0,
                'outlets_power' => $validated['outlets']['power'] ?? 0,
            ]);

            // TODO: Implementovat logiku pro ukládání power consumption
            // - Uložit do ShellyLog tabulky
            // - Aktualizovat real-time widget

            return response()->json([
                'status' => 'ok',
                'message' => 'Power data logged'
            ]);

        } catch (\Exception $e) {
            Log::error("Power update webhook error: {$e->getMessage()}");
            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Webhook pro mixer scene change
     * POST /api/webhooks/mixer-scene-changed
     */
    public function handleMixerSceneChange(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'deviceId' => 'required|string',
                'sceneName' => 'required|string',
                'timestamp' => 'required|string',
            ]);

            Log::info("Mixer scene changed", $validated);

            // TODO: Implementovat logiku pro mixer scene changes
            // - Uložit změnu do databáze
            // - Aktualizovat stav rezervace

            return response()->json([
                'status' => 'ok',
                'message' => 'Scene change logged'
            ]);

        } catch (\Exception $e) {
            Log::error("Mixer scene change webhook error: {$e->getMessage()}");
            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Health check endpoint
     * GET /api/webhooks/health
     */
    public function healthCheck(): JsonResponse
    {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'service' => 'Device Webhook API',
        ]);
    }
}
