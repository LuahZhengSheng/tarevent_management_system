<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\PostSave;
use App\Models\PostComment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MyPostController extends Controller {

    public function __construct() {
        $this->middleware(['auth', 'check.active.user']);
    }

    /**
     * 我的帖子首页（GET）
     */
    public function index(Request $request) {
        $user = $request->user();
        $activeTab = $request->get('tab', 'posts');

        // 搜索 / 筛选 / 排序参数（和 Blade 对应）
        $search = [
            'q' => $request->get('q', ''),
        ];

        $filters = [
            'status' => $request->get('status', ''),
            'visibility' => $request->get('visibility', ''),
        ];

        $sort = [
            'order' => $request->get('sort', 'latest'),
        ];

        // 构建 tabs 数据
        $tabs = $this->buildTabsData($user, $activeTab, $search, $filters, $sort);
        $stats = $this->getUserStats($user);

        // AJAX 请求给 my-posts.js 使用
        if ($request->ajax() || $request->get('ajax')) {
            return $this->ajaxResponse($tabs, $activeTab);
        }

        return view('forums.my-posts', [
            'user' => $user,
            'tabs' => $tabs,
            'activeTab' => $activeTab,
            'search' => $search,
            'filters' => $filters,
            'sort' => $sort,
            'stats' => $stats,
        ]);
    }

    /**
     * 快速删除帖子（POST）
     */
    public function quickDelete(Request $request) {
        if (!$request->isMethod('post')) {
            abort(405);
        }

        $postId = $request->input('post_id');
        $post = Post::findOrFail($postId);

        if ($post->user_id !== $request->user()->id) {
            abort(403);
        }

        try {
            $post->delete();

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

    /**
     * 构建 Tabs 数据
     */
    protected function buildTabsData($user, $activeTab, $search, $filters, $sort) {
        $tabs = [
            'posts' => [
                'label' => 'Posts',
                'count' => 0,
                'items' => [],
            ],
            'drafts' => [
                'label' => 'Drafts',
                'count' => 0,
                'items' => [],
            ],
            'likes' => [
                'label' => 'Likes',
                'count' => 0,
                'items' => [],
            ],
            'saves' => [
                'label' => 'Saves',
                'count' => 0,
                'items' => [],
            ],
            'comments' => [
                'label' => 'Comments',
                'count' => 0,
                'items' => [],
            ],
        ];

        // 当前 tab 的数据
        switch ($activeTab) {
            case 'posts':
                $tabs['posts'] = $this->getPostsData($user, $search, $filters, $sort);
                break;
            case 'drafts':
                $tabs['drafts'] = $this->getDraftsData($user, $search, $sort);
                break;
            case 'likes':
                $tabs['likes'] = $this->getLikesData($user, $search, $sort);
                break;
            case 'saves':
                $tabs['saves'] = $this->getSavesData($user, $search, $sort);
                break;
            case 'comments':
                $tabs['comments'] = $this->getCommentsData($user, $search, $sort);
                break;
        }

        // 各 tab 计数
        $tabs['posts']['count'] = $this->getPostsCount($user);
        $tabs['drafts']['count'] = $this->getDraftsCount($user);
        $tabs['likes']['count'] = $this->getLikesCount($user);
        $tabs['saves']['count'] = $this->getSavesCount($user);
        $tabs['comments']['count'] = $this->getCommentsCount($user);

        return $tabs;
    }

    /**
     * Posts tab 数据
     */
    protected function getPostsData($user, $search, $filters, $sort) {
        $query = $user->posts()->where('status', 'published');

        // 搜索
        if (!empty($search['q'])) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search['q'] . '%')
                        ->orWhere('content', 'like', '%' . $search['q'] . '%');
            });
        }

        // 状态筛选
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // 可见性筛选
        if (!empty($filters['visibility'])) {
            $query->where('visibility', $filters['visibility']);
        }

        // 排序
        switch ($sort['order'] ?? 'latest') {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'most_liked':
                $query->withCount('likes')->orderBy('likes_count', 'desc');
                break;
            case 'most_commented':
                $query->withCount('comments')->orderBy('comments_count', 'desc');
                break;
            case 'latest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // 这里把 with(['media', ...]) 改成只 preload 真正的关系
        $posts = $query->with(['category', 'clubs'])->get();

        return [
            'label' => 'Posts',
            'count' => $posts->count(),
            'items' => $posts->map(function (Post $post) {
                return [
            'post' => $post,
            'excerpt' => \Illuminate\Support\Str::limit(strip_tags($post->content), 150),
            // 这里直接用访问器属性 media（来自 getMediaAttribute）
            'media' => $post->media->map(function ($media) {
                return [
            'type' => $media->type ?? 'image',
            'url' => $media->url ?? '',
                ];
            })->toArray(),
            // 这些关系在 Post 模型里是真实关系：comments / likes / saves
            'comments_count' => $post->comments()->count(),
            'likes_count' => $post->likes()->count(),
            'saves_count' => $post->saves()->count(),
                ];
            }),
        ];
    }

    /**
     * Drafts tab
     */
    protected function getDraftsData($user, $search, $sort) {
        $query = $user->posts()->where('status', 'draft');

        if (!empty($search['q'])) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search['q'] . '%')
                        ->orWhere('content', 'like', '%' . $search['q'] . '%');
            });
        }

        $query->orderBy('updated_at', 'desc');

        $drafts = $query->get();

        return [
            'label' => 'Drafts',
            'count' => $drafts->count(),
            'items' => $drafts->map(function (Post $post) {
                return [
            'post' => $post,
            'excerpt' => Str::limit(strip_tags($post->content), 150),
                ];
            }),
        ];
    }

    /**
     * Likes tab
     */
    protected function getLikesData($user, $search, $sort) {
        // User.php 里是 postLikes()
        $query = $user->postLikes()->with('post');

        if (!empty($search['q'])) {
            $query->whereHas('post', function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search['q'] . '%');
            });
        }

        $query->orderBy('created_at', 'desc');

        $likes = $query->get();

        return [
            'label' => 'Likes',
            'count' => $likes->count(),
            'items' => $likes->map(function (PostLike $like) {
                $post = $like->post;
                return [
            'post' => $post,
            'excerpt' => Str::limit(strip_tags($post->content), 150),
            'comments_count' => $post->comments()->count(),
            'likes_count' => $post->likes()->count(),
            'created_at' => $like->created_at,
                ];
            }),
        ];
    }

    /**
     * Saves tab
     */
    protected function getSavesData($user, $search, $sort) {
        $query = $user->postSaves()->with('post');

        if (!empty($search['q'])) {
            $query->whereHas('post', function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search['q'] . '%');
            });
        }

        $query->orderBy('created_at', 'desc');

        $saves = $query->get();

        return [
            'label' => 'Saves',
            'count' => $saves->count(),
            'items' => $saves->map(function (PostSave $save) {
                $post = $save->post;
                return [
            'post' => $post,
            'excerpt' => Str::limit(strip_tags($post->content), 150),
            'comments_count' => $post->comments()->count(),
            'likes_count' => $post->likes()->count(),
            'created_at' => $save->created_at,
                ];
            }),
        ];
    }

    /**
     * Comments tab
     */
    protected function getCommentsData($user, $search, $sort) {
        // User.php 里是 postComments()
        $query = $user->postComments()->with('post');

        if (!empty($search['q'])) {
            $query->whereHas('post', function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search['q'] . '%');
            });
        }

        $query->orderBy('created_at', 'desc');

        $comments = $query->get();

        return [
            'label' => 'Comments',
            'count' => $comments->count(),
            'items' => $comments->map(function (PostComment $comment) {
                return [
            'comment' => $comment,
            'post' => $comment->post,
                ];
            }),
        ];
    }

    /**
     * AJAX response（my-posts.js 用）
     */
    protected function ajaxResponse($tabs, $activeTab) {
        $html = view('forums.partials.my-posts-tab', [
            'tabs' => $tabs,
            'activeTab' => $activeTab,
                ])->render();

        return response()->json([
                    'success' => true,
                    'html' => $html,
                    'content' => $html,
                    'tabs' => array_map(fn($tab) => ['count' => $tab['count']], $tabs),
        ]);
    }

    /**
     * 顶部统计数据
     */
    protected function getUserStats($user) {
        return [
            'total_posts' => $user->posts()->where('status', 'published')->count(),
            'total_drafts' => $user->posts()->where('status', 'draft')->count(),
            'total_likes_received' => $user->posts()
                    ->withCount('likes')
                    ->get()
                    ->sum('likes_count'),
            'total_saves_received' => $user->posts()
                    ->withCount('saves')
                    ->get()
                    ->sum('saves_count'),
            'unread_notifications' => $user->unreadNotifications()->count(),
        ];
    }

    // count helpers
    protected function getPostsCount($user) {
        return $user->posts()->where('status', 'published')->count();
    }

    protected function getDraftsCount($user) {
        return $user->posts()->where('status', 'draft')->count();
    }

    protected function getLikesCount($user) {
        return $user->postLikes()->count();
    }

    protected function getSavesCount($user) {
        return $user->postSaves()->count();
    }

    protected function getCommentsCount($user) {
        return $user->postComments()->count();
    }
}
