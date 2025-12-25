<?php

namespace App\Decorators\MyPage;

use Illuminate\Http\Request;
use App\Models\User;

class BuildBaseContextDecorator extends BaseMyPageDecorator
{
    public function build(Request $request, User $user): array
    {
        // 先拿到下一个 decorator 的数据（如果有）
        $data = $this->decorator
            ? $this->decorator->build($request, $user)
            : [];

        // 基本参数
        $activeTab = $request->get('tab', 'posts');
        $perPage   = (int) $request->get('per_page', 12);

        // 初始化 tabs 结构，供 blade 使用
        $data['tabs'] = $data['tabs'] ?? [
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

        // 传给 blade 的基础变量
        $data['activeTab'] = $activeTab;
        $data['perPage']   = $perPage;
        $data['user']      = $user;

        // 搜索 / 过滤 / 排序的基础值（后面 ApplySearchSortFilterDecorator 可以覆盖）
        $data['search']  = $data['search']  ?? [
            'q' => $request->get('q', ''),
        ];

        $data['filters'] = $data['filters'] ?? [
            'status'     => $request->get('status', ''),
            'visibility' => $request->get('visibility', ''),
        ];

        $data['sort'] = $data['sort'] ?? [
            'order' => $request->get('sort', 'latest'),
        ];

        // 给前端 JS 用的 endpoint（比如 quick delete）
        $data['endpoints'] = $data['endpoints'] ?? [
            'quick_delete' => route('forums.my-posts.quick-delete'),
        ];

        return $data;
    }
}
