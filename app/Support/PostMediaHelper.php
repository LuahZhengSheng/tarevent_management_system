<?php

namespace App\Support;

use App\Services\Security\VirusScanService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PostMediaHelper {

    public const POST_IMAGE_MAX_SIZE = 10; // MB
    public const POST_VIDEO_MAX_SIZE = 100; // MB
    public const POST_MAX_MEDIA_COUNT = 10;
    public const ALLOWED_IMAGE_TYPES = [
        'image/jpeg',
        'image/png',
        'image/jpg',
        'image/gif',
        'image/webp',
    ];
    public const ALLOWED_VIDEO_TYPES = [
        'video/mp4',
        'video/quicktime',
        'video/x-msvideo',
        'video/mpeg',
        'video/webm',
    ];

    /**
     * Process post media files.
     */
    public static function processPostMedia(array $files, string $disk = 'public'): array {
        $processed = [];

        foreach ($files as $file) {
            if (count($processed) >= self::POST_MAX_MEDIA_COUNT) {
                break;
            }

            if (!$file instanceof UploadedFile) {
                continue;
            }

            $mimeType = $file->getMimeType();

            if (str_starts_with($mimeType, 'image/')) {
                $processed[] = self::processPostImage($file, $disk);
                continue;
            }

            if (str_starts_with($mimeType, 'video/')) {
                $processed[] = self::processPostVideo($file, $disk);
                continue;
            }

            // DEV ONLY: allow scanning any file (do not store)
//            if (app()->environment('local')) {
//                app(\App\Services\Security\VirusScanService::class)->scanOrFail($file);
//
//                // optional: record something so you can see it passed scan
//                $processed[] = [
//                    'disk' => $disk,
//                    'type' => 'other',
//                    'path' => null,
//                    'mime_type' => $mimeType,
//                    'size' => $file->getSize(),
//                    'original_name' => $file->getClientOriginalName(),
//                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
//                ];
//            }
        }

        return $processed;
    }

    public static function processPostImage(UploadedFile $file, string $disk = 'public'): array {
        self::validatePostImage($file);

        // Virus scan (3rd party, fail closed): throws if not clean
        app(VirusScanService::class)->scanOrFail($file);

        $uuid = (string) Str::uuid();
        $filename = $uuid . '.jpeg';
        $path = 'posts/images/' . $filename;

        $manager = new ImageManager(new Driver());
        $image = $manager->read($file);

        if ($image->width() > 2000 || $image->height() > 2000) {
            $image->scale(width: 2000, height: 2000);
        }

        $encoded = $image->toJpeg(quality: 85);
        Storage::disk($disk)->put($path, (string) $encoded);

        return [
            'disk' => $disk,
            'type' => 'image',
            'path' => $path,
            'mime_type' => 'image/jpeg',
            'size' => Storage::disk($disk)->size($path),
            'original_name' => $file->getClientOriginalName(),
            'uuid' => $uuid,
        ];
    }

    public static function processPostVideo(UploadedFile $file, string $disk = 'public'): array {
        self::validatePostVideo($file);

        // Virus scan (3rd party, fail closed): throws if not clean
        app(VirusScanService::class)->scanOrFail($file);

        $uuid = (string) Str::uuid();

        // NOTE: This does not transcode; only renames to .mp4
        $filename = $uuid . '.mp4';
        $path = 'posts/videos/' . $filename;

        $file->storeAs('posts/videos', $filename, $disk);

        return [
            'disk' => $disk,
            'type' => 'video',
            'path' => $path,
            'mime_type' => 'video/mp4',
            'size' => Storage::disk($disk)->size($path),
            'original_name' => $file->getClientOriginalName(),
            'uuid' => $uuid,
        ];
    }

    public static function validatePostImage(UploadedFile $file): void {
        if (!$file->isValid()) {
            throw new \Exception('Invalid file upload.');
        }

        if (!in_array($file->getMimeType(), self::ALLOWED_IMAGE_TYPES)) {
            throw new \Exception('Invalid image type. Allowed: JPEG, PNG, JPG, GIF, WEBP.');
        }

        $maxSizeBytes = self::POST_IMAGE_MAX_SIZE * 1024 * 1024;
        if ($file->getSize() > $maxSizeBytes) {
            throw new \Exception('Image size exceeds ' . self::POST_IMAGE_MAX_SIZE . 'MB limit.');
        }

        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file);
            if (!$image) {
                throw new \Exception('File is not a valid image.');
            }
        } catch (\Exception $e) {
            throw new \Exception('File is not a valid image: ' . $e->getMessage());
        }
    }

    public static function validatePostVideo(UploadedFile $file): void {
        if (!$file->isValid()) {
            throw new \Exception('Invalid file upload.');
        }

        if (!in_array($file->getMimeType(), self::ALLOWED_VIDEO_TYPES)) {
            throw new \Exception('Invalid video type. Allowed: MP4, MOV, AVI, MPEG, WEBM.');
        }

        $maxSizeBytes = self::POST_VIDEO_MAX_SIZE * 1024 * 1024;
        if ($file->getSize() > $maxSizeBytes) {
            throw new \Exception('Video size exceeds ' . self::POST_VIDEO_MAX_SIZE . 'MB limit.');
        }
    }

    public static function deletePostMedia(array $mediaPaths): void {
        foreach ($mediaPaths as $media) {
            if (!is_array($media)) {
                continue;
            }

            $path = $media['path'] ?? null;
            $disk = $media['disk'] ?? 'public'; // old data compatible

            if ($path && Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        }
    }

    public static function formatBytes(int $bytes): string {
        return MediaHelper::formatBytes($bytes);
    }
}
