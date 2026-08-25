<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * 清理临时文件
 *
 * 清理本地临时目录、过期订单相关的临时文件。
 */
class CleanupTempFiles extends Command
{
    protected $signature = 'novel:cleanup-temp {--days=7 : 保留天数}';

    protected $description = '清理临时文件';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $expired = now()->subDays($days);

        $this->info("清理 {$days} 天前的临时文件");

        // 清理本地临时目录（章节导入临时文件等）
        $tmpDir = storage_path('app/private/tmp');
        if (is_dir($tmpDir)) {
            foreach (glob($tmpDir . '/*') as $file) {
                if (filemtime($file) < $expired->getTimestamp()) {
                    @unlink($file);
                }
            }
        }

        // 生产环境：删除 OSS 中超过有效期且未登记的临时分片
        // $oss->listMultipartUploads($bucket) -> abortMultipartUpload

        Log::info('临时文件清理完成', ['days' => $days]);

        return self::SUCCESS;
    }
}
