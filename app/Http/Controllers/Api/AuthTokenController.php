<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class AuthTokenController extends Controller {

    /**
     * POST /api/v1/auth/token
     * Body: { "email": "...", "password": "...", "requestId": "...", "timeStamp": "YYYY-MM-DD HH:MM:SS" }
     */
    public function issueToken(Request $request) {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'requestId' => ['nullable', 'string'],
            'timeStamp' => ['nullable', 'date_format:Y-m-d H:i:s'],
        ]);

        $requestId = $validated['requestId'] ?? null;
        $requestTs = $validated['timeStamp'] ?? null;

        // IFA：requestId 或 timeStamp 至少一个
        if (!$requestId && !$requestTs) {
            return response()->json([
                        'status' => 'F',
                        'timeStamp' => Carbon::now()->format('Y-m-d H:i:s'),
                        'message' => 'Missing requestId or timeStamp.',
                            ], 422);
        }

        /** @var User|null $user */
        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                        'status' => 'F',
                        'timeStamp' => Carbon::now()->format('Y-m-d H:i:s'),
                        'requestId' => $requestId,
                        'requestTime' => $requestTs,
                        'message' => 'Invalid credentials.',
                            ], 401);
        }

        // 只允许 admin / super_admin 领取外部系统 token（按你需求“只有 admin 才能调用”）
        if (!$user->isAdministrator()) {
            return response()->json([
                        'status' => 'F',
                        'timeStamp' => Carbon::now()->format('Y-m-d H:i:s'),
                        'requestId' => $requestId,
                        'requestTime' => $requestTs,
                        'message' => 'Forbidden. Admin only.',
                            ], 403);
        }

        // abilities 可用来做更细权限（可选）
        $abilities = $user->isSuperAdmin() ? ['*'] : ['forum-user-stats:read']; // admin 只能看 student/club（你后面在 stats API 再做目标用户限制）

        $token = $user->createToken('external-system', $abilities);

        return response()->json([
                    'status' => 'S',
                    'timeStamp' => Carbon::now()->format('Y-m-d H:i:s'),
                    'requestId' => $requestId,
                    'requestTime' => $requestTs,
                    'data' => [
                        'tokenType' => 'Bearer',
                        'accessToken' => $token->plainTextToken,
                        'abilities' => $abilities,
                    ],
        ]);
    }

    public function logout(Request $request) {
        $requestId = $request->input('requestId');
        $requestTs = $request->input('timeStamp');

        // IFA：requestId OR timeStamp 至少一个（跟你其它 API 一致的风格）
        if (empty($requestId) && empty($requestTs)) {
            return response()->json([
                        'status' => 'F',
                        'timeStamp' => now()->format('Y-m-d H:i:s'),
                        'requestId' => $requestId,
                        'message' => 'Missing requestId or timeStamp.',
                            ], 422);
        }

        $user = $request->user();

        // 撤销“当前请求使用的那一颗 token”
        $token = $request->user()?->currentAccessToken();
        if ($token) {
            $token->delete(); // 删除 personal_access_tokens 那行，让 token 失效 [file:254]
        }

        return response()->json([
                    'status' => 'S',
                    'timeStamp' => now()->format('Y-m-d H:i:s'),
                    'requestId' => $requestId,
                    'requestTime' => $requestTs,
                    'message' => 'Logout successful.',
                    'data' => [
                        'userId' => $user?->id,
                        'revoked' => (bool) $token,
                    ],
        ]);
    }
}
