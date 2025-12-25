<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\PostComment;
use App\Models\CommentLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LikeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'check.active.user']);
    }

    /**
     * Toggle like on a post
     */
    public function toggle(Post $post)
    {
        try {
            DB::beginTransaction();

            $userId = auth()->id();

            $like = PostLike::where('post_id', $post->id)
                ->where('user_id', $userId)
                ->first();

            if ($like) {
                // Unlike
                $like->delete();
                $post->decrement('likes_count');
                $liked   = false;
                $message = 'Post unliked';
            } else {
                // Like
                PostLike::create([
                    'post_id' => $post->id,
                    'user_id' => $userId,
                ]);

                $post->increment('likes_count');
                $liked   = true;
                $message = 'Post liked';
            }

            DB::commit();

            Log::info($message, [
                'post_id' => $post->id,
                'user_id' => $userId,
            ]);

            return response()->json([
                'success'     => true,
                'message'     => $message,
                'liked'       => $liked,
                'likes_count' => $post->fresh()->likes_count,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Like toggle failed', [
                'error'   => $e->getMessage(),
                'post_id' => $post->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update like status',
            ], 500);
        }
    }

    /**
     * Toggle like on a comment
     */
    public function toggleComment(PostComment $comment)
    {
        try {
            DB::beginTransaction();

            $userId = auth()->id();

            $like = CommentLike::where('comment_id', $comment->id)
                ->where('user_id', $userId)
                ->first();

            if ($like) {
                // Unlike
                $like->delete();
                $comment->decrement('likes_count');
                $liked   = false;
                $message = 'Comment unliked';
            } else {
                // Like
                CommentLike::create([
                    'comment_id' => $comment->id,
                    'user_id'    => $userId,
                    'created_at' => now(),
                ]);

                $comment->increment('likes_count');
                $liked   = true;
                $message = 'Comment liked';
            }

            DB::commit();

            Log::info($message, [
                'comment_id' => $comment->id,
                'user_id'    => $userId,
            ]);

            return response()->json([
                'success'     => true,
                'message'     => $message,
                'liked'       => $liked,
                'likes_count' => $comment->fresh()->likes_count,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Comment like toggle failed', [
                'error'      => $e->getMessage(),
                'comment_id' => $comment->id,
                'user_id'    => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update comment like status',
            ], 500);
        }
    }

    /**
     * Get users who liked a post
     */
    public function users(Post $post)
    {
        $likes = $post->likes()
            ->with('user')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'likes'   => $likes,
        ]);
    }
}
