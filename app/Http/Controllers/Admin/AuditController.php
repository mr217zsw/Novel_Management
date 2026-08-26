<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Book;
use App\Models\Chapter;
use Illuminate\Http\Request;

/**
 * 审核中心（编辑主管/管理员）
 *
 * 统一查看待审核的书籍与章节。
 */
class AuditController extends Controller
{
    /**
     * 待审核书籍
     *
     * GET /api/admin/audit/books
     */
    public function pendingBooks(Request $request)
    {
        return response()->success(
            Book::where('audit_status', 0)
                ->orWhere('status', 1)
                ->with('author:id,pen_name', 'category:id,name')
                ->orderByDesc('id')
                ->paginate($request->input('per_page', 20))
        );
    }

    /**
     * 待审核章节
     *
     * GET /api/admin/audit/chapters
     */
    public function pendingChapters(Request $request)
    {
        return response()->success(
            Chapter::where('audit_status', 0)
                ->with('novel:id,title')
                ->orderByDesc('id')
                ->select('id', 'novel_id', 'chapter_no', 'title', 'word_count', 'audit_status', 'created_at')
                ->paginate($request->input('per_page', 20))
        );
    }

    /**
     * 章节内容预览（审核用）
     *
     * GET /api/admin/audit/chapters/{id}/preview
     */
    public function chapterPreview(int $id)
    {
        $chapter = Chapter::findOrFail($id);

        return response()->success([
            'id' => $chapter->id,
            'novel_id' => $chapter->novel_id,
            'chapter_no' => $chapter->chapter_no,
            'title' => $chapter->title,
            'content' => $chapter->content, // 从 OSS 拉取
            'word_count' => $chapter->word_count,
        ]);
    }

    /**
     * 审核日志
     *
     * GET /api/admin/audit/logs
     */
    public function logs(Request $request)
    {
        return response()->success(
            AuditLog::with('auditor:id,nickname', 'novel:id,title', 'chapter:id,title')
                ->orderByDesc('id')
                ->paginate($request->input('per_page', 20))
        );
    }
}
