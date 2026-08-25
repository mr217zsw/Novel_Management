<?php

namespace App\Jobs;

use App\Models\Material;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * 视频素材后处理任务
 *
 * 上传完成后异步执行：视频截图 / 转码 / 内容审核。
 *
 * 【模拟模式】本地直接标记完成。
 */
class ProcessUploadedVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function __construct(public int $materialId)
    {
    }

    public function handle(): void
    {
        $material = Material::find($this->materialId);
        if (!$material) {
            return;
        }

        // 模拟模式：直接标记完成
        if (config('mock.queue')) {
            Log::info('【模拟视频处理】完成', ['material_id' => $this->materialId]);
            // 生产：调用 OSS 视频截图 + 内容审核
            return;
        }

        // 生产环境：
        // 1. 截图封面图（ffmpeg）
        // 2. 转码（如需要）
        // 3. 内容审核（腾讯云视频审核）
        // 4. 更新素材宽高/时长/截图
    }
}
