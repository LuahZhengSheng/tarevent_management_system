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
use App\Exceptions\VirusScanFailedException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        $pendingTags = Tag::query()
                ->where('status', 'pending')
                ->where('created_by', auth()->id())
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'status']);

        return view('forums.create', compact('categories', 'activeTags', 'pendingTags'));
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

            // === 写入 club_posts 中间表 ===
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

            $redirectUrl = $post->status === 'draft' ? url('/forums/my-posts?tab=drafts') : route('forums.posts.show', $post->slug);

            if ($request->expectsJson()) {
                return response()->json([
                            'success' => true,
                            'message' => $post->status === 'draft' ? 'Post saved as draft successfully!' : 'Post published successfully!',
                            'redirect' => $redirectUrl,
                            'post' => $post->load(['category', 'tags', 'clubs']),
                ]);
            }

            return redirect($redirectUrl)->with(
                            'success',
                            $post->status === 'draft' ? 'Post saved as draft successfully!' : 'Post published successfully!'
            );

//            // Return JSON for AJAX requests
//            if ($request->expectsJson()) {
//                return response()->json([
//                            'success' => true,
//                            'message' => $post->status === 'draft' ? 'Post saved as draft successfully!' : 'Post published successfully!',
//                            'redirect' => route('forums.posts.show', $post->slug),
//                            'post' => $post->load(['category', 'tags', 'clubs']),
//                ]);
//            }
//
//            // Redirect for normal form submissions
//            return redirect()
//                            ->route('forums.posts.show', $post->slug)
//                            ->with(
//                                    'success',
//                                    $post->status === 'draft' ? 'Post saved as draft successfully!' : 'Post published successfully!'
//            );
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
                'exception_class' => get_class($e),
            ]);

            $userMessage = $e instanceof VirusScanFailedException ? $e->userMessage : 'Failed to create post. Please try again.';

            // 这里 fail closed：不把内部原因回传给用户（更安全）
            if ($request->expectsJson()) {
                return response()->json([
                            'success' => false,
                            'message' => $userMessage,
                            'error' => null,
                                ], 422);
            }

            return back()
                            ->withInput()
                            ->withErrors(['error' => $userMessage]);
        }
    }

    /**
     * Display the specified post
     */
    public function show(Request $request, Post $post) {
        // 1) 草稿一律当作不存在（404）
        if ($post->status === 'draft') {
            abort(404);
        }

        // 2) 其它再走你原来的可见性逻辑
        if (!$post->canBeViewedBy(auth()->user())) {
            abort(403, 'You do not have permission to view this post.');
        }

        // 后面保持原样
        $post->load([
            'user',
            'category',
            'tags',
            'clubs',
            'comments' => function ($q) {
                $q->whereNull('parent_id')
                        ->with(['user', 'replyTo', 'replies.user', 'replies.replyTo'])
                        ->latest()
                        ->limit(20);
            },
        ]);

        $post->loadCount(['comments', 'likes']);
        $post->incrementViews();

        $hasLiked = auth()->check() ? $post->isLikedBy(auth()->user()) : false;

        $relatedPosts = Post::published()
                ->public()
                ->where('category_id', $post->category_id)
                ->where('id', '!=', $post->id)
                ->with(['user', 'category'])
                ->withCount(['comments', 'likes'])
                ->inRandomOrder()
                ->limit(3)
                ->get();

        if ($request->query('format') === 'json') {
            return response()->json([
                        'success' => true,
                        'post' => $post,
            ]);
        }

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

            $incomingStatus = $processedData['status'] ?? $post->status;

            /**
             * CASE A:
             * 编辑的是 published，但这次点击 Save Draft（incomingStatus=draft）
             * => 删除旧 draft（若存在）=> 创建新 draft（original_post_id 指向该 published）
             * => 然后把本次内容写进 draft
             */
            if ($post->status === 'published' && $incomingStatus === 'draft') {
                // 旧 draft（同一篇 original 只能一个）
                $existingDraft = Post::where('original_post_id', $post->id)
                        ->where('status', 'draft')
                        ->first();

                if ($existingDraft) {
                    // 软删旧 draft（你 model 用 SoftDeletes）[file:7]
                    $existingDraft->delete();
                }

                // 创建新 draft：从 original 复制一份
                $draft = $post->replicate();

                $draft->original_post_id = $post->id;
                $draft->status = 'draft';
                $draft->published_at = null;

                // draft slug 唯一
                $draft->slug = $post->slug . '-draft-' . now()->timestamp;

                // views/likes/comments 不继承
                $draft->views_count = 0;
                $draft->likes_count = 0;
                $draft->comments_count = 0;

                // 把本次修改写入 draft（包含 title/content/category/visibility/media_paths 等）
                $draft->fill($processedData);
                $draft->status = 'draft';
                $draft->published_at = null;
                $draft->save();

                // 同步 tags
                if (!empty($tagIds)) {
                    $draft->syncTagsWithCount($tagIds, auth()->id());
                } else {
                    $draft->tags()->detach();
                }

                // 同步 clubs
                if (($processedData['visibility'] ?? $draft->visibility) === 'club_only' && $request->filled('club_ids')) {
                    $clubIds = $request->input('club_ids');

                    $pivotData = collect($clubIds)
                            ->unique()
                            ->mapWithKeys(function ($id) {
                                return [$id => ['pinned' => false, 'status' => 'active']];
                            })
                            ->toArray();

                    $draft->clubs()->sync($pivotData);
                } else {
                    $draft->clubs()->detach();
                }

                DB::commit();

                $redirectUrl = url('/forums/my-posts?tab=drafts');

                if ($request->expectsJson()) {
                    return response()->json([
                                'success' => true,
                                'message' => 'Draft version created successfully!',
                                'redirect' => $redirectUrl,
                    ]);
                }

                return redirect($redirectUrl)->with('success', 'Draft version created successfully!');
            }

            /**
             * CASE B:
             * 编辑的是 draft（post.status=draft）
             * - draft -> draft：只更新 draft
             * - draft -> publish：
             *   - 若有 original_post_id：覆盖 original post，然后删除 draft（原逻辑）
             *   - 若无 original_post_id：把当前 draft 直接转成 published，并重新计算 slug（新逻辑）
             */
            if ($post->status === 'draft') {

                if ($incomingStatus === 'published') {

                    // ===== 没有 original_post_id 的“新建草稿”也允许直接发布 =====
                    if (empty($post->original_post_id)) {

                        // 1) 组装更新数据：以 processedData 为准
                        $updateDraft = $processedData;

                        // 2) 强制发布字段
                        $updateDraft['status'] = 'published';
                        $updateDraft['published_at'] = $post->published_at ?? now();

                        // 3) 重新计算 slug（用 title 生成正式 slug，并确保唯一）
                        $titleForSlug = $updateDraft['title'] ?? $post->title ?? null;

                        $baseSlug = $titleForSlug ? Str::slug($titleForSlug) : null;

                        // 如果 title 是纯中文等导致 slug 为空，兜底用 post-{id}
                        if (empty($baseSlug)) {
                            $baseSlug = 'post-' . $post->id;
                        }

                        $slug = $baseSlug;
                        $i = 1;
                        while (Post::where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
                            $slug = $baseSlug . '-' . $i++;
                        }
                        $updateDraft['slug'] = $slug;

                        // 4) 发布后不需要 original_post_id（保持 null）
                        $updateDraft['original_post_id'] = null;

                        // 5) 直接把 draft 更新成 published
                        $post->update($updateDraft);

                        // 6) tags：用本次提交 tagIds；没传就沿用现有（不动）
                        if (!empty($tagIds)) {
                            $post->syncTagsWithCount($tagIds, auth()->id());
                        }

                        // 7) clubs：按 visibility 处理
                        $visibility = $processedData['visibility'] ?? $post->visibility;
                        if ($visibility === 'club_only' && $request->filled('club_ids')) {
                            $clubIds = $request->input('club_ids');

                            $pivotData = collect($clubIds)
                                    ->unique()
                                    ->mapWithKeys(function ($id) {
                                        return [$id => ['pinned' => false, 'status' => 'active']];
                                    })
                                    ->toArray();

                            $post->clubs()->sync($pivotData);
                        } else {
                            $post->clubs()->detach();
                        }

                        DB::commit();

                        $redirectUrl = route('forums.posts.show', $post->slug);

                        if ($request->expectsJson()) {
                            return response()->json([
                                        'success' => true,
                                        'message' => 'Draft published successfully!',
                                        'redirect' => $redirectUrl,
                            ]);
                        }

                        return redirect($redirectUrl)->with('success', 'Draft published successfully!');
                    }

                    // ===== 原逻辑：有 original_post_id => 覆盖 original 并删除 draft =====
                    $original = Post::findOrFail($post->original_post_id);

                    $fieldsToCopy = [
                        'title',
                        'content',
                        'category_id',
                        'visibility',
                        'media_paths',
                    ];

                    $updateOriginal = [];
                    foreach ($fieldsToCopy as $f) {
                        if (array_key_exists($f, $processedData)) {
                            $updateOriginal[$f] = $processedData[$f];
                        } else {
                            $updateOriginal[$f] = $post->{$f};
                        }
                    }

                    $updateOriginal['status'] = 'published';
                    $updateOriginal['published_at'] = $original->published_at ?? now();

                    $original->update($updateOriginal);

                    if (!empty($tagIds)) {
                        $original->syncTagsWithCount($tagIds, auth()->id());
                    } else {
                        $originalTagIds = $post->tags()->pluck('tags.id')->toArray();
                        if (!empty($originalTagIds)) {
                            $original->syncTagsWithCount($originalTagIds, auth()->id());
                        } else {
                            $original->tags()->detach();
                        }
                    }

                    if (($processedData['visibility'] ?? $post->visibility) === 'club_only' && $request->filled('club_ids')) {
                        $clubIds = $request->input('club_ids');

                        $pivotData = collect($clubIds)
                                ->unique()
                                ->mapWithKeys(function ($id) {
                                    return [$id => ['pinned' => false, 'status' => 'active']];
                                })
                                ->toArray();

                        $original->clubs()->sync($pivotData);
                    } else {
                        $original->clubs()->detach();
                    }

                    $post->delete();

                    DB::commit();

                    $redirectUrl = route('forums.posts.show', $original->slug);

                    if ($request->expectsJson()) {
                        return response()->json([
                                    'success' => true,
                                    'message' => 'Draft published successfully!',
                                    'redirect' => $redirectUrl,
                        ]);
                    }

                    return redirect($redirectUrl)->with('success', 'Draft published successfully!');
                }

                // draft -> draft：走下面的“正常 update”，更新 draft 自己
            }


            /**
             * CASE C:
             * 其它情况（published->published、draft->draft）：
             * 走你原本逻辑，更新当前 $post
             */
            // ===== Media 处理：保留你现有逻辑 =====
            if ($request->boolean('replace_media', false)) {
                if ($post->hasMedia()) {
                    PostMediaHelper::deletePostMedia($post->media_paths);
                }
            } elseif (isset($processedData['media_paths'])) {
                $existingMedia = $post->media_paths ?? [];
                $processedData['media_paths'] = array_merge($existingMedia, $processedData['media_paths']);

                if (count($processedData['media_paths']) > PostMediaHelper::POST_MAX_MEDIA_COUNT) {
                    $processedData['media_paths'] = array_slice(
                            $processedData['media_paths'],
                            0,
                            PostMediaHelper::POST_MAX_MEDIA_COUNT
                    );
                }
            }

            // ===== published_at 处理：和 store 一致 =====
            if (($processedData['status'] ?? $post->status) === 'published' && !$post->published_at) {
                $processedData['published_at'] = now();
            } elseif (($processedData['status'] ?? $post->status) === 'draft') {
                $processedData['published_at'] = null;
            }

            unset($processedData['club_id']);

            // ===== visibility 变更：迁移已有 media 到对应 disk =====
            $oldVisibility = $post->visibility;
            $newVisibility = $processedData['visibility'] ?? $post->visibility;

            if ($oldVisibility !== $newVisibility && $post->hasMedia()) {
                $targetDisk = $newVisibility === 'club_only' ? 'local' : 'public';

                $migratedMedia = [];
                foreach (($post->media_paths ?? []) as $media) {
                    if (!is_array($media)) {
                        $migratedMedia[] = $media;
                        continue;
                    }

                    $srcDisk = $media['disk'] ?? 'public';
                    $path = $media['path'] ?? null;

                    if (!$path) {
                        $migratedMedia[] = $media;
                        continue;
                    }

                    if ($srcDisk === $targetDisk) {
                        $media['disk'] = $targetDisk;
                        $migratedMedia[] = $media;
                        continue;
                    }

                    if (!Storage::disk($srcDisk)->exists($path)) {
                        throw new \Exception("Media file missing: disk={$srcDisk}, path={$path}");
                    }

                    $stream = Storage::disk($srcDisk)->readStream($path);
                    if ($stream === false || $stream === null) {
                        throw new \Exception("Failed to read media stream: disk={$srcDisk}, path={$path}");
                    }

                    $written = Storage::disk($targetDisk)->writeStream($path, $stream);

                    if (is_resource($stream)) {
                        fclose($stream);
                    }

                    if (!$written) {
                        throw new \Exception("Failed to write media stream: disk={$targetDisk}, path={$path}");
                    }

                    Storage::disk($srcDisk)->delete($path);

                    $media['disk'] = $targetDisk;
                    $migratedMedia[] = $media;
                }

                if (!isset($processedData['media_paths'])) {
                    $processedData['media_paths'] = $migratedMedia;
                } else {
                    $byPath = [];
                    foreach ($migratedMedia as $m) {
                        if (is_array($m) && !empty($m['path'])) {
                            $byPath[$m['path']] = $m;
                        }
                    }

                    $processedData['media_paths'] = array_map(function ($m) use ($byPath) {
                        if (!is_array($m) || empty($m['path'])) {
                            return $m;
                        }
                        return $byPath[$m['path']] ?? $m;
                    }, $processedData['media_paths']);
                }
            }

            // 更新 post 本身
            $post->update($processedData);

            // 更新 tags
            if (!empty($tagIds)) {
                $post->syncTagsWithCount($tagIds, auth()->id());
            } elseif (isset($tagIds)) {
                $post->tags()->detach();
            }

            // 更新 clubs
            if (($processedData['visibility'] ?? $post->visibility) === 'club_only' && $request->filled('club_ids')) {
                $clubIds = $request->input('club_ids');

                $pivotData = collect($clubIds)
                        ->unique()
                        ->mapWithKeys(function ($id) {
                            return [$id => ['pinned' => false, 'status' => 'active']];
                        })
                        ->toArray();

                $post->clubs()->sync($pivotData);
            } else {
                $post->clubs()->detach();
            }

            DB::commit();

            // draft：去 drafts tab；published：回 show
            $redirectUrl = ($post->status === 'draft') ? url('/forums/my-posts?tab=drafts') : route('forums.posts.show', $post->slug);

            if ($request->expectsJson()) {
                return response()->json([
                            'success' => true,
                            'message' => $post->status === 'draft' ? 'Draft saved successfully!' : 'Post updated successfully!',
                            'redirect' => $redirectUrl,
                ]);
            }

            return redirect($redirectUrl)->with(
                            'success',
                            $post->status === 'draft' ? 'Draft saved successfully!' : 'Post updated successfully!'
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

            Log::error('Post update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'post_id' => $post->id ?? null,
                'user_id' => auth()->id(),
                'exception_class' => get_class($e),
            ]);

            $userMessage = $e instanceof VirusScanFailedException ? $e->userMessage : 'Failed to update post. Please try again.';

            if ($request->expectsJson()) {
                return response()->json([
                            'success' => false,
                            'message' => $userMessage,
                            'error' => null,
                                ], 422);
            }

            return back()->withInput()->withErrors(['error' => $userMessage]);
        }
    }

    /**
     * Remove the specified post
     * 
     * Deletion Rules:
     * 1. DRAFT deletion:
     *    - Only delete media files that are EXCLUSIVELY used by this draft
     *    - Keep media files that are also used by published posts
     *    - Detach tags and clubs
     *    - Soft delete the draft
     * 
     * 2. PUBLISHED post deletion:
     *    - Delete all media files (published posts own their media)
     *    - Detach tags and clubs
     *    - KEEP likes, saves, comments, replies (preserve user engagement history)
     *    - Soft delete the post
     */
    public function destroy(Post $post) {
        // Authorization check
        if (!$post->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to delete this post.');
        }

        try {
            DB::beginTransaction();

            $postStatus = $post->status;
            $postId = $post->id;
            $hasMedia = $post->hasMedia();
            $userId = auth()->id();

            Log::info('Post deletion started', [
                'post_id' => $postId,
                'status' => $postStatus,
                'user_id' => $userId,
                'has_media' => $hasMedia,
            ]);

            // ============================================
            // Step 1: Handle Tags (for both draft and published)
            // ============================================
            if ($post->tags()->exists()) {
                $tagIds = $post->tags()->pluck('tags.id')->toArray();

                // Decrease usage count for each tag
                foreach ($tagIds as $tagId) {
                    DB::table('tags')
                            ->where('id', $tagId)
                            ->where('usage_count', '>', 0)
                            ->decrement('usage_count');
                }

                // Detach all tags
                $post->tags()->detach();

                Log::info('Tags detached and usage counts updated', [
                    'post_id' => $postId,
                    'tag_count' => count($tagIds),
                ]);
            }

            // ============================================
            // Step 2: Handle Clubs (for both draft and published)
            // ============================================
            if ($post->clubs()->exists()) {
                $clubCount = $post->clubs()->count();
                $post->clubs()->detach();

                Log::info('Clubs detached', [
                    'post_id' => $postId,
                    'club_count' => $clubCount,
                ]);
            }

            // ============================================
            // Step 3: Handle Category Count (for published posts only)
            // ============================================
            if ($postStatus === 'published' && $post->category) {
                $post->category->decrement('post_count');

                Log::info('Category post count decremented', [
                    'post_id' => $postId,
                    'category_id' => $post->category_id,
                ]);
            }

            // ============================================
            // Step 4: Handle Media Files
            // ============================================
            if ($hasMedia) {
                $mediaPaths = $post->media_paths ?? [];

                // === CASE A: Deleting a DRAFT ===
                if ($postStatus === 'draft') {
                    $mediaToDelete = [];
                    $mediaToKeep = [];

                    // Check each media file to see if it's used by published posts
                    foreach ($mediaPaths as $media) {
                        $mediaPath = is_array($media) ? ($media['path'] ?? null) : null;

                        if (!$mediaPath) {
                            continue;
                        }

                        // Query: Is this media file used by ANY published post?
                        $isUsedByPublished = Post::where('status', 'published')
                                ->where('id', '!=', $postId)
                                ->whereNotNull('media_paths')
                                ->where(function ($query) use ($mediaPath) {
                                    // Check if media_paths JSON contains this path
                                    $query->whereRaw("JSON_SEARCH(media_paths, 'one', ?) IS NOT NULL", [$mediaPath])
                                    ->orWhereRaw("JSON_CONTAINS(media_paths, JSON_QUOTE(?), '$[*].path')", [$mediaPath]);
                                })
                                ->exists();

                        if ($isUsedByPublished) {
                            // This media is shared with a published post - KEEP IT
                            $mediaToKeep[] = $mediaPath;
                        } else {
                            // This media is ONLY used by this draft - DELETE IT
                            $mediaToDelete[] = $media;
                        }
                    }

                    // Delete draft-exclusive media files
                    if (!empty($mediaToDelete)) {
                        try {
                            foreach ($mediaToDelete as $media) {
                                $disk = is_array($media) ? ($media['disk'] ?? 'public') : 'public';
                                $path = is_array($media) ? ($media['path'] ?? null) : null;

                                if ($path && Storage::disk($disk)->exists($path)) {
                                    Storage::disk($disk)->delete($path);
                                }
                            }

                            Log::info('Draft-exclusive media deleted', [
                                'draft_id' => $postId,
                                'deleted_count' => count($mediaToDelete),
                                'kept_count' => count($mediaToKeep),
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Failed to delete draft media files', [
                                'post_id' => $postId,
                                'error' => $e->getMessage(),
                            ]);
                            // Don't fail the whole deletion if media cleanup fails
                        }
                    }

                    if (!empty($mediaToKeep)) {
                        Log::info('Shared media preserved', [
                            'draft_id' => $postId,
                            'preserved_paths' => $mediaToKeep,
                        ]);
                    }
                }
                // === CASE B: Deleting a PUBLISHED post ===
                else {
                    try {
                        // Delete all media files (published posts own their media)
                        foreach ($mediaPaths as $media) {
                            $disk = is_array($media) ? ($media['disk'] ?? 'public') : 'public';
                            $path = is_array($media) ? ($media['path'] ?? null) : null;

                            if ($path && Storage::disk($disk)->exists($path)) {
                                Storage::disk($disk)->delete($path);
                            }
                        }

                        Log::info('Published post media deleted', [
                            'post_id' => $postId,
                            'media_count' => count($mediaPaths),
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to delete published post media', [
                            'post_id' => $postId,
                            'error' => $e->getMessage(),
                        ]);
                        // Don't fail the whole deletion if media cleanup fails
                    }
                }
            }

            // ============================================
            // Step 5: Soft Delete the Post
            // ============================================
            // IMPORTANT: For published posts, we DON'T delete:
            // - likes (post_likes table)
            // - saves (post_saves table)
            // - comments (post_comments table)
            // These will remain in the database with the soft-deleted post_id
            // This preserves user engagement history

            $post->delete(); // Soft delete

            DB::commit();

            Log::info('Post deletion completed successfully', [
                'post_id' => $postId,
                'status' => $postStatus,
                'user_id' => $userId,
            ]);

            // Redirect based on post status
            $redirectUrl = $postStatus === 'draft' ? route('forums.my-posts', ['tab' => 'drafts']) : route('forums.my-posts', ['tab' => 'posts']);

            return redirect($redirectUrl)
                            ->with('success', 'Post deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Post deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'post_id' => $post->id ?? null,
                'user_id' => auth()->id(),
            ]);

            return back()->withErrors([
                        'error' => 'Failed to delete post. Please try again.'
            ]);
        }
    }

    /**
     * Remove the specified post
     */
