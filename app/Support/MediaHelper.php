<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MediaHelper
{
    /**
     * Allowed image MIME types
     */
    public const ALLOWED_IMAGE_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    /**
     * Allowed video MIME types
     */
    public const ALLOWED_VIDEO_TYPES = [
        'video/mp4',
        'video/mpeg',
        'video/quicktime',
        'video/x-msvideo',
        'video/webm',
    ];

    /**
     * Default max file sizes (in MB)
     */
    public const DEFAULT_IMAGE_MAX_SIZE = 5;
    public const DEFAULT_VIDEO_MAX_SIZE = 50;

    protected static function imageManager(): ImageManager
    {
        return new ImageManager(new Driver());
    }

    /**
     * Process and store an uploaded image
     *
     * @param UploadedFile $file
     * @param string $directory Directory within storage/public
     * @param array $options
     * @return array ['path' => string, 'url' => string, 'metadata' => array]
     */
    public static function processImage(
        UploadedFile $file,
        string $directory = 'images',
        array $options = []
    ): array {
        $config = array_merge([
            'max_size' => self::DEFAULT_IMAGE_MAX_SIZE,
            'max_width' => 2000,
            'max_height' => 2000,
            'quality' => 85,
            'format' => 'jpg', // jpg, png, webp
            'disk' => 'public',
            'compress' => true,
            'thumbnail' => false,
            'thumbnail_width' => 300,
            'thumbnail_height' => 300,
        ], $options);

        self::validateImage($file, $config['max_size']);

        $filename = self::generateFilename($file, $config['format']);
        $path = $directory . '/' . $filename;

        $manager = self::imageManager();
        $image = $manager->read($file);

        if ($image->width() > $config['max_width'] || $image->height() > $config['max_height']) {
            // 保持你原本的 resize 逻辑
            $image->resize($config['max_width'], $config['max_height'], function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        if ($config['compress']) {
            switch ($config['format']) {
                case 'webp':
                    $encoded = $image->toWebp($config['quality']);
                    break;
                case 'png':
                    $encoded = $image->toPng(); // png 一般不带 quality
                    break;
                case 'jpg':
                default:
                    $encoded = $image->toJpeg($config['quality']);
                    break;
            }
        } else {
            $encoded = $image->toJpeg();
        }

        Storage::disk($config['disk'])->put($path, (string) $encoded);

        $result = [
            'filename' => $filename,
            'path' => $path,
            'url' => Storage::disk($config['disk'])->url($path),
            'metadata' => [
                'width' => $image->width(),
                'height' => $image->height(),
                'size' => Storage::disk($config['disk'])->size($path),
                'mime_type' => Storage::disk($config['disk'])->mimeType($path),
            ],
        ];

        if ($config['thumbnail']) {
            $thumbnailPath = self::createThumbnail(
                $image,
                $directory,
                $filename,
                $config['thumbnail_width'],
                $config['thumbnail_height'],
                $config['disk']
            );

            $result['thumbnail_filename'] = basename($thumbnailPath);
            $result['thumbnail_path'] = $thumbnailPath;
            $result['thumbnail_url'] = Storage::disk($config['disk'])->url($thumbnailPath);
        }

        return $result;
    }

    /**
     * Process and store an uploaded video
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param array $options
     * @return array
     */
    public static function processVideo(
        UploadedFile $file,
        string $directory = 'videos',
        array $options = []
    ): array {
        $config = array_merge([
            'max_size' => self::DEFAULT_VIDEO_MAX_SIZE,
            'disk' => 'public',
            'generate_thumbnail' => true,
        ], $options);

        self::validateVideo($file, $config['max_size']);

        $extension = $file->getClientOriginalExtension();
        $filename = Str::random(40) . '.' . $extension;
        $path = $directory . '/' . $filename;

        $file->storeAs($directory, $filename, $config['disk']);

        $result = [
            'path' => $path,
            'url' => Storage::disk($config['disk'])->url($path),
            'metadata' => [
                'size' => Storage::disk($config['disk'])->size($path),
                'mime_type' => Storage::disk($config['disk'])->mimeType($path),
                'extension' => $extension,
            ],
        ];

        // Generate video thumbnail (requires FFmpeg)
        if ($config['generate_thumbnail'] && extension_loaded('ffmpeg')) {
            // placeholder
            $result['thumbnail_path'] = null;
            $result['thumbnail_url'] = null;
        }

        return $result;
    }

    /**
     * Validate image file
     */
    public static function validateImage(UploadedFile $file, float $maxSizeMB = null): void
    {
        $maxSizeMB = $maxSizeMB ?? self::DEFAULT_IMAGE_MAX_SIZE;

        if (!$file->isValid()) {
            throw new \Exception('Invalid file upload.');
        }

        if (!in_array($file->getMimeType(), self::ALLOWED_IMAGE_TYPES)) {
            throw new \Exception('Invalid file type. Allowed types: ' . implode(', ', self::ALLOWED_IMAGE_TYPES));
        }

        $maxSizeBytes = $maxSizeMB * 1024 * 1024;
        if ($file->getSize() > $maxSizeBytes) {
            throw new \Exception("File size exceeds {$maxSizeMB}MB limit.");
        }

        try {
            $manager = self::imageManager();
            $image = $manager->read($file);
            if (!$image) {
                throw new \Exception('File is not a valid image.');
            }
        } catch (\Exception $e) {
            throw new \Exception('File is not a valid image: ' . $e->getMessage());
        }
    }

    /**
     * Validate video file
     */
    public static function validateVideo(UploadedFile $file, float $maxSizeMB = null): void
    {
        $maxSizeMB = $maxSizeMB ?? self::DEFAULT_VIDEO_MAX_SIZE;

        if (!$file->isValid()) {
            throw new \Exception('Invalid file upload.');
        }

        if (!in_array($file->getMimeType(), self::ALLOWED_VIDEO_TYPES)) {
            throw new \Exception('Invalid video type. Allowed types: ' . implode(', ', self::ALLOWED_VIDEO_TYPES));
        }

        $maxSizeBytes = $maxSizeMB * 1024 * 1024;
        if ($file->getSize() > $maxSizeBytes) {
            throw new \Exception("Video size exceeds {$maxSizeMB}MB limit.");
        }
    }

    protected static function generateFilename(UploadedFile $file, string $format = 'jpg'): string
    {
        $timestamp = now()->format('Ymd_His');
        $random = Str::random(8);
        return "{$timestamp}_{$random}.{$format}";
    }

    protected static function createThumbnail(
        $image,
        string $directory,
        string $filename,
        int $width,
        int $height,
        string $disk
    ): string {
        $thumbnail = clone $image;

        $thumbnail->cover($width, $height);

        $thumbnailFilename = 'thumb_' . $filename;
        $thumbnailPath = $directory . '/' . $thumbnailFilename;

        Storage::disk($disk)->put($thumbnailPath, (string) $thumbnail->toJpeg(80));

        return $thumbnailPath;
    }

    /**
     * Delete image and its thumbnail
     */
    public static function deleteImage(string $path, string $disk = 'public'): bool
    {
        $deleted = Storage::disk($disk)->delete($path);

        $directory = dirname($path);
        $filename = basename($path);
        $thumbnailPath = $directory . '/thumb_' . $filename;

        if (Storage::disk($disk)->exists($thumbnailPath)) {
            Storage::disk($disk)->delete($thumbnailPath);
        }

        return $deleted;
    }

    public static function getSupportedFormats(): array
    {
        return [
            'jpg' => [
                'mime' => 'image/jpeg',
                'quality_range' => [60, 100],
                'recommended_quality' => 85,
                'supports_transparency' => false,
            ],
            'png' => [
                'mime' => 'image/png',
                'quality_range' => [0, 9],
                'recommended_quality' => null,
                'supports_transparency' => true,
            ],
            'webp' => [
                'mime' => 'image/webp',
                'quality_range' => [0, 100],
                'recommended_quality' => 85,
                'supports_transparency' => true,
            ],
        ];
    }

    public static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }
}
