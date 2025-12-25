<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Club;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;

class ClubPostFeedController extends Controller {

    public function index(Request $request, Club $club) {
        // ---- IFA mandatory: requestId OR timeStamp ----
        $requestId = $request->input('requestId');
        $reqTs = $request->input('timeStamp');

        if (empty($requestId) && empty($reqTs)) {
            return response()->json([
                        'status' => 'F',
                        'timeStamp' => now()->format('Y-m-d H:i:s'),
                        'requestId' => $requestId,
                        'message' => 'Missing requestId or timeStamp.',
                            ], 422);
        }

        // ---- AUTHZ: only club members can view ----
        if (!$this->isClubMember($request, $club)) {
            return response()->json([
                        'status' => 'F',
                        'timeStamp' => now()->format('Y-m-d H:i:s'),
                        'requestId' => $requestId,
                        'message' => 'Forbidden. Only club members can view club posts.',
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
        if (!is_array($selectedTags))
            $selectedTags = [$selectedTags];
        if (empty($selectedTags) && $request->filled('tag'))
            $selectedTags = [$request->input('tag')];

        $selectedTags = collect($selectedTags)
                ->filter(fn($v) => is_string($v) && trim($v) !== '')
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
        if ($sortBy === 'popular')
            $query->popular();
        else
            $query->recent();

        // Pagination
        $perPage = (int) ($request->input('perPage', 15));
        $perPage = max(1, min(50, $perPage));

        $posts = $query->paginate($perPage)->withQueryString();

        // Optional: provide categories + trending tags for sidebar reuse
        $categories = Category::active()->ordered()->get();
        $popularTags = Tag::active()->popular(10)->get();

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

        return response()->json([
                    'status' => 'S',
                    'timeStamp' => now()->format('Y-m-d H:i:s'),
                    'requestId' => $requestId,
                    'data' => [
                        'posts_html' => $postsHtml,
                        'summary_html' => $summaryHtml,
                        'trending_html' => $trendingHtml,
                        'meta' => [
                            'current_page' => $posts->currentPage(),
                            'last_page' => $posts->lastPage(),
                            'total' => $posts->total(),
                            'per_page' => $posts->perPage(),
                        ],
                        // for other modules / JS-driven UI
                        'categories' => $categories->map(fn($c) => [
                            'id' => $c->id,
                            'name' => $c->name,
                                ])->values(),
                        'popular_tags' => $popularTags->map(fn($t) => [
                            'id' => $t->id,
                            'name' => $t->name,
                            'slug' => $t->slug,
                                ])->values(),
                    ],
        ]);
    }

    /**
     * IMPORTANT: Replace with your existing club permission logic.
     * You said controller already has full rule logic; paste it here.
     */
    private function isClubMember(Request $request, Club $club): bool {
        $user = $request->user();
        if (!$user)
            return false;

        // safe default using your pivot definition (club_user)
        return $club->members()->where('users.id', $user->id)->exists();
    }
}
