<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GdImageConverter
{
    /**
     * Convert an uploaded image (any supported format) to jpg.
     *
     * @return string Relative storage path (without /storage prefix)
     */
    public static function convertUploadedToJpeg(
        UploadedFile $file,
        string $directory,
        string $disk = 'public',
        int $quality = 85
    ): string {
        $raw = @file_get_contents($file->getRealPath());
        if ($raw === false) {
            throw new RuntimeException('Unable to read uploaded image.');
        }

        $src = @imagecreatefromstring($raw);
        if (! $src) {
            throw new RuntimeException('Unsupported or corrupted image file.');
        }

        $width = imagesx($src);
        $height = imagesy($src);
        $dst = imagecreatetruecolor($width, $height);
        if (! $dst) {
            imagedestroy($src);
            throw new RuntimeException('Unable to initialize image conversion buffer.');
        }

        // Fill white background to handle transparent images (png/webp/gif).
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $white);
        imagecopy($dst, $src, 0, 0, 0, 0, $width, $height);

        $filename = uniqid('img_', true) . '.jpg';
        $directory = trim($directory, '/');
        $relativePath = $directory === '' ? $filename : ($directory . '/' . $filename);

        Storage::disk($disk)->makeDirectory($directory);
        $absolutePath = Storage::disk($disk)->path($relativePath);

        $ok = imagejpeg($dst, $absolutePath, max(10, min(100, $quality)));
        imagedestroy($dst);
        imagedestroy($src);

        if (! $ok) {
            throw new RuntimeException('Failed to write converted jpg image.');
        }

        return $relativePath;
    }
}

