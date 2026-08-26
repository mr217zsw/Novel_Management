<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 支付配置
    |--------------------------------------------------------------------------
    */

    // 模拟支付（本地开发）
    'mock' => (bool) env('PAYMENT_MOCK_ENABLED', true),

    // 订单过期时间（分钟）
    'order_expire' => (int) env('PAYMENT_ORDER_EXPIRE', 15),

    // 微信支付
    'wechat' => [
        'app_id' => env('WECHAT_APP_ID'),
        'mch_id' => env('WECHAT_MCH_ID'),
        'api_v3_key' => env('WECHAT_API_V3_KEY'),
        'private_key_path' => env('WECHAT_PRIVATE_KEY_PATH'),
        'cert_serial_no' => env('WECHAT_CERT_SERIAL_NO'),
        'notify_url' => env('WECHAT_NOTIFY_URL'),
    ],

    // 抖音支付
    'douyin' => [
        'app_id' => env('DOUYIN_APP_ID'),
        'mch_id' => env('DOUYIN_MCH_ID'),
        'salt' => env('DOUYIN_SALT'),
        'notify_url' => env('DOUYIN_NOTIFY_URL'),
        'virtual_notify_url' => env('DOUYIN_VIRTUAL_NOTIFY_URL'),
    ],

    // 苹果 IAP
    'apple' => [
        'bundle_id' => env('APPLE_BUNDLE_ID'),
        'shared_secret' => env('APPLE_SHARED_SECRET'),
        'environment' => env('APPLE_ENV', 'sandbox'), // sandbox | production
        'sandbox_url' => 'https://sandbox.itunes.apple.com/verifyReceipt',
        'production_url' => 'https://buy.itunes.apple.com/verifyReceipt',
    ],

];