//    public function destroy(Post $post) {
//        // Authorization check
//        if (!$post->canBeEditedBy(auth()->user())) {
//            abort(403, 'You do not have permission to delete this post.');
//        }
//
//        try {
//            DB::beginTransaction();
//
//            // Delete will trigger model events to clean up media, tags, etc.
//            $post->delete();
//
//            DB::commit();
//
//            Log::info('Post deleted successfully', [
//                'post_id' => $post->id,
//                'user_id' => auth()->id(),
//            ]);
//
//            return redirect()
//                            ->route('forums.index')
//                            ->with('success', 'Post deleted successfully!');
//        } catch (\Exception $e) {
//            DB::rollBack();
//
//            Log::error('Post deletion failed', [
//                'error' => $e->getMessage(),
//                'post_id' => $post->id,
//            ]);
//
//            return back()->withErrors(['error' => 'Failed to delete post: ' . $e->getMessage()]);
//        }
//    }

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
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:50',
            'description' => 'nullable|string|max:200',
        ]);

        try {
            // Sanitize tag name
            $tagName = strtolower(trim($validated['name']));
            $tagName = preg_replace('/[^a-z0-9\s\-_]/u', '', $tagName);
            $tagName = preg_replace('/\s+/', ' ', $tagName);

            // ✅ 关键：如果系统里已经有同名 tag 且为 pending，禁止其它人再提交，并提示等待审批
            $existing = Tag::query()
                    ->where('name', $tagName)
                    ->first();

            if ($existing) {
                if ($existing->status === 'pending') {
                    return response()->json([
                                'success' => false,
                                'code' => 'TAG_PENDING',
                                'message' => 'This tag is pending admin approval and cannot be used yet.',
                                'tag' => [
                                    'id' => $existing->id,
                                    'name' => $existing->name,
                                    'status' => $existing->status,
                                ],
                                    ], 409);
                }

                // 其它状态（例如 active）：让前端走“选择已有 tag”，这里用 409 也可以
                return response()->json([
                            'success' => false,
                            'code' => 'TAG_EXISTS',
                            'message' => 'This tag already exists. Please select it from the suggestions.',
                            'tag' => [
                                'id' => $existing->id,
                                'name' => $existing->name,
                                'status' => $existing->status,
                            ],
                                ], 409);
            }

            // Generate unique slug
            $slug = Str::slug($tagName);
            $originalSlug = $slug;
            $count = 1;

            while (Tag::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            $tag = Tag::create([
                        'name' => $tagName,
                        'slug' => $slug,
                        'type' => 'community',
                        'status' => 'pending',
                        'description' => $validated['description'] ?? null,
                        'created_by' => auth()->id(),
            ]);

            Log::info('Tag creation requested', [
                'tag_id' => $tag->id,
                'tag_name' => $tagName,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                        'success' => false,
                        'code' => 'TAG_PENDING',
                        'message' => 'Tag submitted for approval. You cannot use it until an admin approves it.',
                        'tag' => [
                            'id' => $tag->id,
                            'name' => $tag->name,
                            'status' => $tag->status,
                        ],
                            ], 409);
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
