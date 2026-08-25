<?php

namespace App\Jobs;

use App\Models\Chapter;
use App\Services\AuditService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * 章节自动审核任务
 *
 * 提交章节后异步调用内容审核 API。
 */
class AutoAuditChapter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $chapterId)
    {
    }

    public function handle(AuditService $auditService): void
    {
        $chapter = Chapter::find($this->chapterId);
        if (!$chapter) {
            return;
        }

        // 拉取章节内容（OSS）
        $content = $chapter->content;

        // 调用自动审核（模拟模式直接通过）
        $result = $auditService->autoAudit($content);

        if ($result['status'] === 'block') {
            $chapter->audit_status = 2; // 驳回
            $chapter->audit_remark = '系统自动审核未通过';
        } elseif ($result['status'] === 'pass') {
            // 通过：进入待人工确认（或直接发布）
            $chapter->audit_status = 1;
        } else {
            $chapter->audit_status = 0; // 待人工复核
        }

        $chapter->save();
    }
}
