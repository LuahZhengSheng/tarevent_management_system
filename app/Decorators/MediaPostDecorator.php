<?php
// app/Decorators/MediaPostDecorator.php

namespace App\Decorators;

use App\Support\PostMediaHelper;
use Illuminate\Support\Facades\Log;

class MediaPostDecorator extends BasePostDecorator
{
    /**
     * Process media files
     */
    public function process(): array
    {
        $data = parent::process();

        if ($this->request->hasFile('media')) {
            try {
                $files = $this->request->file('media');

                // Validate total count
                if (count($files) > PostMediaHelper::POST_MAX_MEDIA_COUNT) {
                    throw new \Exception(
                        'Maximum ' . PostMediaHelper::POST_MAX_MEDIA_COUNT . ' media files allowed. ' .
                        'You uploaded ' . count($files) . ' files.'
                    );
                }

                // Decide storage disk based on visibility
                $visibility = $this->request->input('visibility', 'public');
                $disk = $visibility === 'club_only' ? 'local' : 'public';

                // Process all media files (UUID rename etc.)
                $processedMedia = PostMediaHelper::processPostMedia($files, $disk);

                $data['media_paths'] = $processedMedia;

                Log::info('Media processed successfully', [
                    'count' => count($processedMedia),
                    'disk' => $disk,
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
