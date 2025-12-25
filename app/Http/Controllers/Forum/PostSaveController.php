<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostSave;
use Illuminate\Http\Request;

class PostSaveController extends Controller {

    public function __construct() {
        $this->middleware(['auth', 'check.active.user']);
    }

    public function toggle(Request $request, Post $post) {
        if (!$post->canBeViewedBy($request->user())) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        $userId = $request->user()->id;

        $existing = PostSave::where('post_id', $post->id)
                ->where('user_id', $userId)
                ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['success' => true, 'saved' => false]);
        }

        PostSave::create([
            'post_id' => $post->id,
            'user_id' => $userId,
        ]);

        return response()->json(['success' => true, 'saved' => true]);
    }
}
