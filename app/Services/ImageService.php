<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ImageService
{
    /**
     * Convert and save uploaded image to WebP format
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param int $quality
     * @return string Path to saved file
     */
    public function saveAsWebP(UploadedFile $file, string $directory = 'rooms', int $quality = 85): string
    {
        // Generate unique filename
        $filename = Str::ulid() . '.webp';
        $path = $directory . '/' . $filename;
        
        // Load image using GD or Imagick
        $image = $this->loadImage($file);
        
        // Convert to WebP
        $webpContent = $this->convertToWebP($image, $quality);
        
        // Save to storage
        Storage::disk('public')->put($path, $webpContent);
        
        return $path;
    }

    /**
     * Load image from uploaded file
     *
     * @param UploadedFile $file
     * @return \GdImage|resource
     */
    private function loadImage(UploadedFile $file)
    {
        $tempPath = $file->getRealPath();
        $mimeType = $file->getMimeType();

        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                return imagecreatefromjpeg($tempPath);
            case 'image/png':
                return imagecreatefrompng($tempPath);
            case 'image/gif':
                return imagecreatefromgif($tempPath);
            case 'image/webp':
                return imagecreatefromwebp($tempPath);
            default:
                // Fallback - try to detect format
                return imagecreatefromstring(file_get_contents($tempPath));
        }
    }

    /**
     * Convert GD image to WebP format
     *
     * @param \GdImage|resource $image
     * @param int $quality
     * @return string WebP binary content
     */
    private function convertToWebP($image, int $quality): string
    {
        try {
            ob_start();
            imagewebp($image, null, $quality);
            $webpContent = ob_get_clean();
            
            return $webpContent;
        } finally {
            /** @phpstan-ignore-next-line imagedestroy is not deprecated in PHP 8+ */
            if ($image instanceof \GdImage) {
                imagedestroy($image);
            }
        }
    }

    /**
     * Delete old image if it exists
     *
     * @param string|null $path
     * @return void
     */
    public function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
