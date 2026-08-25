<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * 苹果 IAP 支付网关
 *
 * 用于 iOS App 内购买（虚拟商品）。
 * 苹果抽成 30%，服务端通过 verifyReceipt 校验收据。
 */
class AppleIAPPayment extends AbstractPaymentGateway
{
    public function createOrder(array $params): array
    {
        // 苹果 IAP 无服务端下单，客户端创建后返回收据即可
        if ($this->isMock()) {
            $this->logMock('创建 IAP 订单', $params);
            return [
                'mock' => true,
                'order_no' => $params['order_no'],
                'note' => 'Apple IAP 需客户端发起购买并回传收据',
            ];
        }

        throw new \RuntimeException('Apple IAP 客户端购买流程');
    }

    public function verifyCallback(array $data): bool
    {
        // 苹果 IAP 通过 verifyReceipt 校验，不适用普通回调
        return false;
    }

    /**
     * 校验苹果收据
     *
     * @param string $receiptData base64 收据
     */
    public function verifyReceipt(string $receiptData): array
    {
        if ($this->isMock()) {
            $this->logMock('验证苹果收据（模拟通过）', ['receipt' => substr($receiptData, 0, 20)]);
            return [
                'status' => 0,
                'environment' => 'Sandbox',
                'mock' => true,
            ];
        }

        $url = config('payment.apple.environment', 'sandbox') === 'production'
            ? config('payment.apple.production_url')
            : config('payment.apple.sandbox_url');

        $response = Http::post($url, [
            'receipt-data' => $receiptData,
            'password' => config('payment.apple.shared_secret'),
            'exclude-old-transactions' => true,
        ]);

        $result = $response->json();

        // status=21007 表示沙箱收据发给生产，需切换环境重试
        if (($result['status'] ?? -1) === 21007 && config('payment.apple.environment') === 'production') {
            $response = Http::post(config('payment.apple.sandbox_url'), [
                'receipt-data' => $receiptData,
                'password' => config('payment.apple.shared_secret'),
            ]);
            $result = $response->json();
        }

        return $result;
    }

    public function queryOrder(string $orderNo): array
    {
        return ['mock' => true, 'status' => 'pending'];
    }

    public function refund(string $orderNo, float $amount): array
    {
        return ['mock' => true, 'status' => 'refunding'];
    }

    public function extractOrderNo(array $data): string
    {
        return $data['transaction_id'] ?? $data['order_no'] ?? '';
    }
}
