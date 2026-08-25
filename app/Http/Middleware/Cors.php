<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * CORS 跨域中间件
 */
class Cors
{
    public function handle(Request $request, Closure $next)
    {
        $allowedOrigins = explode(',', config('cors.allowed_origins', '*'));
        $origin = $request->header('Origin');

        $allowOrigin = in_array('*', $allowedOrigins, true) || in_array($origin, $allowedOrigins, true)
            ? ($origin ?: '*')
            : '*';

        $response = $next($request);

        $response->headers->set('Access-Control-Allow-Origin', $allowOrigin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-Device-Type');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '86400');

        // 预检请求直接返回
        if ($request->getMethod() === 'OPTIONS') {
            return response('', 204)->withHeaders([
                'Access-Control-Allow-Origin' => $allowOrigin,
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, X-Device-Type',
                'Access-Control-Max-Age' => '86400',
            ]);
        }

        return $response;
    }
}
