<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller {

    public const MAX_MEDIA_COUNT = 5;
    public const MAX_IMAGE_BYTES = 10 * 1024 * 1024;   // 10MB
    public const MAX_VIDEO_BYTES = 100 * 1024 * 1024;  // 100MB

    public function __construct() {
        // 默认所有动作都需要登录，但 listReplies 例外（用于访客查看回复）
        $this->middleware(['auth', 'check.active.user'])
                ->except('listReplies');
    }

    /**
     * Store a new comment / reply (same endpoint)
     */
    public function store(Request $request, Post $post) {
        if (!$post->canBeViewedBy($request->user())) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        $request->validate([
            'content' => ['nullable', 'string', 'max:2000'],
            'parent_id' => ['nullable', 'exists:post_comments,id'],
            'reply_to_user_id' => ['nullable', 'exists:users,id'],
            'media' => ['nullable'],
            'media.*' => ['file'], // size 在下面手动校验（图片/视频上限不同）
        ]);

        $content = trim((string) $request->input('content', ''));

        if ($content === '' && !$request->hasFile('media')) {
            return response()->json([
                        'success' => false,
                        'message' => 'Comment cannot be empty.',
                            ], 422);
        }

        // parent 必须属于同一 post
        $parent = null;
        if ($request->filled('parent_id')) {
            $parent = PostComment::where('id', $request->parent_id)
                    ->where('post_id', $post->id)
                    ->firstOrFail();
        }

        // reply_to_user_id：默认 parent 作者
        // 只有前端明确传了 reply_to_user_id 才 @ 某人
        $replyToUserId = $request->input('reply_to_user_id');

        // media validation + upload
        $mediaPaths = [];
        if ($request->hasFile('media')) {
            $files = $request->file('media');

            if (count($files) > self::MAX_MEDIA_COUNT) {
                return response()->json([
                            'success' => false,
                            'message' => 'Too many media files (max ' . self::MAX_MEDIA_COUNT . ').',
                                ], 422);
            }

            foreach ($files as $file) {
                $mime = $file->getMimeType() ?? '';
                $size = $file->getSize() ?? 0;

                $isVideo = str_starts_with($mime, 'video/');
                $isImage = str_starts_with($mime, 'image/');

                if (!$isVideo && !$isImage) {
                    return response()->json([
                                'success' => false,
                                'message' => 'Only image/video files are allowed.',
                                    ], 422);
                }

                if ($isImage && $size > self::MAX_IMAGE_BYTES) {
                    return response()->json([
                                'success' => false,
                                'message' => 'Image exceeds 10MB.',
                                    ], 422);
                }

                if ($isVideo && $size > self::MAX_VIDEO_BYTES) {
                    return response()->json([
                                'success' => false,
                                'message' => 'Video exceeds 100MB.',
                                    ], 422);
                }

                $path = $file->store('comments/media', 'public');

                $mediaPaths[] = [
                    'path' => $path,
                    'type' => $isVideo ? 'video' : 'image',
                    'mime_type' => $mime,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $size,
                ];
            }
        }

        try {
            DB::beginTransaction();

            $comment = PostComment::create([
                        'post_id' => $post->id,
                        'user_id' => auth()->id(),
                        'parent_id' => $parent?->id,
                        'reply_to_user_id' => $replyToUserId,
                        'content' => $content,
                        'media_paths' => !empty($mediaPaths) ? $mediaPaths : null,
            ]);

            // 整个贴子的 comments_count +1
            $post->increment('comments_count');

            // 如果是 reply，父评论的 replies_count 也要 +1
            $parentRepliesCount = null;
            $parentId = $comment->parent_id;

            if ($parentId) {
                PostComment::where('id', $parentId)->increment('replies_count');
                $parentRepliesCount = PostComment::where('id', $parentId)->value('replies_count');
            }

            DB::commit();

            Log::info('Comment created', [
                'comment_id' => $comment->id,
                'post_id' => $post->id,
                'user_id' => auth()->id(),
                'parent_id' => $comment->parent_id,
                'reply_to_user_id' => $comment->reply_to_user_id,
            ]);

            $comment->load(['user', 'replyTo', 'replies.user', 'replies.replyTo']);

            $html = view('forums.partials.comment_item', [
                'comment' => $comment,
                'isReply' => $comment->parent_id !== null,
                    ])->render();

            return response()->json([
                        'success' => true,
                        'message' => 'Comment posted successfully',
                        'html' => $html,
                        'totalComments' => $post->fresh()->comments_count,
                        'parentRepliesCount' => $parentRepliesCount, // 用于前端更新 View X replies
                        'parentId' => $parentId,
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

    public function destroy(PostComment $comment) {
        if (!$comment->canBeEditedBy(auth()->user())) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        try {
            DB::beginTransaction();

            $post = $comment->post;
            $parent = $comment->parent;       // 父评论（如果这条是 reply）
            $isReply = $parent !== null;

            // 统计要删的总条数（这条 + 它的子回复）
            $totalToDelete = 1 + $comment->replies()->count();

            $comment->delete(); // replies cascade by FK parent_id -> post_comments
            // 整个贴子的 comments_count 减掉
            $post->decrement('comments_count', $totalToDelete);

            // 如果删的是 reply，则父评论的 replies_count 也要减掉 1
            $parentRepliesCount = null;
            $parentId = null;

            if ($isReply) {
                $parentId = $parent->id;

                // 直接重算更稳：当前这条 parent 还有多少子回复
                $currentReplies = PostComment::where('post_id', $post->id)
                        ->where('parent_id', $parentId)
                        ->count();

                PostComment::where('id', $parentId)->update(['replies_count' => $currentReplies]);
                $parentRepliesCount = $currentReplies;
            }

            DB::commit();

            return response()->json([
                        'success' => true,
                        'message' => 'Comment deleted successfully',
                        'totalComments' => $post->fresh()->comments_count,
                        'parentRepliesCount' => $parentRepliesCount,
                        'parentId' => $parentId,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Comment deletion failed', [
                'comment_id' => $comment->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => 'Failed to delete comment'], 500);
        }
    }

    public function listReplies(Request $request, Post $post, PostComment $comment) {
        $user = $request->user();
        if (!$post->canBeViewedBy($user)) {
            return response()->json([
                        'success' => false,
                        'message' => 'Forbidden.',
                            ], 403);
        }

        $perPage = 5;
        $page = max(1, (int) $request->query('page', 1));

        $replies = PostComment::where('post_id', $post->id)
                ->where('parent_id', $comment->id)
                ->with(['user', 'replyTo'])
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page);

        $html = '';
        foreach ($replies as $reply) {
            $html .= view('forums.partials.comment_item', [
                'comment' => $reply,
                'isReply' => true,
                    ])->render();
        }

        return response()->json([
                    'success' => true,
                    'html' => $html,
                    'current_page' => $replies->currentPage(),
                    'last_page' => $replies->lastPage(),
                    'replies_count' => $comment->replies_count,
        ]);
    }

    public function listTopLevel(Request $request, Post $post) {
        $user = $request->user();
        if (!$post->canBeViewedBy($user)) {
            return response()->json([
                        'success' => false,
                        'message' => 'Forbidden.',
                            ], 403);
        }

        $sort = $request->query('sort', 'recent'); // recent / popular

        $query = PostComment::where('post_id', $post->id)
                ->whereNull('parent_id')
                ->withCount(['likes', 'replies'])
                ->with(['user']);

        if ($sort === 'popular') {
            $query->orderByDesc('likes_count')
                    ->orderByDesc('replies_count')
                    ->orderByDesc('created_at');
        } else {
            // most recent
            $query->orderByDesc('created_at');
        }

        $comments = $query->get();

        $html = '';
        foreach ($comments as $comment) {
            $html .= view('forums.partials.comment_item', [
                'comment' => $comment,
                'isReply' => false,
                    ])->render();
        }

        return response()->json([
                    'success' => true,
                    'html' => $html,
        ]);
    }

    public function update(Request $request, PostComment $comment) {
        $user = $request->user();

        if (!$comment->canBeEditedBy($user)) {
            return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized access.',
                            ], 403);
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $content = trim($validated['content']);

        if ($content === '') {
            return response()->json([
                        'success' => false,
                        'message' => 'Comment cannot be empty.',
                            ], 422);
        }

        $comment->content = $content;
        $comment->save();

        // 重新加载关系，保证 Blade 里能用 user / replyTo 等
        $comment->load(['user', 'replyTo', 'replies.user', 'replies.replyTo']);

        // 返回渲染好的 HTML，前端直接 replace 节点
        $html = view('forums.partials.comment_item', [
            'comment' => $comment,
            'isReply' => $comment->parent_id !== null,
                ])->render();

        return response()->json([
                    'success' => true,
                    'message' => 'Comment updated successfully',
                    'html' => $html,
        ]);
    }
}
