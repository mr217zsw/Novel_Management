<?php

namespace App\Services\OSS;

use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * OSS 存储服务
 *
 * 负责文件上传、读取、删除、CDN URL 组装等后端操作。
 * 前端直传已完成的大文件，后端主要通过 oss_key 管理元数据。
 */
class OssStorageService
{
    /**
     * 根据 oss_key 生成 CDN 加速地址
     */
    public function getCdnUrl(string $ossKey): string
    {
        if (empty($ossKey)) {
            return '';
        }

        $cdnDomain = config('aliyun.oss.cdn_domain');
        $bucket = config('aliyun.oss.bucket');
        $endpoint = config('aliyun.oss.endpoint');

        if ($cdnDomain) {
            return "https://{$cdnDomain}/{$ossKey}";
        }

        return "https://{$bucket}.{$endpoint}/{$ossKey}";
    }

    /**
     * 读取 OSS 对象内容（如章节正文）
     *
     * 【模拟模式】开发环境未配置 OSS 时，从本地 storage/oss 目录读取。
     */
    public function getContent(string $ossKey): string
    {
        if (empty($ossKey)) {
            return '';
        }

        // 模拟模式：读取本地文件
        if (config('mock.oss')) {
            $localPath = storage_path('oss/' . $ossKey);
            if (file_exists($localPath)) {
                return file_get_contents($localPath);
            }
            // 未找到则返回默认模拟内容
            return "（本地模拟章节内容）oss_key: {$ossKey}";
        }

        // 生产：通过 OSS SDK 读取
        // $oss = new OssClient(...);
        // return $oss->getObject($this->bucket, $ossKey);
        throw new RuntimeException('生产环境需配置 OSS SDK 读取内容');
    }

    /**
     * 删除 OSS 对象
     */
    public function delete(string $ossKey): bool
    {
        if (empty($ossKey) || config('mock.oss')) {
            Log::info('【OSS 模拟模式】删除对象', ['oss_key' => $ossKey]);
            return true;
        }

        // $oss->deleteObject($this->bucket, $ossKey);
        return true;
    }

    /**
     * 生成唯一的对象存储 key
     *
     * @param string $prefix 目录前缀 materials/covers/chapters/avatars
     * @param string $extension 文件扩展名（不带点）
     */
    public function makeObjectKey(string $prefix, string $extension = ''): string
    {
        $date = date('Y/m/d');
        $unique = uniqid('', true);
        $random = substr(md5(uniqid()), 0, 8);

        $key = "{$prefix}/{$date}/{$unique}-{$random}";
        if ($extension) {
            $key .= '.' . $extension;
        }

        return $key;
    }
}
