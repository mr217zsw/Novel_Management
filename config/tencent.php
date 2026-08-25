<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 腾讯云配置
    |--------------------------------------------------------------------------
    */

    // 内容审核模拟（本地直接通过）
    'audit_mock' => (bool) env('AUDIT_MOCK_ENABLED', true),

    // 内容审核 API
    'cms' => [
        'secret_id' => env('TENCENT_SECRET_ID'),
        'secret_key' => env('TENCENT_SECRET_KEY'),
        'region' => env('TENCENT_REGION', 'ap-guangzhou'),
    ],

];
