<?php

namespace App\Decorators\MyPage;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ApplySearchSortFilterDecorator extends BaseMyPageDecorator
{
    public function apply(Request $request, Builder $query, string $tab): Builder
    {
        $q = trim((string) $request->get('q', ''));
        $sort = $request->get('sort', 'recent');

        // -------------------------
        // Search
        // -------------------------
        if ($q !== '' && in_array($tab, ['posts', 'likes', 'collections'], true)) {
            // collections 由于 join 了 post_saves，建议用 posts. 前缀避免 column ambiguity
            $titleCol = $tab === 'collections' ? 'posts.title' : 'title';
            $contentCol = $tab === 'collections' ? 'posts.content' : 'content';

            $query->where(function ($sub) use ($q, $titleCol, $contentCol) {
                $sub->where($titleCol, 'like', "%{$q}%")
                    ->orWhere($contentCol, 'like', "%{$q}%");
            });
        }

        // -------------------------
        // Filter (only for posts)
        // -------------------------
        if ($tab === 'posts') {
            $filter = $request->get('filter', 'all');
            if ($filter === 'draft') {
                $query->draft();
            } elseif ($filter === 'published') {
                $query->published();
            }
        }

        // -------------------------
        // Sort
        // -------------------------
        if ($tab === 'collections') {
            // Collections 的“recent”应该是：置顶 > 最近查看 > 最近收藏
            if ($sort === 'views') {
                $query->orderBy('posts.views_count', 'desc')->orderBy('post_saves.created_at', 'desc');
            } elseif ($sort === 'popular') {
                $query->orderByRaw('(posts.likes_count * 3 + posts.comments_count * 2 + posts.views_count) DESC');
            } else {
                $query->orderByDesc('post_saves.pinned_at')
                      ->orderByDesc('post_saves.last_viewed_at')
                      ->orderByDesc('post_saves.created_at');
            }

            return $query;
        }

        // posts / likes 默认排序逻辑（沿用你 Post scopes）
        if ($sort === 'popular') {
            $query->popular();
        } elseif ($sort === 'views') {
            $query->orderBy('views_count', 'desc')->orderBy('created_at', 'desc');
        } else {
            $query->recent();
        }

        return $query;
    }

    public function build(Request $request, User $user): array
    {
        return parent::build($request, $user);
    }
}
