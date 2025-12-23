<?php

namespace App\Decorators\MyPage;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\PostComment;

class BuildTabsDataDecorator extends BaseMyPageDecorator {

    protected ApplySearchSortFilterDecorator $queryDecorator;

    public function __construct(MyPageDecoratorInterface $decorator, ApplySearchSortFilterDecorator $queryDecorator) {
        parent::__construct($decorator);
        $this->queryDecorator = $queryDecorator;
    }

    public function build(Request $request, User $user): array {
        $data = parent::build($request, $user);

        $tab = $data['tab'] ?? 'posts';
        $perPage = $data['perPage'] ?? 12;

        // 默认给 view 的变量（避免 undefined）
        $data['posts'] = null;
        $data['likedPosts'] = null;
        $data['myComments'] = null;
        $data['collections'] = null;

        if ($tab === 'posts') {
            $query = Post::query()
                    ->where('user_id', $user->id)
                    ->with(['user', 'club', 'tags'])   // tags 用于显示，避免 N+1 [file:9]
                    ->withCount(['comments', 'likes']);

            $query = $this->queryDecorator->apply($request, $query, 'posts');

            $data['posts'] = $query->paginate($perPage)->appends($request->query());
        }

        if ($tab === 'likes') {
            // 用户点过赞的帖子（取 posts）
            $postIds = PostLike::query()
                    ->where('user_id', $user->id)
                    ->latest()
                    ->pluck('post_id');

            $query = Post::query()
                    ->whereIn('id', $postIds)
                    ->with(['user', 'club', 'tags'])
                    ->withCount(['comments', 'likes']);

            $query = $this->queryDecorator->apply($request, $query, 'likes');

            $data['likedPosts'] = $query->paginate($perPage)->appends($request->query());
        }

        if ($tab === 'comments') {
            // 我写过的评论（带 post，方便跳转）
            $query = PostComment::query()
                    ->where('user_id', $user->id)
                    ->with(['post', 'post.user'])
                    ->latest();

            $data['myComments'] = $query->paginate($perPage)->appends($request->query());
        }

        if ($tab === 'collections') {
            $query = Post::query()
                    ->join('post_saves', 'post_saves.post_id', '=', 'posts.id')
                    ->where('post_saves.user_id', $user->id)
                    ->select([
                        'posts.*',
                        'post_saves.pinned_at as save_pinned_at',
                        'post_saves.last_viewed_at as save_last_viewed_at',
                        'post_saves.created_at as saved_at',
                    ])
                    ->with(['user', 'club', 'tags'])
                    ->withCount(['comments', 'likes']);

            // 统一在这里套 search/sort（不会套 filter，因为 tab=collections）
            $query = $this->queryDecorator->apply($request, $query, 'collections');

            $data['collections'] = $query->paginate($perPage)->appends($request->query());
        }

        return $data;
    }
}
