<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Club;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ClubPostFeedController extends Controller
{
    public function index(Request $request, Club $club)
    {
        // ---- IFA mandatory: requestId OR timeStamp ----
        $requestId = $request->input('requestId');
        $requestTs = $request->input('timeStamp');

        // 给日志用的追踪 ID（优先 requestId，其次 timeStamp，否则 fallback）
        $rid = $requestId ?: ($requestTs ?: ('NO-RID-' . Carbon::now()->format('YmdHis')));

        Log::info('club.posts request.in', [
            'requestId' => $rid,
            'clubId' => $club->id,
            'path' => $request->path(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'query' => $request->query(),
            'actorId' => optional($request->user())->id,
        ]);

        if (empty($requestId) && empty($requestTs)) {
            Log::warning('club.posts validation.fail.missing_requestId_or_timeStamp', [
                'requestId' => $rid,
                'clubId' => $club->id,
            ]);

            return response()->json([
                'status'    => 'F',
                'timeStamp' => now()->format('Y-m-d H:i:s'),
                'requestId' => $requestId,
                'message'   => 'Missing requestId or timeStamp.',
            ], 422);
        }

        // 可选：把请求的 timeStamp 回显（跟你的 forum-user-stats 一致）
        // 这样报告会更统一：response timeStamp + requestTime
        $requestTime = $requestTs;

        try {
            // ---- AUTHZ: only club members can view ----
            if (!$this->isClubMember($request, $club)) {
                Log::warning('club.posts forbidden.not_member', [
                    'requestId' => $rid,
                    'clubId' => $club->id,
                    'actorId' => optional($request->user())->id,
                ]);

                return response()->json([
                    'status'      => 'F',
                    'timeStamp'   => now()->format('Y-m-d H:i:s'),
                    'requestId'   => $requestId,
                    'requestTime' => $requestTime,
                    'message'     => 'Forbidden. Only club members can view club posts.',
                ], 403);
            }

            // ---- Build query: club_posts pivot + forum filters ----
            $query = Post::published()
                ->with(['user', 'category', 'tags', 'clubs'])
                ->withCount(['comments', 'likes'])
                ->where('visibility', 'club_only')
                ->whereHas('clubs', function ($q) use ($club) {
                    $q->where('clubs.id', $club->id)
                      ->where('club_posts.status', 'active');
                });

            // Category filter
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->input('category_id'));
            }

            // Tags filter (multi-select, AND logic), compatible with legacy tag
            $selectedTags = $request->input('tags', []);
            if (!is_array($selectedTags)) {
                $selectedTags = [$selectedTags];
            }
            if (empty($selectedTags) && $request->filled('tag')) {
                $selectedTags = [$request->input('tag')];
            }

            $selectedTags = collect($selectedTags)
                ->filter(fn ($v) => is_string($v) && trim($v) !== '')
                ->unique()
                ->values()
                ->all();

            if (!empty($selectedTags)) {
                foreach ($selectedTags as $slug) {
                    $query->whereHas('tags', function ($tq) use ($slug) {
                        $tq->where('tags.slug', $slug);
                    });
                }
            }

            // Search (search or q)
            $searchText = $request->get('search', $request->get('q', ''));
            if (!empty(trim($searchText))) {
                $searchText = trim($searchText);
                $query->where(function ($q) use ($searchText) {
                    $q->where('title', 'like', "%{$searchText}%")
                        ->orWhere('content', 'like', "%{$searchText}%")
                        ->orWhereHas('user', function ($uq) use ($searchText) {
                            $uq->where('name', 'like', "%{$searchText}%");
                        })
                        ->orWhereHas('tags', function ($tq) use ($searchText) {
                            $tq->where('tags.name', 'like', "%{$searchText}%")
                               ->orWhere('tags.slug', 'like', "%{$searchText}%");
                        });
                });
            }

            // Sorting
            $sortBy = $request->get('sort', 'recent');
            if ($sortBy === 'popular') {
                $query->popular();
            } else {
                $query->recent();
            }

            // Pagination
            $perPage = (int) ($request->input('perPage', 15));
            $perPage = max(1, min(50, $perPage));

            $posts = $query->paginate($perPage)->withQueryString();

            // Optional: provide categories + trending tags for sidebar reuse
            $categories = Category::active()->ordered()->get();
            $popularTags = Tag::active()->popular(10)->get();

            // 注意：这个接口返回的是 HTML 片段（不是纯 JSON 数据）
            $postsHtml = view('forums.partials.posts_page', ['posts' => $posts])->render();
            $summaryHtml = view('forums.partials.results_summary_ajax', [
                'posts' => $posts,
                'categories' => $categories,
                'selectedTags' => $selectedTags,
                'searchText' => $searchText,
            ])->render();
            $trendingHtml = view('forums.partials.trending_tags_ajax', [
                'popularTags' => $popularTags,
                'selectedTags' => $selectedTags,
            ])->render();

            $resp = [
                'status'      => 'S',
                'timeStamp'   => now()->format('Y-m-d H:i:s'),
                'requestId'   => $requestId,
                'requestTime' => $requestTime,
                'clubId'      => $club->id,
                'data' => [
                    'posts_html' => $postsHtml,
                    'summary_html' => $summaryHtml,
                    'trending_html' => $trendingHtml,
                    'meta' => [
                        'current_page' => $posts->currentPage(),
                        'last_page'    => $posts->lastPage(),
                        'total'        => $posts->total(),
                        'per_page'     => $posts->perPage(),
                    ],
                    'categories' => $categories->map(fn ($c) => [
                        'id' => $c->id,
                        'name' => $c->name,
                    ])->values(),
                    'popular_tags' => $popularTags->map(fn ($t) => [
                        'id' => $t->id,
                        'name' => $t->name,
                        'slug' => $t->slug,
                    ])->values(),
                ],
            ];

            Log::info('club.posts response.out', [
                'requestId' => $rid,
                'clubId' => $club->id,
                'status' => 'S',
                'page' => $posts->currentPage(),
                'perPage' => $posts->perPage(),
                'total' => $posts->total(),
                'selectedTagsCount' => count($selectedTags),
                'hasSearch' => !empty(trim((string) $searchText)),
            ]);

            return response()->json($resp);
        } catch (\Throwable $e) {
            Log::error('club.posts exception', [
                'requestId' => $rid,
                'clubId' => $club->id,
                'actorId' => optional($request->user())->id,
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            return response()->json([
                'status'      => 'E',
                'timeStamp'   => now()->format('Y-m-d H:i:s'),
                'requestId'   => $requestId,
                'requestTime' => $requestTime,
                'message'     => 'Internal server error',
                'error'       => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    private function isClubMember(Request $request, Club $club): bool
    {
        $user = $request->user();
        if (!$user) return false;

        // safe default using your pivot definition (club_user)
        return $club->members()->where('users.id', $user->id)->exists();
    }
}
