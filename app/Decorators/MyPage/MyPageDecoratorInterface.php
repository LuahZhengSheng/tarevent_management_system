<?php

namespace App\Decorators\MyPage;

use Illuminate\Http\Request;
use App\Models\User;

interface MyPageDecoratorInterface
{
    /**
     * Build data for My Page view.
     * Must return an associative array for view().
     */
    public function build(Request $request, User $user): array;
}
