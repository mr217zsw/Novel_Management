<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * 订单通知任务
 *
 * 支付成功后异步发送站内信 / 订阅消息 / 短信。
 */
class SendOrderNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function handle(): void
    {
        // 模拟模式：仅记录日志
        if (config('mock.payment')) {
            Log::info('【模拟订单通知】', ['order_no' => $this->order->order_no]);
            return;
        }

        // 生产：
        // 1. 站内信
        // 2. 微信订阅消息
        // 3. 短信
    }
}
