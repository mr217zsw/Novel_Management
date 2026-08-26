<?php

namespace App\Listeners;

use App\Events\UserPaid;
use App\Services\AttributionService;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * 支付成功监听器
 *
 * - 归因首付记录
 * - 通知
 */
class HandleUserPaid implements ShouldQueue
{
    public function __construct(private AttributionService $attributionService)
    {
    }

    public function handle(UserPaid $event): void
    {
        // 归因首付
        $this->attributionService->attributePay($event->user->id, $event->amount);

        // 发送订单通知
        if ($event->orderId) {
            \App\Jobs\SendOrderNotification::dispatch(\App\Models\Order::find($event->orderId));
        }
    }
}
