<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Forum\PostMediaController;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostMediaController extends Controller
{
    public function show(Request $request, Post $post, int $index)
    {
        // 权限：用你 Post model 现成的 canBeViewedBy
        abort_unless($post->canBeViewedBy($request->user()), 403); // club_only 这里会挡住非成员 [file:127]

        $media = $post->media_paths[$index] ?? null;
        abort_unless(is_array($media), 404);

        $disk = $media['disk'] ?? 'public';
        $path = $media['path'] ?? null;
        abort_unless($path, 404);

        abort_unless(Storage::disk($disk)->exists($path), 404);

        return Storage::disk($disk)->response($path, null, [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
