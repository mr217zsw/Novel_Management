<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 管理后台登录鉴权中间件
 *
 * 管理后台使用独立的登录方式（账号密码 + JWT），与小程序端（openid 登录）区分。
 * 校验通过后请求实例附带 user。
 */
class AdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->extractToken($request);

        if (!$token) {
            return response()->json(['code' => 40001, 'message' => '未登录或Token缺失'], 401);
        }

        try {
            $payload = \Tymon\JWTAuth\Facades\JWTAuth::setToken($token)->getPayload();
            $user = \App\Models\User::find($payload['sub']);

            if (!$user || (int) $user->status === 1) {
                return response()->json(['code' => 40002, 'message' => '账号不存在或已被封禁'], 401);
            }

            $request->setUserResolver(fn () => $user);
        } catch (\Exception $e) {
            return response()->json(['code' => 40001, 'message' => 'Token无效或已过期'], 401);
        }

        return $next($request);
    }

    protected function extractToken(Request $request): ?string
    {
        $header = $request->header('Authorization', '');
        if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            return $matches[1];
        }

        return $request->input('token');
    }
}
