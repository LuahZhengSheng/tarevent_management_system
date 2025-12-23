<?php

namespace App\Decorators\MyPage;

use Illuminate\Http\Request;
use App\Models\User;

class BaseMyPageDecorator implements MyPageDecoratorInterface
{
    protected ?MyPageDecoratorInterface $decorator;

    public function __construct(?MyPageDecoratorInterface $decorator = null)
    {
        $this->decorator = $decorator;
    }

    public function build(Request $request, User $user): array
    {
        if ($this->decorator) {
            return $this->decorator->build($request, $user);
        }

        $tab = $request->get('tab', 'posts');

        return [
            'tab' => $tab,
            'filter' => $request->get('filter', 'all'),
            'sort' => $request->get('sort', 'recent'),
            'q' => $request->get('q', ''),
        ];
    }
}
