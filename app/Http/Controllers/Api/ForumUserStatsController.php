<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\PostSave;
use App\Models\PostComment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ForumUserStatsController extends Controller
{
    /**
     * REST API: 获取用户论坛统计
     * GET /api/v1/forum/user-stats?userId=1&timeStamp=2025-12-23 17:30:00&page=1&perPage=10
     * (IFA: requestId OR timeStamp 至少一个)
     */
    public function getUserForumStats(Request $request)
    {
        // 为了让所有 log 都能关联同一笔请求：优先用 requestId，否则用 timeStamp，否则给一个 fallback
        $rid = $request->input('requestId') ?: ($request->input('timeStamp') ?: ('NO-RID-' . Carbon::now()->format('YmdHis')));

        // 记录：请求进入
        Log::info('forum.user-stats request.in', [
            'requestId' => $rid,
            'path' => $request->path(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'userAgent' => $request->userAgent(),
            'query' => $request->query(),
        ]);

        // 1) Validate
        $validated = $request->validate([
            'userId'    => ['required', 'integer'],
            'requestId' => ['nullable', 'string'],
            'timeStamp' => ['nullable', 'date_format:Y-m-d H:i:s'],

            // pagination (optional)
            'perPage'   => ['nullable', 'integer', 'min:1'],
            'page'      => ['nullable', 'integer', 'min:1'],
        ]);

        $userId    = (int) $validated['userId'];
        $requestId = $validated['requestId'] ?? null;
        $requestTs = $validated['timeStamp'] ?? null;

        // 额外 IFA：requestId 或 timeStamp 至少一个
        if (empty($requestId) && empty($requestTs)) {
            Log::warning('forum.user-stats validation.fail.missing_requestId_or_timeStamp', [
                'requestId' => $rid,
                'userId' => $userId,
            ]);

            return response()->json([
                'status'    => 'F',
                'timeStamp' => Carbon::now()->format('Y-m-d H:i:s'),
                'requestId' => $requestId,
                'message'   => 'Missing requestId or timeStamp.',
            ], 422);
        }

        try {
            // 2) AUTHZ：只有 admin / super_admin 可调用
            $actor = $request->user(); // auth:sanctum 下可取到

            Log::info('forum.user-stats auth.actor', [
                'requestId' => $rid,
                'actorId' => optional($actor)->id,
                'actorEmail' => optional($actor)->email,
                'actorRole' => optional($actor)->role,
            ]);

            if (!$actor || !$actor->isAdministrator()) {
                Log::warning('forum.user-stats forbidden.admin_only', [
                    'requestId' => $rid,
                    'actorId' => optional($actor)->id,
                    'actorRole' => optional($actor)->role,
                    'targetUserId' => $userId,
                ]);

                return response()->json([
                    'status'      => 'F',
                    'timeStamp'   => Carbon::now()->format('Y-m-d H:i:s'),
                    'requestId'   => $requestId,
                    'requestTime' => $requestTs,
                    'message'     => 'Forbidden. Admin only.',
                ], 403);
            }

            // 3) 目标用户存在性
            $user = User::find($userId);
            if (!$user) {
                Log::warning('forum.user-stats not_found.user', [
                    'requestId' => $rid,
                    'actorId' => $actor->id,
                    'targetUserId' => $userId,
                ]);

                return response()->json([
                    'status'      => 'F',
                    'timeStamp'   => Carbon::now()->format('Y-m-d H:i:s'),
                    'requestId'   => $requestId,
                    'requestTime' => $requestTs,
                    'message'     => 'User not found.',
                ], 404);
            }

            // 4) admin 只能看 role=student / role=club；super_admin 可看所有
            if ($actor->isAdmin() && !in_array($user->role, ['student', 'club'])) {
                Log::warning('forum.user-stats forbidden.admin_scope', [
                    'requestId' => $rid,
                    'actorId' => $actor->id,
                    'actorRole' => $actor->role,
                    'targetUserId' => $userId,
                    'targetRole' => $user->role,
                ]);

                return response()->json([
                    'status'      => 'F',
                    'timeStamp'   => Carbon::now()->format('Y-m-d H:i:s'),
                    'requestId'   => $requestId,
                    'requestTime' => $requestTs,
                    'message'     => 'Forbidden. Admin can only view student/club users.',
                ], 403);
            }

            // 5) 发帖总数
            $totalPostCount = Post::where('user_id', $userId)->count();

            // 6) 收到的点赞总数（该用户所有帖子被点赞的次数）
            $totalLikeCount = PostLike::whereHas('post', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->count();

            // 7) 被保存总数（该用户所有帖子被收藏的次数）
            $totalSaveCount = PostSave::whereHas('post', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->count();

            // 8) 评论总数（该用户发出的评论数量）
            $totalCommentCount = PostComment::where('user_id', $userId)->count();

            // 9) Comments pagination (default 10, max 50)
            $perPage = (int) ($request->input('perPage', 10));
            $perPage = max(1, min(50, $perPage)); // server-side cap

            $commentsPaginator = PostComment::with('post:id,title')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            $comments = collect($commentsPaginator->items())->map(function (PostComment $comment) {
                return [
                    'commentId' => $comment->id,
                    'postId'    => $comment->post_id,
                    'postTitle' => optional($comment->post)->title,
                    'content'      => $comment->content,
                    'createdAt' => $comment->created_at?->format('Y-m-d H:i:s'),
                ];
            });

            // 10) 成功回传
            $resp = [
                'status'      => 'S',
                'timeStamp'   => Carbon::now()->format('Y-m-d H:i:s'),
                'requestId'   => $requestId,
                'requestTime' => $requestTs,
                'userId'      => $userId,
                'forumStats'  => [
                    'totalPostCount'    => $totalPostCount,
                    'totalLikeCount'    => $totalLikeCount,
                    'totalSaveCount'    => $totalSaveCount,
                    'totalCommentCount' => $totalCommentCount,
                    'comments' => $comments,
                    'commentsMeta' => [
                        'currentPage' => $commentsPaginator->currentPage(),
                        'lastPage'    => $commentsPaginator->lastPage(),
                        'total'       => $commentsPaginator->total(),
                        'perPage'     => $commentsPaginator->perPage(),
                    ],
                ],
            ];

            Log::info('forum.user-stats response.out', [
                'requestId' => $rid,
                'status' => $resp['status'],
                'targetUserId' => $userId,
                'counts' => [
                    'posts' => $totalPostCount,
                    'likes' => $totalLikeCount,
                    'saves' => $totalSaveCount,
                    'comments' => $totalCommentCount,
                ],
                'page' => $commentsPaginator->currentPage(),
                'perPage' => $commentsPaginator->perPage(),
                'commentsReturned' => count($comments),
            ]);

            return response()->json($resp);
        } catch (\Throwable $e) {
            Log::error('forum.user-stats exception', [
                'requestId' => $rid,
                'targetUserId' => $request->input('userId'),
                'actorId' => optional($request->user())->id,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            return response()->json([
                'status'      => 'E',
                'timeStamp'   => Carbon::now()->format('Y-m-d H:i:s'),
                'requestId'   => $requestId ?? null,
                'requestTime' => $requestTs ?? null,
                'message'     => 'Internal server error',
                'error'       => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
