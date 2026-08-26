<?php

namespace App\Services\Payment;

use RuntimeException;

/**
 * 支付路由
 *
 * 根据平台 + 商品类型选择对应支付方式：
 * - wechat：微信支付
 * - douyin + 虚拟商品(chapter/vip)：抖音虚拟支付（不抽成）
 * - douyin + 普通商品(recharge)：抖音普通支付
 * - apple：苹果 IAP
 */
class PaymentRouter
{
    public const PLATFORM_WECHAT = 'wechat';
    public const PLATFORM_DOUYIN = 'douyin';
    public const PLATFORM_IOS = 'apple';

    /**
     * 虚拟商品类型（抖音走虚拟支付）
     */
    private const VIRTUAL_PRODUCT_TYPES = ['chapter', 'vip'];

    public function route(string $platform, string $productType = 'recharge'): PaymentGateway
    {
        return match ($platform) {
            self::PLATFORM_WECHAT => app(WechatPayment::class),
            self::PLATFORM_DOUYIN => in_array($productType, self::VIRTUAL_PRODUCT_TYPES, true)
                ? app(DouyinVirtualPayment::class)
                : app(DouyinPayment::class),
            self::PLATFORM_IOS => app(AppleIAPPayment::class),
            default => throw new RuntimeException("不支持的支付平台: {$platform}"),
        };
    }

    public function routeByName(string $gateway): PaymentGateway
    {
        return match ($gateway) {
            'wechat' => app(WechatPayment::class),
            'douyin' => app(DouyinPayment::class),
            'douyin_virtual' => app(DouyinVirtualPayment::class),
            'apple' => app(AppleIAPPayment::class),
            default => throw new RuntimeException("不支持的支付网关: {$gateway}"),
        };
    }
}
