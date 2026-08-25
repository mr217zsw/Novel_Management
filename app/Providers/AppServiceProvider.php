<?php

namespace App\Providers;

use App\Http\ResponseMacro;
use App\Services\Payment\PaymentRouter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentRouter::class);
    }

    public function boot(): void
    {
        // 统一响应格式
        ResponseMacro::register();
    }
}
