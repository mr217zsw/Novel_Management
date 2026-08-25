<?php

namespace App\Services\Payment;

use App\Events\UserPaid;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * 支付回调统一处理器
 *
 * 统一流程：验签 → 幂等加锁 → 事务发货 → 触发事件。
 * 支持微信/抖音/苹果（苹果通过 verifyReceipt 单独处理）。
 */
class PaymentCallbackHandler
{
    public function __construct(private PaymentRouter $router)
    {
    }

    /**
     * 处理支付回调
     *
     * @param string $platform wechat/douyin
     * @return string 返回给平台的 ACK（SUCCESS / FAIL）
     */
    public function handle(Request $request, string $platform): string
    {
        // 模拟模式：直接成功（可强制指定订单号用于测试）
        if (config('payment.mock')) {
            $orderNo = $request->input('order_no', $request->input('out_trade_no'));
            if ($orderNo) {
                $this->markPaid($orderNo, $request->input('transaction_id', 'mock_' . uniqid()));
            }
            return 'SUCCESS';
        }

        $gateway = $this->router->route($platform);

        $data = $request->all();

        // 1. 验签
        if (!$gateway->verifyCallback($data)) {
            Log::warning('支付回调验签失败', ['platform' => $platform]);
            return 'FAIL';
        }

        // 2. 提取业务订单号
        $orderNo = $gateway->extractOrderNo($data);

        // 3. 幂等加锁（防止重复回调）
        $lockKey = "payment:callback:{$orderNo}";
        $lockAcquired = Redis::set($lockKey, '1', 'EX', 60, 'NX');

        if (!$lockAcquired) {
            return 'SUCCESS'; // 已在处理中
        }

        try {
            $this->markPaid($orderNo, $data['transaction_id'] ?? $platform . '_' . uniqid(), $data);
        } finally {
            Redis::del($lockKey);
        }

        return 'SUCCESS';
    }

    /**
     * 将订单标记为已支付并发货
     */
    protected function markPaid(string $orderNo, string $platformOrderId, array $callbackData = []): void
    {
        DB::transaction(function () use ($orderNo, $platformOrderId, $callbackData) {
            $order = Order::where('order_no', $orderNo)->lockForUpdate()->first();

            if (!$order || $order->status === Order::STATUS_PAID) {
                return; // 幂等：已处理
            }

            $order->status = Order::STATUS_PAID;
            $order->platform_order_id = $platformOrderId;
            $order->pay_time = now();
            if ($callbackData) {
                $order->callback_data = $callbackData;
            }
            $order->save();

            // 发货
            $this->deliver($order);

            // 触发归因 / 分成等事件
            event(new UserPaid($order->user, (float) $order->pay_amount, $order->platform, $order->id));
        });
    }

    /**
     * 订单发货
     */
    protected function deliver(Order $order): void
    {
        $user = $order->user;

        switch ($order->product_type) {
            case 'recharge':
                // 充值：增加阅读币余额
                User::where('id', $order->user_id)
                    ->increment('balance', $order->pay_amount);
                break;

            case 'chapter':
                // 章节解锁
                \App\Models\UserChapterPurchase::firstOrCreate([
                    'user_id' => $order->user_id,
                    'chapter_id' => $order->product_id,
                ], [
                    'order_id' => $order->id,
                    'price' => $order->pay_amount,
                ]);
                break;

            case 'vip':
                // VIP 开通：累加会员天数
                $days = (int) $order->product_name ?: 30;
                $expireAt = $user->isVip()
                    ? $user->vip_expire_at->addDays($days)
                    : now()->addDays($days);

                User::where('id', $order->user_id)->update(['vip_expire_at' => $expireAt]);
                break;
        }
    }
}
