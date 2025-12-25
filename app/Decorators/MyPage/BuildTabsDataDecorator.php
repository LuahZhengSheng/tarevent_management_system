<?php

namespace App\Decorators\MyPage;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\PostComment;

class BuildTabsDataDecorator extends BaseMyPageDecorator
{
    public function __construct(?MyPageDecoratorInterface $decorator = null)
    {
        parent::__construct($decorator);
    }

    public function build(Request $request, User $user): array
    {
        // 从前面的 decorator 链拿到基础数据（tab、perPage 等）
        $data = parent::build($request, $user);

        $tab     = $data['tab']     ?? 'posts';
        $perPage = $data['perPage'] ?? 12;

        // 先给 view 准备好默认值，避免 undefined
        $data['posts']       = null;
        $data['likedPosts']  = null;
        $data['myComments']  = null;
        $data['collections'] = null;

        // Posts：自己发的帖子
        if ($tab === 'posts') {
            $query = Post::query()
                ->where('user_id', $user->id)
                ->with(['user', 'clubs', 'tags'])
                ->withCount(['comments', 'likes']);

            // 如果你在 ApplySearchSortFilterDecorator 里已经对 posts 做了 search/sort/filter，
            // 那里应该把处理后的 query 放到 $data['queries']['posts'] 等，你也可以改成：
            // $query = $data['queries']['posts'] ?? $query;

            $data['posts'] = $query
                ->paginate($perPage)
                ->appends($request->query());
        }

        // Likes：我点赞过的帖子
        if ($tab === 'likes') {
            $postIds = PostLike::query()
                ->where('user_id', $user->id)
                ->latest()
                ->pluck('post_id');

            $query = Post::query()
                ->whereIn('id', $postIds)
                ->with(['user', 'clubs', 'tags'])
                ->withCount(['comments', 'likes']);

            $data['likedPosts'] = $query
                ->paginate($perPage)
                ->appends($request->query());
        }

        // Comments：我写过的评论
        if ($tab === 'comments') {
            $query = PostComment::query()
                ->where('user_id', $user->id)
                ->with(['post', 'post.user'])
                ->latest();

            $data['myComments'] = $query
                ->paginate($perPage)
                ->appends($request->query());
        }

        // Collections（Saves）：我收藏的帖子
        if ($tab === 'collections' || $tab === 'saves') {
            $query = Post::query()
                ->join('post_saves', 'post_saves.post_id', '=', 'posts.id')
                ->where('post_saves.user_id', $user->id)
                ->select([
                    'posts.*',
                    'post_saves.pinned_at as save_pinned_at',
                    'post_saves.last_viewed_at as save_last_viewed_at',
                    'post_saves.created_at as saved_at',
                ])
                ->with(['user', 'clubs', 'tags'])
                ->withCount(['comments', 'likes']);

            $data['collections'] = $query
                ->paginate($perPage)
                ->appends($request->query());
        }

        return $data;
    }
}
