<?php

namespace App\Decorators\MyPage;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;

class BuildStatsDecorator extends BaseMyPageDecorator
{
    public function build(Request $request, User $user): array
    {
        $data = parent::build($request, $user);

        $posts = Post::query()->where('user_id', $user->id);

        $data['stats'] = [
            'total_posts' => (clone $posts)->count(),
            'published_posts' => (clone $posts)->published()->count(),
            'draft_posts' => (clone $posts)->draft()->count(),
            'total_views' => (clone $posts)->sum('views_count'),
        ];

        return $data;
    }
}
