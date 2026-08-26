<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Log;

/**
 * 支付网关抽象基类
 *
 * 提供模拟支付能力：开发环境 PAYMENT_MOCK_ENABLED=true 时直接返回模拟凭证，
 * 生产环境由子类实现真实 SDK 调用。
 */
abstract class AbstractPaymentGateway implements PaymentGateway
{
    /**
     * 当前是否模拟模式
     */
    protected function isMock(): bool
    {
        return (bool) config('payment.mock', true);
    }

    /**
     * 记录模拟日志
     */
    protected function logMock(string $action, array $data): void
    {
        Log::info("【模拟支付 {$action}】", $data);
    }

    /**
     * 创建模拟订单（子类可覆盖但默认返回通用模拟结构）
     */
    protected function mockCreateOrder(array $params): array
    {
        $this->logMock('创建订单', $params);

        return [
            'mock' => true,
            'order_no' => $params['order_no'],
            'amount' => $params['amount'],
            // 模拟支付所需客户端参数
            'pay_params' => [
                'timeStamp' => time(),
                'nonceStr' => md5(uniqid()),
                'package' => 'prepay_id=mock_' . $params['order_no'],
                'signType' => 'RSA',
                'paySign' => 'mock-signature',
            ],
        ];
    }
}
