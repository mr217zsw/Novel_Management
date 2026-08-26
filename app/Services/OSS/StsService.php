<?php

namespace App\Services\OSS;

use AlibabaCloud\Client\AlibabaCloud;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * STS 临时凭证服务
 *
 * 前端直传 OSS 前，先请求此服务获取临时密钥（有效期 30 分钟）。
 * 使用 RAM 子账号 AssumeRole 获取临时凭证，最小权限授权到指定目录。
 *
 * 参考文档：https://help.aliyun.com/zh/ram/sts
 */
class StsService
{
    /**
     * 生成 STS 临时上传凭证
     *
     * @param int $userId 请求用户ID（用于 RoleSessionName 审计）
     * @param string $prefix 允许上传的目录前缀 materials/、covers/、avatars/
     * @return array
     */
    public function generateToken(int $userId, string $prefix = 'materials/'): array
    {
        $accountId = config('aliyun.ram.account_id');
        $roleName = config('aliyun.ram.role_name');

        if (empty($accountId) || empty($roleName)) {
            throw new RuntimeException('STS 未配置 RAM 账号或角色，请检查 config/aliyun.php');
        }

        $roleArn = "acs:ram::{$accountId}:role/{$roleName}";
        $bucket = config('aliyun.oss.bucket');
        $duration = config('aliyun.oss.sts_duration', 1800);

        // 统一根前缀（多项目共用一个 bucket 时用目录区分，如 novel-platform/）
        $rootPrefix = $this->rootPrefix();

        // 限定仅能上传到指定目录（含统一根前缀）
        $policy = [
            'Version' => '1',
            'Statement' => [
                [
                    'Effect' => 'Allow',
                    'Action' => [
                        'oss:PutObject',
                        'oss:InitiateMultipartUpload',
                        'oss:UploadPart',
                        'oss:CompleteMultipartUpload',
                        'oss:AbortMultipartUpload',
                        'oss:ListParts',
                    ],
                    'Resource' => [
                        "acs:oss:*:*:{$bucket}/{$rootPrefix}{$prefix}*",
                    ],
                ],
            ],
        ];

        // 【模拟模式】未配置密钥时，本地开发直接返回占位凭证，由前端本地 mock 上传
        if (config('mock.oss')) {
            Log::info('【OSS 模拟模式】返回占位 STS 凭证', ['user_id' => $userId, 'prefix' => $prefix]);
            return $this->buildMockToken($userId, $prefix);
        }

        AlibabaCloud::accessKeyClient(
            config('aliyun.access_key_id'),
            config('aliyun.access_key_secret')
        )->regionId(config('aliyun.oss.sts_region', 'cn-hangzhou'))->asDefaultClient();

        $result = AlibabaCloud::rpc()
            ->product('Sts')
            ->version('2015-04-01')
            ->action('AssumeRole')
            ->method('POST')->scheme('https')
            ->host('sts.aliyuncs.com')
            ->options([
                'query' => [
                    'RoleArn' => $roleArn,
                    'RoleSessionName' => "upload-session-{$userId}-" . time(),
                    'DurationSeconds' => $duration,
                    'Policy' => json_encode($policy, JSON_UNESCAPED_UNICODE),
                ],
            ])
            ->request();

        $credentials = $result['Credentials'];

        return $this->buildToken($credentials, $prefix);
    }

    /**
     * 统一根前缀，如 novel-platform/
     */
    protected function rootPrefix(): string
    {
        $prefix = rtrim((string) config('aliyun.oss.prefix'), '/');
        return $prefix === '' ? '' : $prefix . '/';
    }

    /**
     * 组装最终返回给前端的凭证结构
     */
    protected function buildToken(array $credentials, string $prefix): array
    {
        return [
            'AccessKeyId' => $credentials['AccessKeyId'],
            'AccessKeySecret' => $credentials['AccessKeySecret'],
            'SecurityToken' => $credentials['SecurityToken'],
            'Expiration' => $credentials['Expiration'],
            'Region' => config('aliyun.region', 'oss-cn-hangzhou'),
            'Endpoint' => 'https://' . config('aliyun.oss.endpoint'),
            'Bucket' => config('aliyun.oss.bucket'),
            'Prefix' => $this->rootPrefix() . $prefix . date('Y/m/d') . '/',
            'PartSize' => config('aliyun.oss.part_size'),
            'Concurrency' => config('aliyun.oss.max_concurrency'),
        ];
    }

    /**
     * 本地模拟凭证
     */
    protected function buildMockToken(int $userId, string $prefix): array
    {
        return [
            'AccessKeyId' => config('aliyun.access_key_id') ?: 'mock-access-key',
            'AccessKeySecret' => 'mock-secret',
            'SecurityToken' => 'mock-token',
            'Expiration' => now()->addSeconds(config('aliyun.oss.sts_duration', 1800))->toIso8601String(),
            'Region' => 'oss-cn-hangzhou',
            'Endpoint' => 'https://' . config('aliyun.oss.endpoint'),
            'Bucket' => config('aliyun.oss.bucket', 'your-bucket-name'),
            'Prefix' => $this->rootPrefix() . $prefix . date('Y/m/d') . '/',
            'PartSize' => config('aliyun.oss.part_size'),
            'Concurrency' => config('aliyun.oss.max_concurrency'),
            'Mock' => true,
        ];
    }
}
