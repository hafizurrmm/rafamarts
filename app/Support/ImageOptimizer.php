<?php

namespace App\Support;

/**
 * Resizes + compresses an uploaded image in place, using PHP's built-in GD extension
 * only — no composer package (intervention/image etc.) required, since GD is enabled
 * on almost every shared host by default. Large, uncompressed phone-camera photos are
 * one of the biggest page-speed killers on an ecommerce site, so every image saved
 * through a Filament FileUpload (product photos, banners, logo) is run through this.
 *
 * Safe by design: if GD is missing, the file isn't an image, or anything goes wrong,
 * it silently leaves the original file untouched rather than breaking the upload.
 */
class ImageOptimizer
{
    public static function optimize(?string $relativePath, int $maxWidth = 1600, int $quality = 80): void
    {
        if (blank($relativePath) || !extension_loaded('gd')) {
            return;
        }

        $fullPath = storage_path('app/public/' . ltrim($relativePath, '/'));

        if (!is_file($fullPath)) {
            return;
        }

        $info = @getimagesize($fullPath);

        if (!$info) {
            return; // not a readable raster image (e.g. SVG) — leave it alone
        }

        [$width, $height, $type] = $info;

        try {
            $source = match ($type) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($fullPath),
                IMAGETYPE_PNG => imagecreatefrompng($fullPath),
                IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($fullPath) : null,
                default => null, // skip gif/bmp/svg — resizing/recompressing isn't worth the risk
            };

            if (!$source) {
                return;
            }

            // Downscale only if it's actually bigger than the target — never upscale.
            if ($width > $maxWidth) {
                $newWidth = $maxWidth;
                $newHeight = (int) round($height * ($maxWidth / $width));

                $resized = imagecreatetruecolor($newWidth, $newHeight);

                if ($type === IMAGETYPE_PNG) {
                    imagealphablending($resized, false);
                    imagesavealpha($resized, true);
                }

                imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($source);
                $source = $resized;
            }

            match ($type) {
                IMAGETYPE_JPEG => imagejpeg($source, $fullPath, $quality),
                IMAGETYPE_PNG => imagepng($source, $fullPath, (int) round((100 - $quality) / 11.11)), // PNG uses 0 (best) - 9 (smallest)
                IMAGETYPE_WEBP => function_exists('imagewebp') ? imagewebp($source, $fullPath, $quality) : null,
                default => null,
            };

            imagedestroy($source);
        } catch (\Throwable $e) {
            // Never let a broken/corrupt image file break a save — just keep the original.
            report($e);
        }
    }
}
