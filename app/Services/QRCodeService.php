<?php

namespace App\Services;

use App\Models\Reservation;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class QRCodeService
{
    /**
     * Directory for QR code storage
     */
    private const QR_STORAGE_PATH = 'qrcodes';
    private const QR_PUBLIC_PATH = 'media/qrcodes';

    /**
     * Generate QR code for reservation
     */
    public function generateForReservation(Reservation $reservation): array
    {
        // Check if already generated
        if ($reservation->qr_code && $reservation->qr_generated_at) {
            return [
                'success' => true,
                'qr_code' => $reservation->qr_code,
                'message' => 'QR code already generated',
            ];
        }

        // Prepare QR data - compact JSON with essential info
        $qrData = json_encode([
            'rid' => $reservation->id,                      // Reservation ID
            'token' => substr($reservation->access_token, 0, 32),  // First 32 chars
            'room' => $reservation->room_id,
            'start' => $reservation->start_at->timestamp,
            'end' => $reservation->end_at->timestamp,
            'type' => 'reservation',
        ]);

        // Generate QR code image
        $qrPath = $this->generateQRImage($qrData, $reservation->id);

        if (!$qrPath) {
            return [
                'success' => false,
                'message' => 'Failed to generate QR code image',
            ];
        }

        // Update reservation
        $reservation->update([
            'qr_code' => $qrPath,
            'qr_generated_at' => now(),
        ]);

        return [
            'success' => true,
            'qr_code' => $qrPath,
            'message' => 'QR code generated successfully',
        ];
    }

    /**
     * Generate QR image using external service with fallbacks
     */
    private function generateQRImage(string $qrData, int $reservationId): ?string
    {
        // Ensure directory exists
        $this->ensureStorageDirectory();

        $filename = "qr_" . $reservationId . "_" . time() . ".png";
        $filepath = storage_path(self::QR_STORAGE_PATH . "/" . $filename);

        // Try multiple QR code generators with fallback strategy

        // 1. Try Google Charts API (deprecated but widely supported)
        if ($this->tryGoogleChartsAPI($qrData, $filepath)) {
            return "/" . self::QR_PUBLIC_PATH . "/" . $filename;
        }

        // 2. Try QR Server API (reliable, free)
        if ($this->tryQRServerAPI($qrData, $filepath)) {
            return "/" . self::QR_PUBLIC_PATH . "/" . $filename;
        }

        // 3. Try alternative API
        if ($this->tryAlternativeQRAPI($qrData, $filepath)) {
            return "/" . self::QR_PUBLIC_PATH . "/" . $filename;
        }

        // 4. Fallback: Create text-based QR placeholder
        $this->createTextQRPlaceholder($qrData, $filepath);
        
        return "/" . self::QR_PUBLIC_PATH . "/" . $filename;
    }

    /**
     * Generate QR using Google Charts API
     */
    private function tryGoogleChartsAPI(string $qrData, string $filepath): bool
    {
        try {
            $encodedData = urlencode($qrData);
            $url = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl={$encodedData}&choe=UTF-8";

            $context = stream_context_create([
                'http' => ['timeout' => 5],
                'https' => ['timeout' => 5],
            ]);

            $imageContent = @file_get_contents($url, false, $context);

            if ($imageContent && strlen($imageContent) > 0) {
                return file_put_contents($filepath, $imageContent) !== false;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Generate QR using QR Server API
     */
    private function tryQRServerAPI(string $qrData, string $filepath): bool
    {
        try {
            $encodedData = urlencode($qrData);
            $url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={$encodedData}";

            $context = stream_context_create([
                'http' => ['timeout' => 5],
                'https' => ['timeout' => 5],
            ]);

            $imageContent = @file_get_contents($url, false, $context);

            if ($imageContent && strlen($imageContent) > 0) {
                return file_put_contents($filepath, $imageContent) !== false;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Generate QR using alternative service
     */
    private function tryAlternativeQRAPI(string $qrData, string $filepath): bool
    {
        try {
            $encodedData = urlencode($qrData);
            $url = "https://qr.api.quickchart.io/v1/create?text={$encodedData}&size=300";

            $context = stream_context_create([
                'http' => ['timeout' => 5],
                'https' => ['timeout' => 5],
            ]);

            $imageContent = @file_get_contents($url, false, $context);

            if ($imageContent && strlen($imageContent) > 0) {
                return file_put_contents($filepath, $imageContent) !== false;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Create text-based QR placeholder
     */
    private function createTextQRPlaceholder(string $qrData, string $filepath): bool
    {
        $content = "QR CODE PLACEHOLDER\n";
        $content .= "===================\n\n";
        $content .= "Data (Base64):\n";
        $content .= base64_encode($qrData) . "\n\n";
        $content .= "Raw Data:\n";
        $content .= $qrData . "\n";

        return file_put_contents($filepath, $content) !== false;
    }

    /**
     * Validate QR code data against a reservation
     */
    public function validateQRData(string $qrData, Reservation $reservation): array
    {
        try {
            $data = json_decode($qrData, true);

            if (!$data) {
                return [
                    'valid' => false,
                    'code' => 'INVALID_QR_FORMAT',
                    'message' => 'Invalid QR code format',
                ];
            }

            // Check reservation ID
            if (($data['rid'] ?? null) != $reservation->id) {
                return [
                    'valid' => false,
                    'code' => 'WRONG_RESERVATION',
                    'message' => 'QR code is for different reservation',
                ];
            }

            // Check room ID
            if (($data['room'] ?? null) != $reservation->room_id) {
                return [
                    'valid' => false,
                    'code' => 'WRONG_ROOM',
                    'message' => 'QR code is for different room',
                ];
            }

            // Check time window: 15 min before start to end of reservation
            $now = now();
            $start = $reservation->start_at;
            $end = $reservation->end_at;

            $accessStart = $start->copy()->subMinutes(15);
            $accessEnd = $end;

            if ($now->isBefore($accessStart)) {
                $minutesUntil = ceil($now->diffInSeconds($accessStart) / 60);
                return [
                    'valid' => false,
                    'code' => 'TOO_EARLY',
                    'message' => "Access too early. Available in {$minutesUntil} minutes",
                ];
            }

            if ($now->isAfter($accessEnd)) {
                return [
                    'valid' => false,
                    'code' => 'EXPIRED',
                    'message' => 'Reservation time window has expired',
                ];
            }

            return [
                'valid' => true,
                'code' => 'QR_VALID',
                'message' => 'QR code is valid',
                'reservation' => [
                    'id' => $reservation->id,
                    'room_id' => $reservation->room_id,
                    'user_id' => $reservation->user_id,
                    'start_at' => $reservation->start_at,
                    'end_at' => $reservation->end_at,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'code' => 'ERROR',
                'message' => 'Error validating QR code: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check if QR code is currently in valid time window
     */
    public function isQRCurrentlyValid(Reservation $reservation): bool
    {
        if (!$reservation->qr_code || !$reservation->qr_generated_at) {
            return false;
        }

        return $reservation->isQRValid();
    }

    /**
     * Get QR code access window
     */
    public function getAccessWindow(Reservation $reservation): array
    {
        return $reservation->getQRAccessWindow();
    }

    /**
     * Ensure storage directory exists
     */
    private function ensureStorageDirectory(): void
    {
        $dir = storage_path(self::QR_STORAGE_PATH);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Create public symlink if doesn't exist
        $publicDir = public_path(self::QR_PUBLIC_PATH);
        if (!is_link(public_path(self::QR_PUBLIC_PATH))) {
            if (!is_dir(dirname($publicDir))) {
                mkdir(dirname($publicDir), 0755, true);
            }
            if (!file_exists($publicDir)) {
                symlink($dir, $publicDir);
            }
        }
    }

    /**
     * Cleanup old QR codes
     */
    public function cleanupOldQRCodes(int $daysOld = 30): int
    {
        $dir = storage_path(self::QR_STORAGE_PATH);
        if (!is_dir($dir)) {
            return 0;
        }

        $cutoffTime = now()->subDays($daysOld)->timestamp;
        $deleted = 0;

        foreach (glob($dir . '/*.png') as $file) {
            if (filemtime($file) < $cutoffTime) {
                if (unlink($file)) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    /**
     * Generate QR code image from arbitrary data
     * Used for service access codes
     */
    public function generateQRImageFromData(string $qrData, string $identifier): ?string
    {
        $this->ensureStorageDirectory();

        $filename = $identifier . "_" . time() . ".png";
        $filepath = storage_path(self::QR_STORAGE_PATH . "/" . $filename);

        // Try QR code generators with fallback
        if ($this->tryGoogleChartsAPI($qrData, $filepath)) {
            return self::QR_PUBLIC_PATH . "/" . $filename;
        }

        if ($this->tryQRServerAPI($qrData, $filepath)) {
            return self::QR_PUBLIC_PATH . "/" . $filename;
        }

        if ($this->tryAlternativeQRAPI($qrData, $filepath)) {
            return self::QR_PUBLIC_PATH . "/" . $filename;
        }

        $this->createTextQRPlaceholder($qrData, $filepath);
        return self::QR_PUBLIC_PATH . "/" . $filename;
    }
}

