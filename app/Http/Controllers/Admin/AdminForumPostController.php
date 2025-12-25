<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Club;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminForumPostController extends Controller {

    public function index(Request $request) {
        $query = Post::query()
                ->with(['user', 'category'])
                ->withCount(['comments', 'likes']);

        // Search
        $searchText = trim((string) $request->input('search', ''));
        if ($searchText !== '') {
            $query->where(function ($q) use ($searchText) {
                $q->where('title', 'like', "%{$searchText}%")
                        ->orWhere('content', 'like', "%{$searchText}%")
                        ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$searchText}%")->orWhere('email', 'like', "%{$searchText}%"))
                        ->orWhereHas('tags', fn($tq) => $tq->where('name', 'like', "%{$searchText}%")->orWhere('slug', 'like', "%{$searchText}%"));
            });
        }

        // Filters
        if ($request->filled('status'))
            $query->where('status', $request->input('status'));
        if ($request->filled('visibility'))
            $query->where('visibility', $request->input('visibility'));
        if ($request->filled('category_id'))
            $query->where('category_id', $request->integer('category_id'));

        if ($request->filled('club_id')) {
            $clubId = $request->integer('club_id');
            $query->whereHas('clubs', fn($cq) => $cq->where('clubs.id', $clubId));
        }

        if ($request->filled('date_from'))
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        if ($request->filled('date_to'))
            $query->whereDate('created_at', '<=', $request->input('date_to'));

        if ($request->filled('has_media')) {
            if ($request->input('has_media') === 'yes') {
                $query->whereNotNull('media_paths');
            } elseif ($request->input('has_media') === 'no') {
                $query->where(function ($q) {
                    $q->whereNull('media_paths')->orWhere('media_paths', '[]');
                });
            }
        }

        // Tags AND logic
        $selectedTags = $request->input('tags', []);
        if (!is_array($selectedTags))
            $selectedTags = [$selectedTags];
        $selectedTags = collect($selectedTags)->filter(fn($v) => is_string($v) && trim($v) !== '')->unique()->values()->all();

        foreach ($selectedTags as $slug) {
            $query->whereHas('tags', fn($tq) => $tq->where('tags.slug', $slug));
        }

        // Sort
        $sortby = $request->input('sortby', 'recent');
        if ($sortby === 'popular') {
            $query->orderByDesc('views_count')->orderByDesc('likes_count')->orderByDesc('comments_count');
        } else {
            $query->latest();
        }

        $posts = $query->paginate(15)->withQueryString();

        // Filter data
        $categories = Category::query()->orderBy('name')->get();
        $clubs = Club::query()->orderBy('name')->get();
        $allTags = Tag::query()->orderBy('name')->get();

        // 关键：这里返回你说的 view
        return view('admin.forums.index', compact(
                        'posts',
                        'categories',
                        'clubs',
                        'allTags',
                        'selectedTags',
                        'searchText'
        ));
    }

    public function show(Post $post) {
        $post->load(['user', 'category', 'tags', 'clubs']);
        $post->loadCount(['comments', 'likes']);

        // 兼容：media_paths 可能是 null / JSON string / array
        $raw = $post->media_paths ?? null;

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = (json_last_error() === JSON_ERROR_NONE) ? $decoded : null;
        }

        $media = [];
        if (is_array($raw)) {
            foreach ($raw as $m) {
                if (!is_array($m))
                    continue;

                $disk = $m['disk'] ?? 'public';
                $path = $m['path'] ?? null;

                $url = null;
                if ($path && $disk === 'public') {
                    // public disk 才给可访问 URL
                    try {
                        $url = Storage::disk($disk)->url($path);
                    } catch (\Throwable $e) {
                        
                    }
                }

                $media[] = [
                    'disk' => $disk,
                    'path' => $path,
                    'type' => $m['type'] ?? null,
                    'original_name' => $m['original_name'] ?? ($m['originalName'] ?? null),
                    'url' => $url,
                ];
            }
        }

        return response()->json([
                    'success' => true,
                    'post' => [
                        'id' => $post->id,
                        'title' => $post->title,
                        'slug' => $post->slug,
                        'content' => $post->content,
                        'status' => $post->status,
                        'visibility' => $post->visibility,
                        'author' => $post->user ? [
                    'id' => $post->user->id,
                    'name' => $post->user->name,
                    'email' => $post->user->email,
                        ] : null,
                        'category' => $post->category ? [
                    'id' => $post->category->id,
                    'name' => $post->category->name,
                        ] : null,
                        'clubs' => $post->clubs->map(fn($c) => [
                            'id' => $c->id,
                            'name' => $c->name,
                                ])->values(),
                        'tags' => $post->tags->map(fn($t) => [
                            'id' => $t->id,
                            'name' => $t->name,
                            'slug' => $t->slug,
                            'status' => $t->status ?? null,
                            'type' => $t->type ?? null,
                                ])->values(),
                        'views_count' => (int) ($post->views_count ?? 0),
                        'likes_count' => (int) ($post->likes_count ?? 0),
                        'comments_count' => (int) ($post->comments_count ?? 0),
                        'has_media' => count($media) > 0,
                        'media' => $media,
                    ],
        ]);
    }
}
