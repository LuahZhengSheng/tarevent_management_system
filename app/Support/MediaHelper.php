<?php
// app/Helpers/MediaHelper.php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MediaHelper
{
    // Constants for post media
    const POST_IMAGE_MAX_SIZE = 10; // MB
    const POST_VIDEO_MAX_SIZE = 100; // MB
    const POST_MAX_MEDIA_COUNT = 10;
    
    const ALLOWED_IMAGE_TYPES = [
        'image/jpeg',
        'image/png',
        'image/jpg',
        'image/gif',
        'image/webp',
    ];
    
    const ALLOWED_VIDEO_TYPES = [
        'video/mp4',
        'video/quicktime',
        'video/x-msvideo',
        'video/mpeg',
    ];

    /**
     * Process post media files (images to JPEG, videos to MP4)
     */
    public static function processPostMedia(array $files): array
    {
        $processedMedia = [];
        $imageCount = 0;
        $videoCount = 0;

        foreach ($files as $file) {
            if (count($processedMedia) >= self::POST_MAX_MEDIA_COUNT) {
                break;
            }

            $mimeType = $file->getMimeType();
            
            if (str_starts_with($mimeType, 'image/')) {
                $imageCount++;
                if ($imageCount > self::POST_MAX_MEDIA_COUNT) continue;
                
                $result = self::processPostImage($file);
                $processedMedia[] = $result;
                
            } elseif (str_starts_with($mimeType, 'video/')) {
                $videoCount++;
                if ($videoCount > self::POST_MAX_MEDIA_COUNT) continue;
                
                $result = self::processPostVideo($file);
                $processedMedia[] = $result;
            }
        }

        return $processedMedia;
    }

    /**
     * Process and convert image to JPEG with UUID naming
     */
    public static function processPostImage(UploadedFile $file): array
    {
        // Validate image
        self::validatePostImage($file);
        
        // Generate UUID filename
        $uuid = Str::uuid()->toString();
        $filename = $uuid . '.jpeg';
        $path = 'posts/images/' . $filename;
        
        // Process image
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file);
        
        // Resize if too large (max 2000x2000)
        if ($image->width() > 2000 || $image->height() > 2000) {
            $image->scale(width: 2000, height: 2000);
        }
        
        // Convert to JPEG and save
        $encoded = $image->toJpeg(quality: 85);
        Storage::disk('public')->put($path, (string) $encoded);
        
        return [
            'type' => 'image',
            'path' => $path,
            'mime_type' => 'image/jpeg',
            'size' => Storage::disk('public')->size($path),
            'original_name' => $file->getClientOriginalName(),
            'uuid' => $uuid,
        ];
    }

    /**
     * Process video (convert to MP4 if needed, or just store with UUID)
     */
    public static function processPostVideo(UploadedFile $file): array
    {
        // Validate video
        self::validatePostVideo($file);
        
        // Generate UUID filename
        $uuid = Str::uuid()->toString();
        
        // For now, we'll just rename to MP4 (actual conversion requires FFmpeg)
        // In production, you should use FFmpeg to convert videos
        $filename = $uuid . '.mp4';
        $path = 'posts/videos/' . $filename;
        
        // Store video
        $file->storeAs('posts/videos', $filename, 'public');
        
        return [
            'type' => 'video',
            'path' => $path,
            'mime_type' => 'video/mp4',
            'size' => Storage::disk('public')->size($path),
            'original_name' => $file->getClientOriginalName(),
            'uuid' => $uuid,
        ];
    }

    /**
     * Validate image for posts
     */
    public static function validatePostImage(UploadedFile $file): void
    {
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

    /**
     * Validate video for posts
     */
    public static function validatePostVideo(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new \Exception('Invalid file upload.');
        }

        if (!in_array($file->getMimeType(), self::ALLOWED_VIDEO_TYPES)) {
            throw new \Exception('Invalid video type. Allowed: MP4, MOV, AVI, MPEG.');
        }

        $maxSizeBytes = self::POST_VIDEO_MAX_SIZE * 1024 * 1024;
        if ($file->getSize() > $maxSizeBytes) {
            throw new \Exception('Video size exceeds ' . self::POST_VIDEO_MAX_SIZE . 'MB limit.');
        }
    }

    /**
     * Delete post media files
     */
    public static function deletePostMedia(array $mediaPaths): void
    {
        foreach ($mediaPaths as $media) {
            if (isset($media['path']) && Storage::disk('public')->exists($media['path'])) {
                Storage::disk('public')->delete($media['path']);
            }
        }
    }

    /**
     * Format bytes to human readable
     */
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
