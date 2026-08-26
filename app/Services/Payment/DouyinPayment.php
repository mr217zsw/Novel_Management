<?php

namespace App\Services\Payment;

/**
 * 抖音普通支付网关
 *
 * 用于抖音小程序购买阅读币等普通商品（企业认证）。
 * 生产环境：对接抖音开放平台支付。
 */
class DouyinPayment extends AbstractPaymentGateway
{
    public function createOrder(array $params): array
    {
        if ($this->isMock()) {
            return $this->mockCreateOrder($params);
        }

        // 生产：调用抖音支付 API
        throw new \RuntimeException('请配置抖音支付 SDK');
    }

    public function verifyCallback(array $data): bool
    {
        if ($this->isMock()) {
            $this->logMock('抖音普通支付回调验签通过', $data);
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
        return $data['cp_trade_no'] ?? $data['out_order_no'] ?? '';
    }
}
