<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ForumUserStatsController;
use Illuminate\Http\Request;

class TestApiController extends Controller
{
    public function testForumUserStats(Request $request)
    {
        // 假设你要测 userId = 1
        $userId    = 1;

        // 按作业要求的格式生成一个 timeStamp：YYYY-MM-DD HH:MM:SS
        $timeStamp = now()->format('Y-m-d H:i:s'); // 等价于 MySQL 的 NOW() 格式 [web:53][web:59]

        // 拿到 API 控制器实例
        $apiController = app(ForumUserStatsController::class);

        // 往当前 Request 里塞入参数，注意这里用 timeStamp，不再用 requestId
        $request->merge([
            'userId'    => $userId,
            'timeStamp' => $timeStamp,
        ]);

        // 直接调用 API 方法
        $response = $apiController->getUserForumStats($request);

        // $response 是 JsonResponse，用 getData(true) 拿到数组形式的数据 [web:54][web:60]
        $data = $response->getData(true);

        // 传给视图，在 Blade 里自己 dd/print_r 或渲染
        return view('test.forum_user_stats', [
            'response' => $data,
        ]);
    }
}
