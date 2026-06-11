<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadedImageOptimizer
{
    public function storePublic(
        UploadedFile $file,
        string $directory,
        string $fileName,
        int $maxDimension = 1600
    ): array {
        $disk = Storage::disk('public');
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $targetPath = trim($directory, '/') . '/' . ltrim($fileName, '/');

        $optimized = $this->optimizeImageBinary($file, $extension, $maxDimension);

        if ($optimized !== null) {
            $disk->put($targetPath, $optimized['contents']);

            return [
                'path' => $targetPath,
                'size' => strlen($optimized['contents']),
                'mime_type' => $optimized['mime_type'],
                'extension' => $optimized['extension'],
            ];
        }

        $path = $disk->putFileAs(trim($directory, '/'), $file, basename($fileName));

        return [
            'path' => (string) $path,
            'size' => $file->getSize() ?: 0,
            'mime_type' => (string) $file->getClientMimeType(),
            'extension' => $extension,
        ];
    }

    private function optimizeImageBinary(UploadedFile $file, string $extension, int $maxDimension): ?array
    {
        if (! $this->canOptimize($extension)) {
            return null;
        }

        $sourcePath = $file->getRealPath();
        if (! is_string($sourcePath) || $sourcePath === '' || ! is_file($sourcePath)) {
            return null;
        }

        $imageInfo = @getimagesize($sourcePath);
        if (! is_array($imageInfo) || empty($imageInfo[0]) || empty($imageInfo[1])) {
            return null;
        }

        [$sourceWidth, $sourceHeight] = [$imageInfo[0], $imageInfo[1]];
        $resource = $this->createImageResource($sourcePath, $extension);

        if (! $resource) {
            return null;
        }

        $targetWidth = $sourceWidth;
        $targetHeight = $sourceHeight;

        if (max($sourceWidth, $sourceHeight) > $maxDimension) {
            $scale = $maxDimension / max($sourceWidth, $sourceHeight);
            $targetWidth = max(1, (int) round($sourceWidth * $scale));
            $targetHeight = max(1, (int) round($sourceHeight * $scale));
        }

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        if (! $canvas) {
            imagedestroy($resource);
            return null;
        }

        if (in_array($extension, ['png', 'webp'], true)) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $transparent);
        }

        imagecopyresampled(
            $canvas,
            $resource,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight
        );

        $binary = $this->encodeImageResource($canvas, $extension);

        imagedestroy($canvas);
        imagedestroy($resource);

        if ($binary === null) {
            return null;
        }

        return [
            'contents' => $binary,
            'mime_type' => match ($extension) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
                default => (string) $file->getClientMimeType(),
            },
            'extension' => $extension,
        ];
    }

    private function canOptimize(string $extension): bool
    {
        if (! function_exists('imagecreatetruecolor') || ! function_exists('imagecopyresampled')) {
            return false;
        }

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true);
    }

    private function createImageResource(string $path, string $extension): mixed
    {
        return match ($extension) {
            'jpg', 'jpeg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($path) : false,
            'png' => function_exists('imagecreatefrompng') ? @imagecreatefrompng($path) : false,
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };
    }

    private function encodeImageResource(mixed $resource, string $extension): ?string
    {
        ob_start();

        $encoded = match ($extension) {
            'jpg', 'jpeg' => function_exists('imagejpeg') ? @imagejpeg($resource, null, 76) : false,
            'png' => function_exists('imagepng') ? @imagepng($resource, null, 8) : false,
            'webp' => function_exists('imagewebp') ? @imagewebp($resource, null, 76) : false,
            default => false,
        };

        $contents = ob_get_clean();

        if (! $encoded || ! is_string($contents) || $contents === '') {
            return null;
        }

        return $contents;
    }
}
