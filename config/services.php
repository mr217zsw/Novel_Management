<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // 短信服务
    'sms' => [
        'provider' => env('SMS_PROVIDER', 'aliyun'),
        'sign_name' => env('SMS_SIGN_NAME'),
        'template_code' => env('SMS_TEMPLATE_CODE'),
    ],

    // 腾讯云内容审核
    'tencent' => [
        'secret_id' => env('TENCENT_SECRET_ID'),
        'secret_key' => env('TENCENT_SECRET_KEY'),
        'region' => env('TENCENT_REGION', 'ap-guangzhou'),
    ],

];
