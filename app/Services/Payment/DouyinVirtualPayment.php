<?php

namespace App\Services\Payment;

/**
 * 抖音虚拟支付网关
 *
 * 用于抖音小程序购买虚拟商品（章节/VIP）。
 * 特点：抖音虚拟支付不抽成，金额固定档位。
 */
class DouyinVirtualPayment extends AbstractPaymentGateway
{
    public function createOrder(array $params): array
    {
        if ($this->isMock()) {
            return $this->mockCreateOrder($params);
        }

        // 生产：对接抖音虚拟支付
        throw new \RuntimeException('请配置抖音虚拟支付 SDK');
    }

    public function verifyCallback(array $data): bool
    {
        if ($this->isMock()) {
            $this->logMock('抖音虚拟支付回调验签通过', $data);
            return true;
        }
        return false;
    }

    public function queryOrder(string $orderNo): array
    {
        if ($this->isMock()) {
            return ['mock' => true, 'status' => 'SUCCESS'];
        }
        return [];
    }

    public function refund(string $orderNo, float $amount): array
    {
        if ($this->isMock()) {
            return ['mock' => true, 'status' => 'refunding'];
        }
        return [];
    }

    public function extractOrderNo(array $data): string
    {
        return $data['cp_trade_no'] ?? $data['out_trade_no'] ?? '';
    }
}
