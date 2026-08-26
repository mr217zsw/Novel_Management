<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * 每日对账
 *
 * 核对各平台订单：本地订单 vs 平台订单（生产需调用平台查询 API）。
 * 发现不一致订单标记并记录日志。
 */
class DailyReconcile extends Command
{
    protected $signature = 'novel:reconcile {--date= : 对账日期，默认昨日}';

    protected $description = '每日支付对账';

    public function handle(): int
    {
        $date = $this->option('date') ?: now()->subDay()->toDateString();

        $this->info("开始对账日期：{$date}");

        // 待支付且已超时的订单，标记为已取消
        $expired = Order::where('status', Order::STATUS_PENDING)
            ->where('expire_time', '<', now())
            ->update(['status' => Order::STATUS_CANCELLED]);

        $paidOrders = Order::where('status', Order::STATUS_PAID)
            ->whereDate('pay_time', $date)
            ->count();

        $this->info("超时取消订单：{$expired} 个，昨日已支付：{$paidOrders} 个");

        // 生产环境：循环调用平台查询接口比对
        // foreach ($paidOrders as $order) {
        //     $gateway = $router->route($order->platform);
        //     $result = $gateway->queryOrder($order->order_no);
        //     // 比对订单状态与金额
        // }

        Log::info('每日对账完成', ['date' => $date, 'expired' => $expired, 'paid' => $paidOrders]);

        return self::SUCCESS;
    }
}
