<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Payment\PaymentCallbackHandler;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * 支付控制器
 *
 * - POST /api/payment/orders   创建支付订单
 * - POST /api/payment/callback 支付回调（微信/抖音）
 * - POST /api/payment/verify   苹果收据校验
 * - POST /api/payment/query    查询订单状态
 */
class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private PaymentCallbackHandler $callbackHandler
    ) {
    }

    /**
     * 创建支付订单
     *
     * POST /api/payment/orders
     * body: { platform, product_type, product_id, openid? }
     */
    public function createOrder(Request $request)
    {
        $validated = $request->validate([
            'platform' => 'required|in:wechat,douyin,apple',
            'product_type' => 'required|in:recharge,chapter,vip',
            'product_id' => 'required|integer',
            'openid' => 'nullable|string',
        ]);

        $result = $this->paymentService->createOrder(
            $request->user(),
            $validated['platform'],
            $validated['product_type'],
            $validated['product_id'],
            $request->only(['openid'])
        );

        return response()->success($result);
    }

    /**
     * 支付回调（微信/抖音）
     *
     * POST /api/payment/callback/{platform}
     */
    public function callback(Request $request, string $platform)
    {
        if (!in_array($platform, ['wechat', 'douyin'], true)) {
            return response()->fail('不支持的支付平台', 400);
        }

        Log::info("收到支付回调", ['platform' => $platform, 'data' => $request->all()]);

        $ack = $this->callbackHandler->handle($request, $platform);

        // 微信/抖音回调期望返回 SUCCESS/FAIL 明文
        return response($ack, $ack === 'SUCCESS' ? 200 : 400)->header('Content-Type', 'text/plain');
    }

    /**
     * 苹果 IAP 收据校验
     *
     * POST /api/payment/verify
     * body: { receipt_data, order_no }
     */
    public function verifyApple(Request $request)
    {
        $validated = $request->validate([
            'receipt_data' => 'required|string',
            'order_no' => 'required|string',
        ]);

        $gateway = app(\App\Services\Payment\AppleIAPPayment::class);
        $result = $gateway->verifyReceipt($validated['receipt_data']);

        if (($result['status'] ?? -1) === 0) {
            // 校验通过，标记订单已支付
            $this->callbackHandler->handle(
                $request->merge(['order_no' => $validated['order_no'], 'transaction_id' => 'apple_' . uniqid()]),
                'apple'
            );
            return response()->success(['valid' => true, 'result' => $result]);
        }

        return response()->fail('收据校验失败', 400);
    }

    /**
     * 查询订单状态
     *
     * GET /api/payment/orders/{order_no}
     */
    public function query(string $orderNo)
    {
        $order = Order::where('order_no', $orderNo)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return response()->success($order);
    }
}
