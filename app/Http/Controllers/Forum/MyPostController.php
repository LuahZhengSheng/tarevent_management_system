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
     * My Posts Index Page (GET)
     */
    public function index(Request $request) {
        $user = $request->user();
        $activeTab = $request->get('tab', 'posts');

        // Search / Filter / Sort parameters
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

        // Build tabs data
        $tabs = $this->buildTabsData($user, $activeTab, $search, $filters, $sort);
        $stats = $this->getUserStats($user);

        // AJAX request for my-posts.js
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
     * Quick Delete Post (POST)
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
     * Build Tabs Data
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

        // Load current tab data
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

        // Update all tab counts
        $tabs['posts']['count'] = $this->getPostsCount($user);
        $tabs['drafts']['count'] = $this->getDraftsCount($user);
        $tabs['likes']['count'] = $this->getLikesCount($user);
        $tabs['saves']['count'] = $this->getSavesCount($user);
        $tabs['comments']['count'] = $this->getCommentsCount($user);

        return $tabs;
    }

    /**
     * Posts Tab Data
     */
    protected function getPostsData($user, $search, $filters, $sort) {
        $query = $user->posts()->where('status', 'published');

        // Search
        if (!empty($search['q'])) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search['q'] . '%')
                    ->orWhere('content', 'like', '%' . $search['q'] . '%');
            });
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Visibility filter
        if (!empty($filters['visibility'])) {
            $query->where('visibility', $filters['visibility']);
        }

        // Sorting
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

        $posts = $query->with(['category', 'clubs'])->get();

        return [
            'label' => 'Posts',
            'count' => $posts->count(),
            'items' => $posts->map(function (Post $post) {
                return [
                    'post' => $post,
                    'excerpt' => Str::limit(strip_tags($post->content), 150),
                    'media' => $post->media->map(function ($m) {
                        $type = is_object($m) ? ($m->type ?? 'image') : ($m['type'] ?? 'image');
                        $url = is_object($m) ? ($m->url ?? '') : ($m['url'] ?? '');

                        return [
                            'mediatype' => $type,
                            'mediaurl' => $url,
                            'thumbnailurl' => $url,
                        ];
                    })->filter(function ($m) {
                        return !empty($m['mediaurl']);
                    })->values()->toArray(),
                    'comments_count' => $post->comments()->count(),
                    'likes_count' => $post->likes()->count(),
                    'saves_count' => $post->saves()->count(),
                ];
            }),
        ];
    }

    /**
     * Drafts Tab Data
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
     * Likes Tab Data
     * FIXED: Handle soft-deleted posts
     */
    protected function getLikesData($user, $search, $sort) {
        // Include soft-deleted posts using withTrashed()
        $query = $user->postLikes()->with(['post' => function($q) {
            $q->withTrashed(); // Include soft-deleted posts
        }]);

        if (!empty($search['q'])) {
            $query->whereHas('post', function ($q) use ($search) {
                $q->withTrashed() // Search in soft-deleted posts too
                  ->where('title', 'like', '%' . $search['q'] . '%');
            });
        }

        $query->orderBy('created_at', 'desc');

        $likes = $query->get();

        return [
            'label' => 'Likes',
            'count' => $likes->count(),
            'items' => $likes->filter(function (PostLike $like) {
                // Filter out likes where post is completely missing (hard deleted)
                return $like->post !== null;
            })->map(function (PostLike $like) {
                $post = $like->post;
                
                return [
                    'post' => $post,
                    'excerpt' => Str::limit(strip_tags($post->content), 150),
                    'comments_count' => $post->comments()->count(),
                    'likes_count' => $post->likes()->count(),
                    'created_at' => $like->created_at,
                    'is_deleted' => $post->trashed(), // Flag for UI display
                ];
            })->values(), // Re-index array after filter
        ];
    }

    /**
     * Saves Tab Data
     * FIXED: Handle soft-deleted posts
     */
    protected function getSavesData($user, $search, $sort) {
        // Include soft-deleted posts
        $query = $user->postSaves()->with(['post' => function($q) {
            $q->withTrashed();
        }]);

        if (!empty($search['q'])) {
            $query->whereHas('post', function ($q) use ($search) {
                $q->withTrashed()
                  ->where('title', 'like', '%' . $search['q'] . '%');
            });
        }

        $query->orderBy('created_at', 'desc');

        $saves = $query->get();

        return [
            'label' => 'Saves',
            'count' => $saves->count(),
            'items' => $saves->filter(function (PostSave $save) {
                // Filter out saves where post is completely missing
                return $save->post !== null;
            })->map(function (PostSave $save) {
                $post = $save->post;
                
                return [
                    'post' => $post,
                    'excerpt' => Str::limit(strip_tags($post->content), 150),
                    'comments_count' => $post->comments()->count(),
                    'likes_count' => $post->likes()->count(),
                    'created_at' => $save->created_at,
                    'is_deleted' => $post->trashed(), // Flag for UI display
                ];
            })->values(),
        ];
    }

    /**
     * Comments Tab Data
     * FIXED: Handle soft-deleted posts
     */
    protected function getCommentsData($user, $search, $sort) {
        // Include soft-deleted posts
        $query = $user->postComments()->with(['post' => function($q) {
            $q->withTrashed();
        }]);

        if (!empty($search['q'])) {
            $query->whereHas('post', function ($q) use ($search) {
                $q->withTrashed()
                  ->where('title', 'like', '%' . $search['q'] . '%');
            });
        }

        $query->orderBy('created_at', 'desc');

        $comments = $query->get();

        return [
            'label' => 'Comments',
            'count' => $comments->count(),
            'items' => $comments->filter(function (PostComment $comment) {
                // Filter out comments where post is completely missing
                return $comment->post !== null;
            })->map(function (PostComment $comment) {
                return [
                    'comment' => $comment,
                    'post' => $comment->post,
                    'is_deleted' => $comment->post->trashed(), // Flag for UI display
                ];
            })->values(),
        ];
    }

    /**
     * AJAX Response (for my-posts.js)
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
     * User Stats (for header)
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

    // =====================================================
    // Count Helpers
    // =====================================================
    
    protected function getPostsCount($user) {
        return $user->posts()->where('status', 'published')->count();
    }

    protected function getDraftsCount($user) {
        return $user->posts()->where('status', 'draft')->count();
    }

    /**
     * FIXED: Count likes even if posts are soft-deleted
     */
    protected function getLikesCount($user) {
        return $user->postLikes()
            ->whereHas('post', function($q) {
                $q->withTrashed(); // Count likes on soft-deleted posts too
            })
            ->count();
    }

    /**
     * FIXED: Count saves even if posts are soft-deleted
     */
    protected function getSavesCount($user) {
        return $user->postSaves()
            ->whereHas('post', function($q) {
                $q->withTrashed();
            })
            ->count();
    }

    /**
     * FIXED: Count comments even if posts are soft-deleted
     */
    protected function getCommentsCount($user) {
        return $user->postComments()
            ->whereHas('post', function($q) {
                $q->withTrashed();
            })
            ->count();
    }
}