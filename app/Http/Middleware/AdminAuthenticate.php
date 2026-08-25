<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * 管理后台认证中间件
 *
 * 与 JwtAuthenticate 类似，但校验用户必须为后台用户（非封禁）。
 * 登录路由放在 admin.auth 组内但单独放行。
 */
class AdminAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        // 登录路由放行
        if ($request->is('api/admin/login')) {
            return $next($request);
        }

        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['code' => 40101, 'message' => '用户不存在'], 401);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['code' => 40102, 'message' => '登录已过期'], 401);
        } catch (JWTException $e) {
            return response()->json(['code' => 40100, 'message' => '未登录或Token无效'], 401);
        }

        if ($user->isBanned()) {
            return response()->json(['code' => 40103, 'message' => '账号已被封禁'], 403);
        }

        $request->setUserResolver(fn () => $user);
        $request->attributes->set('auth_user', $user);

        return $next($request);
    }
}
