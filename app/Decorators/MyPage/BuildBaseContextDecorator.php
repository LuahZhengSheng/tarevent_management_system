<?php

namespace App\Decorators\MyPage;

use Illuminate\Http\Request;
use App\Models\User;

class BuildBaseContextDecorator extends BaseMyPageDecorator
{
    public function build(Request $request, User $user): array
    {
        $data = parent::build($request, $user);

        $data['user'] = $user;
        $data['perPage'] = (int) $request->get('per_page', 12);

        // 给前端 JS 用的 endpoints（避免在 JS 里 hardcode）
        $data['endpoints'] = [
            'quick_delete' => route('forums.my-posts.quick-delete'),
        ];

        return $data;
    }
}
