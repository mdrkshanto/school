<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Image\Enums\Format;
use Spatie\Image\Image;

/**
 * Stores uploaded images as optimised .webp files on a configurable disk
 * while preserving transparency. Uses spatie/image when a supported driver
 * (Imagick/Vips) is present, otherwise falls back to a transparent-safe GD path.
 */
class MediaService
{
    public function __construct(private readonly string $disk = 'local') {}

    /**
     * Store an uploaded image converted to webp. Returns the stored relative path.
     */
    public function storeImage(UploadedFile $file, string $directory = 'images', int $quality = 85): string
    {
        $filename = Str::random(40).'.webp';
        $relative = trim($directory, '/').'/'.$filename;

        $this->assertSupportedImage($file);

        $target = Storage::disk($this->disk)->path($relative);

        if (extension_loaded('imagick') || extension_loaded('vips')) {
            Image::load($file->getPathname())
                ->format(Format::Webp)
                ->quality($quality)
                ->save($target);
        } else {
            $this->convertWithGd($file, $target, $quality);
        }

        return $relative;
    }

    /**
     * Store a webp file already generated elsewhere (e.g. during seeding).
     */
    public function deleteImage(?string $relative): void
    {
        if ($relative && Storage::disk($this->disk)->exists($relative)) {
            Storage::disk($this->disk)->delete($relative);
        }
    }

    private function convertWithGd(UploadedFile $file, string $target, int $quality): void
    {
        $image = @imagecreatefromstring($file->getContent());

        if ($image === false) {
            throw new RuntimeException('The uploaded file is not a valid image.');
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);

        $dir = dirname($target);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        if (! imagewebp($image, $target, $quality)) {
            throw new RuntimeException('Failed to convert image to webp.');
        }

        imagedestroy($image);
    }

    private function assertSupportedImage(UploadedFile $file): void
    {
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif', 'image/bmp'];
        $mime = $file->getMimeType();

        abort_if(! in_array($mime, $allowed, true), 422, 'Unsupported image type.');
    }
}
