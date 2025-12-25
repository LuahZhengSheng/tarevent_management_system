<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class AuthTokenController extends Controller
{
    /**
     * POST /api/v1/auth/token
     * Body: { "email": "...", "password": "...", "requestId": "...", "timeStamp": "YYYY-MM-DD HH:MM:SS" }
     */
    public function issueToken(Request $request)
    {
        $validated = $request->validate([
            'email'     => ['required', 'email'],
            'password'  => ['required', 'string'],
            'requestId' => ['nullable', 'string'],
            'timeStamp' => ['nullable', 'date_format:Y-m-d H:i:s'],
        ]);

        $requestId = $validated['requestId'] ?? null;
        $requestTs = $validated['timeStamp'] ?? null;

        // IFA：requestId 或 timeStamp 至少一个
        if (!$requestId && !$requestTs) {
            return response()->json([
                'status'    => 'F',
                'timeStamp' => Carbon::now()->format('Y-m-d H:i:s'),
                'message'   => 'Missing requestId or timeStamp.',
            ], 422);
        }

        /** @var User|null $user */
        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'status'      => 'F',
                'timeStamp'   => Carbon::now()->format('Y-m-d H:i:s'),
                'requestId'   => $requestId,
                'requestTime' => $requestTs,
                'message'     => 'Invalid credentials.',
            ], 401);
        }

        // ✅ 允许所有用户领取 token，只是 abilities 不同
        // 你可以按实际需要调整这些权限名
        if ($user->isSuperAdmin()) {
            $abilities = ['*'];
        } elseif ($user->isAdmin()) {
            $abilities = ['forum-user-stats:read', 'clubs-posts:read'];
        } elseif ($user->role === 'club') {
            $abilities = ['clubs-posts:read'];
        } else {
            // student / other normal users
            $abilities = ['clubs-posts:read'];
        }

        $token = $user->createToken('external-system', $abilities);

        return response()->json([
            'status'      => 'S',
            'timeStamp'   => Carbon::now()->format('Y-m-d H:i:s'),
            'requestId'   => $requestId,
            'requestTime' => $requestTs,
            'data' => [
                'tokenType'   => 'Bearer',
                'accessToken' => $token->plainTextToken,
                'abilities'   => $abilities,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $requestId = $request->input('requestId');
        $requestTs = $request->input('timeStamp');

        if (empty($requestId) && empty($requestTs)) {
            return response()->json([
                'status'    => 'F',
                'timeStamp' => now()->format('Y-m-d H:i:s'),
                'requestId' => $requestId,
                'message'   => 'Missing requestId or timeStamp.',
            ], 422);
        }

        $user = $request->user();

        // 撤销“当前请求使用的 token”
        $token = $request->user()?->currentAccessToken();
        if ($token) {
            $token->delete();
        }

        return response()->json([
            'status'      => 'S',
            'timeStamp'   => now()->format('Y-m-d H:i:s'),
            'requestId'   => $requestId,
            'requestTime' => $requestTs,
            'message'     => 'Logout successful.',
            'data' => [
                'userId'   => $user?->id,
                'revoked'  => (bool) $token,
            ],
        ]);
    }
}
