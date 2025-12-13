<?php

// app/Http/Controllers/Forum/PostController.php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use App\Http\Requests\Forum\StorePostRequest;
use App\Http\Requests\Forum\UpdatePostRequest;
use App\Decorators\BasePostDecorator;
use App\Decorators\ContentSanitizationDecorator;
use App\Decorators\ValidationPostDecorator;
use App\Decorators\MediaPostDecorator;
use App\Decorators\TagsPostDecorator;
use App\Support\MediaHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PostController extends Controller {

    /**
     * æž„é€ å‡½æ•° - å¼ºåˆ¶ç™»å½• User ID = 1ï¼ˆä»…å¼€å‘çŽ¯å¢ƒï¼‰
     */
    public function __construct() {
        // å¼€å‘çŽ¯å¢ƒè‡ªåŠ¨ç™»å½• User ID = 1
        if (config('app.env') === 'local' && !Auth::check()) {
            $user = User::find(1);
            if ($user) {
                Auth::login($user);
                \Log::info('Auto-logged in User ID = 1 for testing');
            } else {
                \Log::error('User ID = 1 not found in database');
            }
        }
    }

    /**
     * Display a listing of posts
     */
    public function index(Request $request) {
        $query = Post::published()
                ->public()
                ->with(['user', 'category', 'tags', 'club'])
                ->withCount(['comments', 'likes']);

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by tag
        if ($request->filled('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('tags.slug', $request->tag);
            });
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort', 'recent');
        if ($sortBy === 'popular') {
            $query->popular();
        } else {
            $query->recent();
        }

        $posts = $query->paginate(15);
        $categories = Category::active()->ordered()->get();
        $popularTags = Tag::active()->popular(10)->get();

        return view('forums.index', compact('posts', 'categories', 'popularTags'));
    }

    /**
     * Show the form for creating a new post
     */
    public function create() {
        $categories = Category::active()->ordered()->get();
        $activeTags = Tag::active()->orderBy('name')->get();

        // Mock clubs data (replace with real data when User/Club models are ready)
        $userClubs = collect([
            (object) ['id' => 1, 'name' => 'Tech Club', 'member_count' => 45],
            (object) ['id' => 2, 'name' => 'Photography Society', 'member_count' => 32],
            (object) ['id' => 3, 'name' => 'Music Band', 'member_count' => 28],
        ]);

        // When User model is ready, replace with:
        // $userClubs = auth()->user()->clubs ?? collect([]);

        return view('forums.create', compact('categories', 'activeTags', 'userClubs'));
    }

    /**
     * Store a newly created post
     */
    public function store(StorePostRequest $request) {
        try {
            DB::beginTransaction();

            // Build decorator chain (order matters!)
            // 1. Start with base decorator (extracts basic data)
            $baseDecorator = new BasePostDecorator($request);

            // 2. Sanitize content (remove dangerous HTML)
            $sanitizedDecorator = new ContentSanitizationDecorator($baseDecorator);

            // 3. Validate sanitized content
            $validatedDecorator = new ValidationPostDecorator($sanitizedDecorator);

            // 4. Process media files
            $mediaDecorator = new MediaPostDecorator($validatedDecorator);

            // 5. Process tags (last because it creates DB records)
            $tagsDecorator = new TagsPostDecorator($mediaDecorator);

            // Process all data through decorators
            $processedData = $tagsDecorator->process();

            // Extract tag IDs before creating post
            $tagIds = $processedData['tag_ids'] ?? [];
            unset($processedData['tag_ids']);

            // Set user ID
            $processedData['user_id'] = auth()->id();

            // Handle club_ids for club_only visibility
            if ($processedData['visibility'] === 'club_only' && $request->filled('club_ids')) {
                // For now, use first club (update when multi-club support is ready)
                $processedData['club_id'] = $request->input('club_ids')[0];
            } else {
                $processedData['club_id'] = null;
            }

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
                            'post' => $post->load(['category', 'tags']),
                ]);
            }

            // Redirect for normal form submissions
            return redirect()
                            ->route('forums.posts.show', $post->slug)
                            ->with('success', $post->status === 'draft' ? 'Post saved as draft successfully!' : 'Post published successfully!');
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
            'club',
            'comments' => function ($query) {
                $query->with('user')->latest()->limit(10);
            }
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

        // Mock clubs
        $userClubs = collect([
            (object) ['id' => 1, 'name' => 'Tech Club', 'member_count' => 45],
            (object) ['id' => 2, 'name' => 'Photography Society', 'member_count' => 32],
            (object) ['id' => 3, 'name' => 'Music Band', 'member_count' => 28],
        ]);

        // When User model is ready:
        // $userClubs = auth()->user()->clubs ?? collect([]);

        $post->load(['category', 'tags', 'club']);

        return view('forums.edit', compact('post', 'categories', 'activeTags', 'userClubs'));
    }

    /**
     * Update the specified post
     */
    public function update(UpdatePostRequest $request, Post $post) {
        try {
            DB::beginTransaction();

            // Use Decorator Pattern to process data
            $baseDecorator = new BasePostDecorator($request);
            $sanitizedDecorator = new ContentSanitizationDecorator($baseDecorator);
            $validatedDecorator = new ValidationPostDecorator($sanitizedDecorator);
            $mediaDecorator = new MediaPostDecorator($validatedDecorator);
            $tagsDecorator = new TagsPostDecorator($mediaDecorator);

            $processedData = $tagsDecorator->process();

            // Extract tag IDs
            $tagIds = $processedData['tag_ids'] ?? [];
            unset($processedData['tag_ids']);

            // Handle club_ids
            if ($processedData['visibility'] === 'club_only' && $request->filled('club_ids')) {
                $processedData['club_id'] = $request->input('club_ids')[0];
            } else {
                $processedData['club_id'] = null;
            }

            // Handle media replacement or addition
            if ($request->boolean('replace_media', false)) {
                // Delete old media files
                if ($post->hasMedia()) {
                    MediaHelper::deletePostMedia($post->media_paths);
                }
                // Use only new media
                // $processedData['media_paths'] is already set by decorator
            } elseif (isset($processedData['media_paths'])) {
                // Merge with existing media
                $existingMedia = $post->media_paths ?? [];
                $processedData['media_paths'] = array_merge($existingMedia, $processedData['media_paths']);

                // Limit to max count
                if (count($processedData['media_paths']) > MediaHelper::POST_MAX_MEDIA_COUNT) {
                    $processedData['media_paths'] = array_slice(
                            $processedData['media_paths'],
                            0,
                            MediaHelper::POST_MAX_MEDIA_COUNT
                    );
                }
            }

            // Handle published_at timestamp
            if ($processedData['status'] === 'published' && !$post->published_at) {
                $processedData['published_at'] = now();
            } elseif ($processedData['status'] === 'draft') {
                $processedData['published_at'] = null;
            }

            // Update post
            $post->update($processedData);

            // Update tags
            if (!empty($tagIds)) {
                $post->syncTagsWithCount($tagIds, auth()->id());
            } elseif (isset($tagIds)) {
                // Empty array means remove all tags
                $post->tags()->detach();
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
}