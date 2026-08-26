<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // 生产环境接入 Sentry / ELK
        });
    }

    public function render($request, Throwable $e)
    {
        // API 接口统一错误格式
        if ($request->is('api/*')) {
            // 校验异常
            if ($e instanceof ValidationException) {
                return response()->json([
                    'code' => 42200,
                    'message' => '参数校验失败',
                    'data' => $e->errors(),
                    'time' => time(),
                ], 422);
            }

            // 认证异常
            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'code' => 40100,
                    'message' => '未登录',
                    'data' => null,
                    'time' => time(),
                ], 401);
            }

            // 业务异常（带 code 字段的 RuntimeException）
            if ($e instanceof \RuntimeException) {
                return response()->json([
                    'code' => $e->getCode() ?: 40000,
                    'message' => $e->getMessage(),
                    'data' => null,
                    'time' => time(),
                ], $this->isHttpStatus($e) ? $e->getCode() : 400);
            }

            // 模型未找到
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json([
                    'code' => 40400,
                    'message' => '资源不存在',
                    'data' => null,
                    'time' => time(),
                ], 404);
            }

            // 生产环境隐藏详细错误
            if (!config('app.debug')) {
                return response()->json([
                    'code' => 50000,
                    'message' => '服务器内部错误',
                    'data' => null,
                    'time' => time(),
                ], 500);
            }
        }

        return parent::render($request, $e);
    }

    /**
     * 重写控制台异常渲染，规避 Laravel 10 + Symfony Console 6.4 的
     * renderThrowable($output) 参数为 null 的兼容性 bug。
     */
    public function renderForConsole($output, Throwable $e)
    {
        // 若 output 为 null，直接输出到 STDERR，避免 Symfony 崩溃
        if (!$output instanceof OutputInterface) {
            fwrite(STDERR, $e->getMessage() . "\n");
            fwrite(STDERR, $e->getTraceAsString() . "\n");
            return;
        }

        parent::renderForConsole($output, $e);
    }

    protected function isHttpStatus(Throwable $e): bool
    {
        $code = $e->getCode();
        return $code >= 400 && $code < 600;
    }
}
