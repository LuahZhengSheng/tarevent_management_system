<?php
// app/Decorators/MediaPostDecorator.php

namespace App\Decorators;

use App\Support\PostMediaHelper;
use Illuminate\Support\Facades\Log;

class MediaPostDecorator extends BasePostDecorator
{
    /**
     * Process media files
     *
     * @return array
     */
    public function process(): array
    {
        $data = parent::process();
        
        // Process media files if present
        if ($this->request->hasFile('media')) {
            try {
                $files = $this->request->file('media');
                
                // Validate total count
                if (count($files) > MediaHelper::POST_MAX_MEDIA_COUNT) {
                    throw new \Exception(
                        'Maximum ' . MediaHelper::POST_MAX_MEDIA_COUNT . ' media files allowed. ' .
                        'You uploaded ' . count($files) . ' files.'
                    );
                }
                
                // Process all media files (convert to JPEG/MP4, rename with UUID)
                $processedMedia = MediaHelper::processPostMedia($files);
                
                // Add to data
                $data['media_paths'] = $processedMedia;
                
                Log::info('Media processed successfully', [
                    'count' => count($processedMedia),
                    'types' => array_column($processedMedia, 'type'),
                    'total_size' => array_sum(array_column($processedMedia, 'size')),
                ]);
                
            } catch (\Exception $e) {
                Log::error('Media processing failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }
        }
        
        return $data;
    }
}
