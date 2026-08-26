<?php

/**
 * 模拟模式开关（本地开发）
 *
 * 文档标注：审核/支付/短信/队列/OSS 本地开发用模拟实现，
 * 生产环境关闭对应开关后走真实服务。
 */
return [

    // 内容审核模拟（本地直接返回"通过"）
    'audit' => (bool) env('AUDIT_MOCK_ENABLED', true),

    // 支付模拟（本地直接返回成功）
    'payment' => (bool) env('PAYMENT_MOCK_ENABLED', true),

    // 短信模拟（本地不实际发送，仅记录日志）
    'sms' => (bool) env('SMS_MOCK_ENABLED', true),

    // 队列模拟（本地同步执行，不投递队列）
    'queue' => (bool) env('QUEUE_MOCK_ENABLED', true),

    // OSS 模拟（本地使用 storage/oss 目录代替 OSS）
    'oss' => (bool) env('OSS_MOCK_ENABLED', true),

];
