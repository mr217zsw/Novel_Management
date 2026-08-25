<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 阿里云配置
    |--------------------------------------------------------------------------
    */

    // 主账号 AccessKey（用于 STS AssumeRole）
    'access_key_id' => env('ALIYUN_ACCESS_KEY_ID'),
    'access_key_secret' => env('ALIYUN_ACCESS_KEY_SECRET'),
    'region' => env('ALIYUN_REGION', 'cn-hangzhou'),

    // RAM 角色
    'ram' => [
        'account_id' => env('RAM_ACCOUNT_ID'),
        'role_name' => env('RAM_ROLE_NAME', 'oss-uploader-role'),
    ],

    // OSS
    'oss' => [
        'bucket' => env('OSS_BUCKET', 'your-bucket-name'),
        'endpoint' => env('OSS_ENDPOINT', 'oss-cn-hangzhou.aliyuncs.com'),
        'internal_endpoint' => env('OSS_INTERNAL_ENDPOINT'),
        'cdn_domain' => env('OSS_CDN_DOMAIN'),

        // STS
        'sts_region' => env('OSS_STS_REGION', 'cn-hangzhou'),
        'sts_duration' => (int) env('OSS_STS_DURATION', 1800),

        // 分片上传
        'part_size' => (int) env('OSS_PART_SIZE', 5242880), // 5MB
        'max_concurrency' => (int) env('OSS_MAX_CONCURRENCY', 3),

        // 允许上传的目录前缀
        'allowed_prefixes' => [
            'materials/',
            'chapters/',
            'covers/',
            'avatars/',
        ],
    ],

];
