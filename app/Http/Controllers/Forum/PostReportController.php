<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostReport;
use Illuminate\Http\Request;

class PostReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'check.active.user']);
    }

    public function store(Request $request, Post $post)
    {
        if ((int)$post->user_id === (int)$request->user()->id) {
            return response()->json(['success' => false, 'message' => 'You cannot report your own post.'], 422);
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:50'],
            'details' => ['nullable', 'string', 'max:2000'],
        ]);

        PostReport::create([
            'post_id' => $post->id,
            'reporter_user_id' => $request->user()->id,
            'reason' => $validated['reason'] ?? null,
            'details' => $validated['details'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json(['success' => true, 'message' => 'Report submitted.']);
    }
}
