<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfilePhotoService
{
    public function storeUploadedPhoto(UploadedFile $file): string
    {
        $binary = file_get_contents($file->getRealPath());
        return $this->storeBinary($binary);
    }

    public function storeBase64Photo(string $base64): string
    {
        $payload = preg_replace('/^data:image\/[a-zA-Z0-9.+-]+;base64,/', '', $base64);
        $binary = base64_decode($payload, true);
        if ($binary === false) {
            throw new \RuntimeException('Imagem base64 inválida.');
        }

        return $this->storeBinary($binary);
    }

    public function deleteIfExists(?string $path): void
    {
        if (!$path) {
            return;
        }

        Storage::disk($this->disk())->delete($path);
    }

    private function storeBinary(string $binary): string
    {
        $optimized = $this->optimizeImage($binary);
        $directory = $this->directory();
        $filename = $directory . '/' . Str::uuid()->toString() . '.jpg';

        Storage::disk($this->disk())->makeDirectory($directory);
        Storage::disk($this->disk())->put($filename, $optimized);

        return $filename;
    }

    private function optimizeImage(string $binary): string
    {
        if (!function_exists('imagecreatefromstring')) {
            return $binary;
        }

        $image = @imagecreatefromstring($binary);
        if ($image === false) {
            throw new \RuntimeException('Imagem inválida.');
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $maxPx = (int) config('avatars.max_px', 512);

        if ($width > $maxPx || $height > $maxPx) {
            $ratio = min($maxPx / $width, $maxPx / $height);
            $newWidth = (int) max(1, round($width * $ratio));
            $newHeight = (int) max(1, round($height * $ratio));

            $resized = imagecreatetruecolor($newWidth, $newHeight);
            $white = imagecolorallocate($resized, 255, 255, 255);
            imagefill($resized, 0, 0, $white);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $resized;
        }

        if (!function_exists('imagejpeg')) {
            imagedestroy($image);
            return $binary;
        }

        $quality = (int) config('avatars.jpeg_quality', 82);
        ob_start();
        imagejpeg($image, null, $quality);
        $output = ob_get_clean();
        imagedestroy($image);

        return $output !== false ? $output : $binary;
    }

    private function disk(): string
    {
        return (string) config('avatars.disk', 'public');
    }

    private function directory(): string
    {
        return (string) config('avatars.directory', 'avatars');
    }
}
