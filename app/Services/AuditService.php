<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Book;
use App\Models\Chapter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 内容审核服务
 *
 * 腾讯云文本/图片审核 API + 人工复核。
 *
 * 【模拟模式】开发环境 AUDIT_MOCK_ENABLED=true 直接返回通过。
 * 生产环境替换为真实腾讯云内容安全 API 调用。
 */
class AuditService
{
    /**
     * 自动审核文本内容
     *
     * @param string $content 待审核内容
     * @return array ['status' => pass|review|block, 'labels' => [], 'keywords' => []]
     */
    public function autoAudit(string $content): array
    {
        // 本地模拟模式：直接通过
        if (config('tencent.audit_mock')) {
            Log::info('【模拟内容审核】内容通过', ['length' => mb_strlen($content)]);
            return [
                'status' => 'pass',
                'suggestion' => '正常',
                'labels' => [],
                'keywords' => [],
            ];
        }

        // 生产环境：调用腾讯云 CMS 文本审核
        // $client = new \TencentCloud\Cms\V20180321\CmsClient(...);
        // $result = $client->TextModeration([...]);
        // 根据 Suggestion 返回映射

        // 默认走人工复核（安全兜底）
        return [
            'status' => 'review',
            'suggestion' => '待人工复核',
            'labels' => [],
            'keywords' => [],
        ];
    }

    /**
     * 人工审核章节
     *
     * @param int $chapterId
     * @param string $action pass|reject
     * @param string|null $remark
     */
    public function manualAuditChapter(int $chapterId, string $action, ?string $remark = null): Chapter
    {
        return DB::transaction(function () use ($chapterId, $action, $remark) {
            $chapter = Chapter::findOrFail($chapterId);

            if ($action === 'pass') {
                $chapter->audit_status = 1;
                $chapter->status = 1; // 已发布
            } else {
                $chapter->audit_status = 2;
            }

            $chapter->audit_remark = $remark;
            $chapter->audit_time = now();
            $chapter->auditor_id = auth()->id();
            $chapter->save();

            AuditLog::create([
                'chapter_id' => $chapterId,
                'action' => $action,
                'remark' => $remark,
                'auditor_id' => auth()->id(),
            ]);

            if ($action === 'pass') {
                $book = Book::where('id', $chapter->novel_id)->first();
                if ($book) {
                    $book->increment('total_chapters');
                    $book->increment('total_words', $chapter->word_count ?? 0);
                }
            }

            return $chapter;
        });
    }

    /**
     * 人工审核书籍
     */
    public function manualAuditBook(int $bookId, string $action, ?string $remark = null): Book
    {
        return DB::transaction(function () use ($bookId, $action, $remark) {
            $book = Book::findOrFail($bookId);

            if ($action === 'pass') {
                $book->audit_status = 1;
                $book->status = 2; // 已上架
                $book->published_at = now();
            } else {
                $book->audit_status = 2;
            }

            $book->audit_remark = $remark;
            $book->save();

            AuditLog::create([
                'novel_id' => $bookId,
                'action' => $action,
                'remark' => $remark,
                'auditor_id' => auth()->id(),
            ]);

            return $book;
        });
    }
}
