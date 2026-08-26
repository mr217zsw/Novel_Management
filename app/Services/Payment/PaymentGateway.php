<?php

namespace App\Services\Payment;

/**
 * 支付网关统一接口
 *
 * 所有支付平台（微信/抖音普通/抖音虚拟/苹果IAP）必须实现该接口，
 * 由 PaymentRouter 按平台路由分发。
 */
interface PaymentGateway
{
    /**
     * 创建支付订单
     *
     * @param array $params [
     *   'order_no' => string 业务订单号,
     *   'amount' => float 金额(分),
     *   'product_name' => string 商品名称,
     *   'user_id' => int,
     * ]
     * @return array 平台支付参数（如 prepay_id、签名等）
     */
    public function createOrder(array $params): array;

    /**
     * 验证支付回调
     *
     * @param array $data 回调原始数据
     * @return bool
     */
    public function verifyCallback(array $data): bool;

    /**
     * 查询订单支付状态
     */
    public function queryOrder(string $orderNo): array;

    /**
     * 退款
     */
    public function refund(string $orderNo, float $amount): array;

    /**
     * 从回调数据中提取业务订单号
     */
    public function extractOrderNo(array $data): string;
}
