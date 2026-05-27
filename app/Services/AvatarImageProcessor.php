<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class AvatarImageProcessor
{
    /**
     * Lädt ein Upload-Bild, skaliert es und speichert es komprimiert auf der public-Disk.
     *
     * @return string relativer Pfad (z. B. avatars/uuid.webp)
     */
    public function storeFromUpload(UploadedFile $file): string
    {
        if (! extension_loaded('gd')) {
            return $file->store('avatars', 'public');
        }

        $image = $this->loadImage($file);
        $maxEdge = (int) config('creator.avatar.max_edge_px', 512);
        $resized = $this->resizeToMaxEdge($image, $maxEdge);

        return $this->encodeAndStore($resized);
    }

    private function loadImage(UploadedFile $file): GdImage
    {
        $path = $file->getRealPath();
        if ($path === false) {
            throw new RuntimeException('Upload path missing');
        }

        $image = match ($file->getMimeType()) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/gif' => @imagecreatefromgif($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };

        if (! $image instanceof GdImage) {
            throw new RuntimeException('Unsupported or corrupt image');
        }

        return $image;
    }

    private function resizeToMaxEdge(GdImage $image, int $maxEdge): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width <= 0 || $height <= 0) {
            imagedestroy($image);

            throw new RuntimeException('Invalid image dimensions');
        }

        if ($width <= $maxEdge && $height <= $maxEdge) {
            return $image;
        }

        $ratio = min($maxEdge / $width, $maxEdge / $height);
        $newWidth = max(1, (int) round($width * $ratio));
        $newHeight = max(1, (int) round($height * $ratio));

        $canvas = imagecreatetruecolor($newWidth, $newHeight);
        if ($canvas === false) {
            imagedestroy($image);

            throw new RuntimeException('Could not allocate image');
        }

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        if ($transparent !== false) {
            imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, $transparent);
        }
        imagealphablending($canvas, true);

        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);

        return $canvas;
    }

    private function encodeAndStore(GdImage $image): string
    {
        $useWebp = function_exists('imagewebp');
        $extension = $useWebp ? 'webp' : 'jpg';
        $filename = 'avatars/'.Str::uuid()->toString().'.'.$extension;

        ob_start();
        $ok = $useWebp
            ? imagewebp($image, null, (int) config('creator.avatar.webp_quality', 85))
            : $this->encodeJpeg($image);
        $binary = ob_get_clean();
        imagedestroy($image);

        if (! $ok || $binary === false || $binary === '') {
            throw new RuntimeException('Could not encode image');
        }

        Storage::disk('public')->put($filename, $binary);

        return $filename;
    }

    private function encodeJpeg(GdImage $image): bool
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $canvas = imagecreatetruecolor($width, $height);
        if ($canvas === false) {
            return false;
        }

        $white = imagecolorallocate($canvas, 255, 255, 255);
        if ($white !== false) {
            imagefilledrectangle($canvas, 0, 0, $width, $height, $white);
        }
        imagecopy($canvas, $image, 0, 0, 0, 0, $width, $height);
        $quality = (int) config('creator.avatar.jpeg_quality', 85);
        $result = imagejpeg($canvas, null, $quality);
        imagedestroy($canvas);

        return $result;
    }
}
