<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

/**
 * 权限校验中间件
 *
 * 用法：->middleware('permission:channel.create')
 *
 * 超级管理员直接放行；普通用户读取缓存中的权限码集合。
 * 权限码缓存 Redis 300 秒，角色变更时清除。
 */
class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permissionCode)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['code' => 40100, 'message' => '未登录'], 401);
        }

        // 超级管理员拥有所有权限
        if ($user->is_super_admin) {
            return $next($request);
        }

        $cacheKey = "user:permissions:{$user->id}";
        $permissions = Redis::get($cacheKey);

        if ($permissions) {
            $permissions = json_decode($permissions, true);
        } else {
            $permissions = DB::table('user_roles')
                ->where('user_roles.user_id', $user->id)
                ->join('role_permissions', 'user_roles.role_id', '=', 'role_permissions.role_id')
                ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
                ->pluck('permissions.code')
                ->toArray();

            Redis::setex($cacheKey, 300, json_encode($permissions));
        }

        if (!in_array($permissionCode, $permissions, true)) {
            return response()->json(['code' => 40003, 'message' => '权限不足'], 403);
        }

        return $next($request);
    }
}
