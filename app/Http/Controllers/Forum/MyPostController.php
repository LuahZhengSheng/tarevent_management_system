<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Services\PostService;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class MyPostController extends Controller
{
    protected PostService $postService;

    public function __construct(PostService $postService)
    {
        // Comment out auth middleware for now
        // $this->middleware(['auth', 'check.active.user']);
        $this->postService = $postService;
    }

    /**
     * Display user's posts (My Posts page)
     */
    public function index(Request $request)
    {
        // Mock user for testing - replace with auth()->user() later
        $user = User::find(1); // or create a mock user
        
        if (!$user) {
            // Create a mock user if needed
            $user = User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
                'role' => 'student',
            ]);
        }

        $filter = $request->get('filter', 'all'); // all, published, draft

        // Get posts based on filter
        if ($filter === 'draft') {
            $posts = $this->postService->getUserDrafts($user, 12);
        } elseif ($filter === 'published') {
            $posts = $this->postService->getUserPublishedPosts($user, 12);
        } else {
            $posts = $this->postService->getUserPosts($user, 12);
        }

        // Get user statistics
        $stats = $this->postService->getUserPostStats($user);

        return view('forums.my-posts', compact('posts', 'stats', 'filter'));
    }

    /**
     * Quick delete post via AJAX
     */
    public function quickDelete(Request $request)
    {
        $postId = $request->input('post_id');
        $post = Post::findOrFail($postId);

        // Authorization check (commented for now)
        // if (!$post->canBeEditedBy(auth()->user())) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Unauthorized access.',
        //     ], 403);
        // }

        try {
            $this->postService->deletePost($post);

            return response()->json([
                'success' => true,
                'message' => 'Post deleted successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete post.',
            ], 500);
        }
    }
}