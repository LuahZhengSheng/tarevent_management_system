<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'check.active.user']);
    }

    /**
     * Store a new comment
     */
    public function store(Request $request, Post $post)
    {
        $request->validate([
            'content' => 'required|string|min:1|max:1000',
            'parent_id' => 'nullable|exists:post_comments,id',
        ]);

        try {
            DB::beginTransaction();

            // Create comment
            $comment = PostComment::create([
                'post_id' => $post->id,
                'user_id' => auth()->id(),
                'parent_id' => $request->parent_id,
                'content' => $request->content,
            ]);

            // Update post comments count
            $post->increment('comments_count');

            DB::commit();

            Log::info('Comment created', [
                'comment_id' => $comment->id,
                'post_id' => $post->id,
                'user_id' => auth()->id(),
            ]);

            // Load relationships
            $comment->load('user');

            // Generate HTML for the comment
            $html = $this->generateCommentHtml($comment);

            return response()->json([
                'success' => true,
                'message' => 'Comment posted successfully',
                'html' => $html,
                'total_comments' => $post->comments_count,
                'comment' => $comment,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Comment creation failed', [
                'error' => $e->getMessage(),
                'post_id' => $post->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to post comment',
            ], 500);
        }
    }

    /**
     * Delete a comment
     */
    public function destroy(PostComment $comment)
    {
        // Authorization check
        if (!$comment->canBeEditedBy(auth()->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 403);
        }

        try {
            DB::beginTransaction();

            $post = $comment->post;
            
            // Count total comments to delete (including replies)
            $totalToDelete = 1 + $comment->replies()->count();

            // Delete comment (cascade will delete replies)
            $comment->delete();

            // Update post comments count
            $post->decrement('comments_count', $totalToDelete);

            DB::commit();

            Log::info('Comment deleted', [
                'comment_id' => $comment->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Comment deleted successfully',
                'total_comments' => $post->fresh()->comments_count,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Comment deletion failed', [
                'comment_id' => $comment->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete comment',
            ], 500);
        }
    }

    /**
     * Generate HTML for comment
     */
    protected function generateCommentHtml(PostComment $comment)
    {
        $isReply = $comment->parent_id !== null;
        $userBadge = '';
        
        if ($comment->user->hasRole('admin')) {
            $userBadge = '<span class="badge bg-danger ms-2">Admin</span>';
        } elseif ($comment->user->hasRole('club')) {
            $userBadge = '<span class="badge bg-info ms-2">Club Admin</span>';
        }

        if ($isReply) {
            // Reply HTML
            return <<<HTML
            <div class="reply-item d-flex gap-3" data-comment-id="{$comment->id}">
                <img src="{$comment->user->profile_photo_url}" 
                     alt="{$comment->user->name}" 
                     class="rounded-circle"
                     width="32"
                     height="32">
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>{$comment->user->name}</strong>
                            {$userBadge}
                            <div class="text-muted small">
                                {$comment->created_at->diffForHumans()}
                            </div>
                        </div>
                        <button class="btn btn-sm btn-link text-muted delete-comment-btn"
                                data-comment-id="{$comment->id}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <p class="mb-0 mt-1">{$comment->content}</p>
                </div>
            </div>
HTML;
        } else {
            // Main comment HTML
            return <<<HTML
            <div class="comment-item" data-comment-id="{$comment->id}">
                <div class="d-flex gap-3">
                    <img src="{$comment->user->profile_photo_url}" 
                         alt="{$comment->user->name}" 
                         class="rounded-circle"
                         width="40"
                         height="40">
                    <div class="flex-grow-1">
                        <div class="comment-content">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>{$comment->user->name}</strong>
                                    {$userBadge}
                                    <div class="text-muted small">
                                        {$comment->created_at->diffForHumans()}
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-link text-muted" 
                                            type="button" 
                                            data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <button class="dropdown-item text-danger delete-comment-btn"
                                                    data-comment-id="{$comment->id}">
                                                <i class="bi bi-trash me-2"></i>Delete
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <p class="mb-2 mt-2">{$comment->content}</p>
                            <div class="comment-actions">
                                <button type="button" 
                                        class="btn btn-sm btn-link text-muted reply-btn"
                                        data-comment-id="{$comment->id}">
                                    <i class="bi bi-reply me-1"></i>Reply
                                </button>
                            </div>
                        </div>
                        <div class="reply-input mt-3" id="replyInput{$comment->id}" style="display: none;">
                            <div class="d-flex gap-2">
                                <input type="text" 
                                       class="form-control form-control-sm" 
                                       placeholder="Write a reply..."
                                       id="replyText{$comment->id}">
                                <button type="button" 
                                        class="btn btn-sm btn-primary submit-reply-btn"
                                        data-comment-id="{$comment->id}">
                                    <i class="bi bi-send"></i>
                                </button>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-secondary cancel-reply-btn"
                                        data-comment-id="{$comment->id}">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
HTML;
        }
    }
}