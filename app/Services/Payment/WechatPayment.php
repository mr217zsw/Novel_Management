<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Log;

/**
 * 微信支付网关
 *
 * 用于微信小程序购买阅读币/章节/VIP。
 * 生产环境：对接微信支付 v3 API（JSAPI 下单 + 回调验签）。
 */
class WechatPayment extends AbstractPaymentGateway
{
    public function createOrder(array $params): array
    {
        if ($this->isMock()) {
            return $this->mockCreateOrder($params);
        }

        // 生产：调用微信支付 v3 统一下单 JSAPI
        // $client = new \WechatPay\GuzzleMiddleware\WechatPayMiddleware(...);
        // $response = $client->post('/v3/pay/transactions/jsapi', [
        //     'json' => [
        //         'appid' => config('payment.wechat.app_id'),
        //         'mchid' => config('payment.wechat.mch_id'),
        //         'description' => $params['product_name'],
        //         'out_trade_no' => $params['order_no'],
        //         'notify_url' => config('payment.wechat.notify_url'),
        //         'amount' => ['total' => (int) round($params['amount']), 'currency' => 'CNY'],
        //         'payer' => ['openid' => $params['openid']],
        //     ],
        // ]);

        throw new \RuntimeException('请配置微信支付 SDK');
    }

    public function verifyCallback(array $data): bool
    {
        if ($this->isMock()) {
            $this->logMock('回调验签通过', $data);
            return true;
        }

        // 生产：使用微信支付 APIv3 密钥验签
        return false;
    }

    public function queryOrder(string $orderNo): array
    {
        if ($this->isMock()) {
            return ['mock' => true, 'status' => 'SUCCESS', 'order_no' => $orderNo];
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
        // 微信回调：out_trade_no
        return $data['out_trade_no'] ?? '';
    }
}
