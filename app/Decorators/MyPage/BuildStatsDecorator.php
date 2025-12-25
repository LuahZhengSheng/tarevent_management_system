<?php

namespace App\Decorators\MyPage;

use App\Models\Post;
use App\Models\PostLike;
use App\Models\PostSave;
use App\Models\PostComment;

class BuildStatsDecorator extends BaseMyPageDecorator
{
    public function build(\Illuminate\Http\Request $request, \App\Models\User $user): array
    {
        $data = $this->decorator
            ? $this->decorator->build($request, $user)
            : [];

        $postsQuery = Post::where('user_id', $user->id);

        $stats = [];

        $stats['total_posts']  = (clone $postsQuery)->where('status', 'published')->count();
        $stats['total_drafts'] = (clone $postsQuery)->where('status', 'draft')->count();

        $stats['likes_received'] = PostLike::whereHas('post', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->count();

        $stats['saves_received'] = PostSave::whereHas('post', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->count();

        $stats['likes_given'] = PostLike::where('user_id', $user->id)->count();
        $stats['saves_given'] = PostSave::where('user_id', $user->id)->count();
        $stats['comments']    = PostComment::where('user_id', $user->id)->count();

        $data['stats'] = $stats;

        return $data;
    }
}
