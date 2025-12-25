<?php

namespace App\Decorators\MyPage;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\PostComment;

class BuildActivityDecorator extends BaseMyPageDecorator
{
    public function build(Request $request, User $user): array
    {
        $data = parent::build($request, $user);

        $myPostIds = Post::query()
            ->where('user_id', $user->id)
            ->pluck('id');

        // Likes to my posts (exclude self-like)
        $likes = PostLike::query()
            ->whereIn('post_id', $myPostIds)
            ->where('user_id', '!=', $user->id)
            ->with(['user', 'post'])
            ->latest()
            ->limit(10)
            ->get();

        // Comments to my posts (exclude my own comments)
        $comments = PostComment::query()
            ->whereIn('post_id', $myPostIds)
            ->where('user_id', '!=', $user->id)
            ->with(['user', 'post'])
            ->latest()
            ->limit(10)
            ->get();

        $items = [];

        foreach ($likes as $like) {
            if (!$like->post) continue;
            $items[] = [
                'type' => 'like',
                'from_user' => $like->user?->name ?? 'Someone',
                'post_title' => $like->post->title,
                'post_url' => route('forums.posts.show', $like->post),
                'time' => optional($like->created_at)->diffForHumans(),
            ];
        }

        foreach ($comments as $comment) {
            if (!$comment->post) continue;
            $items[] = [
                'type' => 'comment',
                'from_user' => $comment->user?->name ?? 'Someone',
                'post_title' => $comment->post->title,
                'post_url' => route('forums.posts.show', $comment->post),
                'time' => optional($comment->created_at)->diffForHumans(),
            ];
        }

        // recent first
        usort($items, function ($a, $b) {
            return strcmp($b['time'] ?? '', $a['time'] ?? '');
        });

        $data['notifyStats'] = [
            'likes' => $likes->count(),
            'comments' => $comments->count(),
            'total' => $likes->count() + $comments->count(),
            'items' => array_slice($items, 0, 10),
        ];

        return $data;
    }
}
