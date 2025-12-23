<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\User;

use App\Decorators\MyPage\BaseMyPageDecorator;
use App\Decorators\MyPage\BuildBaseContextDecorator;
use App\Decorators\MyPage\BuildTabsDataDecorator;
use App\Decorators\MyPage\BuildStatsDecorator;
use App\Decorators\MyPage\BuildActivityDecorator;
use App\Decorators\MyPage\ApplySearchSortFilterDecorator;

class MyPageService
{
    protected $decoratorChain;

    public function __construct()
    {
        $base = new BaseMyPageDecorator();

        $queryDecorator = new ApplySearchSortFilterDecorator($base);

        // chain: BaseContext -> Stats -> Activity -> TabsData
        $this->decoratorChain = new BuildTabsDataDecorator(
            new BuildActivityDecorator(
                new BuildStatsDecorator(
                    new BuildBaseContextDecorator($base)
                )
            ),
            $queryDecorator
        );
    }

    public function build(Request $request, User $user): array
    {
        return $this->decoratorChain->build($request, $user);
    }
}
