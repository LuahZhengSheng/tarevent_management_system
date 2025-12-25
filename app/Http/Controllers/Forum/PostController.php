<?php

// app/Http/Controllers/Forum/PostController.php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Http\Requests\Forum\StorePostRequest;
use App\Http\Requests\Forum\UpdatePostRequest;
use App\Decorators\BasePostDecorator;
use App\Decorators\ContentSanitizationDecorator;
use App\Decorators\ValidationPostDecorator;
use App\Decorators\MediaPostDecorator;
use App\Decorators\TagsPostDecorator;
use App\Support\PostMediaHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PostController extends Controller {

    public function __construct() {
//        if (config('app.env') === 'local' && !Auth::check()) {
//            $user = User::find(2);
//            if ($user) {
//                Auth::login($user);
//                \Log::info('Auto-logged in User ID = 2 for testing');
//            } else {
//                \Log::error('User ID = 2 not found in database');
//            }
//        }
        $this->middleware(['auth', 'check.active.user']);
    }

    /**
     * Display a listing of posts
     */
    public function index(Request $request) {
        $query = Post::published()
                ->public()
                ->with(['user', 'category', 'tags', 'clubs'])
                ->withCount(['comments', 'likes']);

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        /**
         * Tags filter (multi-select)
         * - supports tags[]=slug
         * - also keep backward compatible with tag=slug (single)
         * AND logic: post must have all selected tags
         */
        $selectedTags = $request->input('tags', []);
        if (!is_array($selectedTags))
            $selectedTags = [$selectedTags];

        if (empty($selectedTags) && $request->filled('tag')) {
            $selectedTags = [$request->tag];
        }

        $selectedTags = collect($selectedTags)
                ->filter(fn($v) => is_string($v) && trim($v) !== '')
                ->unique()
                ->values()
                ->all();

        if (!empty($selectedTags)) {
            foreach ($selectedTags as $slug) {
                $query->whereHas('tags', function ($q) use ($slug) {
                    $q->where('tags.slug', $slug);
                });
            }
        }

        // Search (keep your current param name "search")
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

        $posts = $query->paginate(15)->withQueryString();

        $categories = Category::active()->ordered()->get();
        $popularTags = Tag::active()->popular(10)->get(); // by usage_count desc [file:180]
        // AJAX response (search/filter/sort/tags/infinite-scroll)
        if ($request->ajax() || $request->boolean('ajax')) {
            $postsHtml = view('forums.partials.posts_page', ['posts' => $posts])->render();

            // 下面两个 partial 我在第2点给你完整代码
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
                        'success' => true,
                        'posts_html' => $postsHtml,
                        'summary_html' => $summaryHtml,
                        'trending_html' => $trendingHtml,
                        'meta' => [
                            'current_page' => $posts->currentPage(),
                            'last_page' => $posts->lastPage(),
                            'total' => $posts->total(),
                        ],
            ]);
        }

        return view('forums.index', compact('posts', 'categories', 'popularTags'));
    }

    /**
     * Show the form for creating a new post
     */
    public function create() {
        $categories = Category::active()->ordered()->get();
        $activeTags = Tag::active()->orderBy('name')->get();

        return view('forums.create', compact('categories', 'activeTags'));
    }

    /**
     * Store a newly created post
     */
    public function store(StorePostRequest $request) {
        try {
            DB::beginTransaction();

            // Build decorator chain (order matters!)
            $baseDecorator = new BasePostDecorator($request);                 // 1. 基础数据
            $sanitizedDecorator = new ContentSanitizationDecorator($baseDecorator); // 2. 内容清洗
            $validatedDecorator = new ValidationPostDecorator($sanitizedDecorator); // 3. 校验
            $mediaDecorator = new MediaPostDecorator($validatedDecorator);      // 4. 媒体
            $tagsDecorator = new TagsPostDecorator($mediaDecorator);           // 5. 标签
            // Process all data through decorators
            $processedData = $tagsDecorator->process();

            // Extract tag IDs before creating post
            $tagIds = $processedData['tag_ids'] ?? [];
            unset($processedData['tag_ids']);

            // Set user ID
            $processedData['user_id'] = auth()->id();

            // 不再使用 club_id 字段，visibility + club_ids 由中间表 club_posts 表达
            // Set published_at timestamp
            if ($processedData['status'] === 'published') {
                $processedData['published_at'] = now();
            }

            // Create post
            $post = Post::create($processedData);

            // Attach tags with usage count tracking
            if (!empty($tagIds)) {
                $post->syncTagsWithCount($tagIds, auth()->id());
            }

            // === 新增：写入 club_posts 中间表 ===
            if ($processedData['visibility'] === 'club_only' && $request->filled('club_ids')) {
                $clubIds = $request->input('club_ids'); // array
                // 构建 pivot 数据：默认 pinned=false, status=active
                $pivotData = collect($clubIds)
                        ->unique()
                        ->mapWithKeys(function ($id) {
                            return [$id => ['pinned' => false, 'status' => 'active']];
                        })
                        ->toArray();

                $post->clubs()->sync($pivotData);
            } else {
                // 不是 club_only，确保没有 club 关联
                $post->clubs()->detach();
            }

            DB::commit();

            Log::info('Post created successfully', [
                'post_id' => $post->id,
                'user_id' => $post->user_id,
                'status' => $post->status,
                'tags_count' => count($tagIds),
                'has_media' => isset($processedData['media_paths']),
            ]);

            // Return JSON for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                            'success' => true,
                            'message' => $post->status === 'draft' ? 'Post saved as draft successfully!' : 'Post published successfully!',
                            'redirect' => route('forums.posts.show', $post->slug),
                            'post' => $post->load(['category', 'tags', 'clubs']),
                ]);
            }

            // Redirect for normal form submissions
            return redirect()
                            ->route('forums.posts.show', $post->slug)
                            ->with(
                                    'success',
                                    $post->status === 'draft' ? 'Post saved as draft successfully!' : 'Post published successfully!'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                            'success' => false,
                            'message' => 'Validation failed.',
                            'errors' => $e->errors(),
                                ], 422);
            }

            return back()->withInput()->withErrors($e->errors());
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Post creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                            'success' => false,
                            'message' => 'Failed to create post. Please try again.',
                            'error' => config('app.debug') ? $e->getMessage() : null,
                                ], 500);
            }

            return back()
                            ->withInput()
                            ->withErrors(['error' => 'Failed to create post: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified post
     */
    public function show(Post $post) {
        // Authorization check
        if (!$post->canBeViewedBy(auth()->user())) {
            abort(403, 'You do not have permission to view this post.');
        }

        // Eager load relationships
        $post->load([
            'user',
            'category',
            'tags',
            'clubs',
            'comments' => function ($q) {
                $q->whereNull('parent_id')
                        ->with([
                            'user',
                            'replyTo',
                            'replies.user',
                            'replies.replyTo',
                        ])
                        ->latest()
                        ->limit(20);
            },
        ]);

        $post->loadCount(['comments', 'likes']);

        // Increment views count
        $post->incrementViews();

        // Check if current user has liked the post
        $hasLiked = false;
        if (auth()->check()) {
            $hasLiked = $post->isLikedBy(auth()->user());
        }

        // Get related posts (same category, exclude current)
        $relatedPosts = Post::published()
                ->public()
                ->where('category_id', $post->category_id)
                ->where('id', '!=', $post->id)
                ->with(['user', 'category'])
                ->withCount(['comments', 'likes'])
                ->inRandomOrder()
                ->limit(3)
                ->get();

        return view('forums.show', compact('post', 'hasLiked', 'relatedPosts'));
    }

    /**
     * Show the form for editing the post
     */
    public function edit(Post $post) {
        // Authorization check
        if (!$post->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to edit this post.');
        }

        $categories = Category::active()->ordered()->get();
        $activeTags = Tag::active()->orderBy('name')->get();

        // 预加载关系：clubs
        $post->load(['category', 'tags', 'clubs']);

        return view('forums.edit', compact('post', 'categories', 'activeTags'));
    }

    /**
     * Update the specified post
     */
    public function update(UpdatePostRequest $request, Post $post) {
        try {
            DB::beginTransaction();

            // Decorator chain
            $baseDecorator = new BasePostDecorator($request);
            $sanitizedDecorator = new ContentSanitizationDecorator($baseDecorator);
            $validatedDecorator = new ValidationPostDecorator($sanitizedDecorator);
            $mediaDecorator = new MediaPostDecorator($validatedDecorator);
            $tagsDecorator = new TagsPostDecorator($mediaDecorator);

            $processedData = $tagsDecorator->process();

            // Extract tag IDs
            $tagIds = $processedData['tag_ids'] ?? [];
            unset($processedData['tag_ids']);

            // ===== Media 处理：保留你现有逻辑 =====
            if ($request->boolean('replace_media', false)) {
                // 删除旧 media
                if ($post->hasMedia()) {
                    PostMediaHelper::deletePostMedia($post->media_paths);
                }
                // 使用 decorator 生成的新 media_paths
                // $processedData['media_paths'] 已经设置好
            } elseif (isset($processedData['media_paths'])) {
                // 合并旧 media 和新增 media
                $existingMedia = $post->media_paths ?? [];
                $processedData['media_paths'] = array_merge($existingMedia, $processedData['media_paths']);

                if (count($processedData['media_paths']) > MediaHelper::POST_MAX_MEDIA_COUNT) {
                    $processedData['media_paths'] = array_slice(
                            $processedData['media_paths'],
                            0,
                            MediaHelper::POST_MAX_MEDIA_COUNT
                    );
                }
            }

            // ===== published_at 处理：和 store 一致 =====
            if ($processedData['status'] === 'published' && !$post->published_at) {
                $processedData['published_at'] = now();
            } elseif ($processedData['status'] === 'draft') {
                $processedData['published_at'] = null;
            }

            // 不再使用 club_id 列
            unset($processedData['club_id']);

            // 更新 post 本身
            $post->update($processedData);

            // 更新 tags
            if (!empty($tagIds)) {
                $post->syncTagsWithCount($tagIds, auth()->id());
            } elseif (isset($tagIds)) {
                // 空数组 => 清空 tags
                $post->tags()->detach();
            }

            // ===== 更新 club_posts 中间表 =====
            if ($processedData['visibility'] === 'club_only' && $request->filled('club_ids')) {
                $clubIds = $request->input('club_ids');

                $pivotData = collect($clubIds)
                        ->unique()
                        ->mapWithKeys(function ($id) {
                            return [$id => ['pinned' => false, 'status' => 'active']];
                        })
                        ->toArray();

                $post->clubs()->sync($pivotData);
            } else {
                // 改回 public 等非 club_only，可视为不再属于任何 club
                $post->clubs()->detach();
            }

            DB::commit();

            Log::info('Post updated successfully', [
                'post_id' => $post->id,
                'user_id' => auth()->id(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                            'success' => true,
                            'message' => 'Post updated successfully!',
                            'redirect' => route('forums.posts.show', $post->slug),
                ]);
            }

            return redirect()
                            ->route('forums.posts.show', $post->slug)
                            ->with('success', 'Post updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                            'success' => false,
                            'message' => 'Validation failed.',
                            'errors' => $e->errors(),
                                ], 422);
            }

            return back()->withInput()->withErrors($e->errors());
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Post update failed', [
                'error' => $e->getMessage(),
                'post_id' => $post->id,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                            'success' => false,
                            'message' => 'Failed to update post.',
                            'error' => config('app.debug') ? $e->getMessage() : null,
                                ], 500);
            }

            return back()
                            ->withInput()
                            ->withErrors(['error' => 'Failed to update post: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified post
     */
    public function destroy(Post $post) {
        // Authorization check
        if (!$post->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to delete this post.');
        }

        try {
            DB::beginTransaction();

            // Delete will trigger model events to clean up media, tags, etc.
            $post->delete();

            DB::commit();

            Log::info('Post deleted successfully', [
                'post_id' => $post->id,
                'user_id' => auth()->id(),
            ]);

            return redirect()
                            ->route('forums.index')
                            ->with('success', 'Post deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Post deletion failed', [
                'error' => $e->getMessage(),
                'post_id' => $post->id,
            ]);

            return back()->withErrors(['error' => 'Failed to delete post: ' . $e->getMessage()]);
        }
    }

    /**
     * Get tags for autocomplete (AJAX)
     */
    public function searchTags(Request $request) {
        $search = $request->get('q', '');

        $tags = Tag::active()
                ->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
                })
                ->orderBy('usage_count', 'desc')
                ->limit(10)
                ->get(['id', 'name', 'type', 'usage_count']);

        return response()->json([
                    'success' => true,
                    'tags' => $tags,
        ]);
    }

    /**
     * Request a new tag (requires admin approval)
     */
    public function requestTag(Request $request) {
        // Validate input
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:50|unique:tags,name',
            'description' => 'nullable|string|max:200',
        ]);

        try {
            // Sanitize tag name
            $tagName = strtolower(trim($validated['name']));
            $tagName = preg_replace('/[^a-z0-9\s\-_]/u', '', $tagName);
            $tagName = preg_replace('/\s+/', ' ', $tagName);

            // Generate unique slug
            $slug = Str::slug($tagName);
            $originalSlug = $slug;
            $count = 1;

            while (Tag::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            // Create tag with pending status
            $tag = Tag::create([
                        'name' => $tagName,
                        'slug' => $slug,
                        'type' => 'community',
                        'status' => 'pending', // Requires admin approval
                        'description' => $validated['description'] ?? null,
                        'created_by' => auth()->id(),
            ]);

            Log::info('Tag creation requested', [
                'tag_id' => $tag->id,
                'tag_name' => $tagName,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                        'success' => true,
                        'message' => 'Tag submitted for approval! It will be reviewed by an admin shortly.',
                        'tag' => [
                            'id' => $tag->id,
                            'name' => $tag->name,
                            'status' => $tag->status,
                        ],
            ]);
        } catch (\Exception $e) {
            Log::error('Tag request failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                        'success' => false,
                        'message' => 'Failed to submit tag request. ' . $e->getMessage(),
                            ], 500);
        }
    }

    public function toggleLike(Request $request, Post $post) {
        $user = $request->user();

        // 强制后端登录校验（前端已拦，但后端也要拦）
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        // 可选：可见性校验（避免对不可见帖子点赞）
        if (!$post->canBeViewedBy($user)) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        DB::transaction(function () use ($post, $user, &$liked) {
            $liked = $post->toggleLike($user); // 你 Post 模型已有 toggleLike [file:2]
        });

        return response()->json([
                    'success' => true,
                    'liked' => $liked,
                    'likes_count' => $post->fresh()->likes_count,
        ]);
    }

    public function storeComment(Request $request, Post $post) {
        $user = $request->user();

        if (!$post->canBeViewedBy($user)) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:2000'],
            'parent_id' => ['nullable', 'integer', 'exists:post_comments,id'],
            'reply_to_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'media' => ['nullable'], // files
            'media.*' => ['file', 'max:51200'], // 50MB each; 你可按需改
        ]);

        // 至少要有 body 或 media
        if (empty(trim($validated['body'] ?? '')) && !$request->hasFile('media')) {
            return response()->json(['success' => false, 'message' => 'Comment cannot be empty.'], 422);
        }

        // 如果是 reply：parent 必须属于同一个 post
        $parent = null;
        if (!empty($validated['parent_id'])) {
            $parent = \App\Models\PostComment::where('id', $validated['parent_id'])
                    ->where('post_id', $post->id)
                    ->firstOrFail();
        }

        // reply_to_user_id：默认指向 parent 作者（前端也会传，但这里兜底）
        $replyToUserId = $validated['reply_to_user_id'] ?? ($parent?->user_id);

        // 上传 media：保存结构为 [{path,type,mime_type,original_name,size}, ...]
        $mediaPaths = [];
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $mime = $file->getMimeType();
                $type = str_starts_with($mime, 'video/') ? 'video' : 'image';
                $path = $file->store('comments/media', 'public');

                $mediaPaths[] = [
                    'path' => $path,
                    'type' => $type,
                    'mime_type' => $mime,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                ];
            }
        }

        $comment = \DB::transaction(function () use ($post, $user, $parent, $replyToUserId, $validated, $mediaPaths) {
                    $comment = \App\Models\PostComment::create([
                                'post_id' => $post->id,
                                'user_id' => $user->id,
                                'parent_id' => $parent?->id,
                                'reply_to_user_id' => $replyToUserId,
                                'body' => $validated['body'] ?? '',
                                'media_paths' => !empty($mediaPaths) ? $mediaPaths : null,
                    ]);

                    $post->increment('comments_count');

                    return $comment;
                });

        $comment->load(['user', 'replyTo', 'replies.user', 'replies.replyTo']);

        $html = view('forums.partials._comment_item', [
            'comment' => $comment,
            'isReply' => $comment->parent_id !== null,
                ])->render();

        return response()->json([
                    'success' => true,
                    'html' => $html,
                    'totalComments' => $post->fresh()->comments_count,
                    'comment' => [
                        'id' => $comment->id,
                        'parent_id' => $comment->parent_id,
                    ],
        ]);
    }

    public function destroyComment(Request $request, PostComment $comment) {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        if (!$comment->canBeEditedBy($user)) { // 你 PostComment 已有 canBeEditedBy [file:7]
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        DB::transaction(function () use ($comment) {
            $post = $comment->post()->lockForUpdate()->first();
            $deletedCount = 1 + $comment->replies()->count();

            $comment->replies()->delete();
            $comment->delete();

            if ($post) {
                $post->decrement('comments_count', $deletedCount);
            }
        });

        return response()->json([
                    'success' => true,
                    'totalComments' => $comment->post->fresh()->comments_count,
        ]);
    }
}
