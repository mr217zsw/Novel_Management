<?php

namespace App\Services\Payment;

use App\Models\Chapter;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * 支付下单服务
 *
 * 统一创建订单并路由到对应支付网关。
 */
class PaymentService
{
    public function __construct(private PaymentRouter $router)
    {
    }

    /**
     * 创建支付订单
     *
     * @param User $user
     * @param string $platform wechat/douyin/apple
     * @param string $productType recharge/chapter/vip
     * @param int $productId 商品ID 或 章节ID
     * @param array $extra 额外参数（如 openid）
     */
    public function createOrder(User $user, string $platform, string $productType, int $productId, array $extra = []): array
    {
        // 计算金额
        $amount = $this->resolveAmount($productType, $productId);
        if ($amount <= 0) {
            throw new RuntimeException('商品金额无效');
        }

        $productName = $this->resolveProductName($productType, $productId);

        // 创建业务订单
        $order = DB::transaction(function () use ($user, $platform, $productType, $productId, $amount, $productName) {
            return Order::create([
                'order_no' => Order::generateOrderNo($platform),
                'user_id' => $user->id,
                'platform' => $platform,
                'product_type' => $productType,
                'product_id' => $productId,
                'product_name' => $productName,
                'amount' => $amount,
                'pay_amount' => $amount,
                'status' => Order::STATUS_PENDING,
                'expire_time' => now()->addMinutes(config('payment.order_expire', 15)),
            ]);
        });

        // 路由到支付网关
        $gateway = $this->router->route($platform, $productType);
        $payParams = $gateway->createOrder([
            'order_no' => $order->order_no,
            'amount' => $order->amount,
            'product_name' => $productName,
            'user_id' => $user->id,
            'openid' => $extra['openid'] ?? null,
        ]);

        return [
            'order' => $order,
            'pay_params' => $payParams,
        ];
    }

    /**
     * 解析金额（单位分）
     */
    protected function resolveAmount(string $productType, int $productId): float
    {
        return match ($productType) {
            'recharge', 'vip' => (float) (Product::find($productId)?->price ?? 0),
            'chapter' => (float) (Chapter::find($productId)?->price ?? 0),
            default => 0,
        };
    }

    protected function resolveProductName(string $productType, int $productId): string
    {
        return match ($productType) {
            'recharge' => (string) (Product::find($productId)?->name ?? '阅读币充值'),
            'vip' => (string) (Product::find($productId)?->name ?? 'VIP会员'),
            'chapter' => (string) (Chapter::find($productId)?->title ?? '章节解锁'),
            default => '商品',
        };
    }
}
