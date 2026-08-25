<?php

namespace App\Http;

use Illuminate\Support\Facades\Response;

/**
 * 统一响应宏
 *
 * 用法：
 *   response()->success($data, '操作成功');          // 成功
 *   response()->fail('参数错误', 400, 40000);        // 失败
 *
 * 统一格式：{ code, message, data, time }
 */
class ResponseMacro
{
    public static function register(): void
    {
        Response::macro('success', function ($data = null, string $message = 'ok') {
            return response()->json([
                'code' => 0,
                'message' => $message,
                'data' => $data,
                'time' => time(),
            ]);
        });

        Response::macro('fail', function (string $message = 'error', int $httpStatus = 400, int $code = 40000) {
            return response()->json([
                'code' => $code,
                'message' => $message,
                'data' => null,
                'time' => time(),
            ], $httpStatus);
        });
    }
}
