<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MyPageService;
use App\Services\PostService;
use App\Models\Post;
use App\Models\User;

class MyPostController extends Controller
{
    protected PostService $postService;
    protected MyPageService $myPageService;

    public function __construct(PostService $postService, MyPageService $myPageService)
    {
        $this->middleware(['auth', 'check.active.user']);
        $this->postService = $postService;
        $this->myPageService = $myPageService;
    }

    public function index(Request $request)
    {
        // TODO: 真实环境用 auth()->user()
        $user = auth()->user() ?? User::find(1);

        $viewData = $this->myPageService->build($request, $user);

        return view('forums.my-posts', $viewData);
    }

    public function quickDelete(Request $request)
    {
        $postId = $request->input('post_id');
        $post = Post::findOrFail($postId);

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
