<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * JWT 认证中间件（小程序端）
 */
class JwtAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['code' => 40101, 'message' => '用户不存在'], 401);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['code' => 40102, 'message' => '登录已过期'], 401);
        } catch (JWTException $e) {
            return response()->json(['code' => 40100, 'message' => '未登录或Token无效'], 401);
        }

        $request->setUserResolver(fn () => $user);
        $request->attributes->set('auth_user', $user);

        return $next($request);
    }
}
