<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Chapter;
use App\Services\AuditService;
use App\Services\OSS\OssStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 后台章节管理（作者上传 + 编辑审核）
 */
class ChapterController extends Controller
{
    /**
     * 章节列表
     *
     * GET /api/admin/books/{bookId}/chapters
     */
    public function index(Request $request, int $bookId)
    {
        $query = Chapter::where('novel_id', $bookId);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('audit_status')) {
            $query->where('audit_status', $request->input('audit_status'));
        }

        return response()->success(
            $query->orderBy('chapter_no')
                ->select('id', 'novel_id', 'chapter_no', 'title', 'is_free', 'price', 'word_count', 'status', 'audit_status', 'created_at')
                ->paginate($request->input('per_page', 20))
        );
    }

    /**
     * 创建章节
     *
     * POST /api/admin/books/{bookId}/chapters
     * body: { title, chapter_no, content, is_free, price? }
     * content 正文：本地开发直接传文本（存本地/模拟OSS），生产前端直传 OSS 后传 oss_key。
     */
    public function store(Request $request, int $bookId)
    {
        Book::findOrFail($bookId);

        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'chapter_no' => 'required|integer|min:1|unique:chapters,chapter_no,NULL,id,novel_id,' . $bookId,
            'content' => 'required_without:oss_key|string',
            'oss_key' => 'required_without:content|string',
            'is_free' => 'required|in:0,1',
            'price' => 'nullable|numeric|min:0',
        ]);

        $ossKey = $validated['oss_key'] ?? null;
        $content = $validated['content'] ?? null;

        // 未走 OSS 直传时，模拟存本地
        if ($content !== null) {
            $ossStorage = app(OssStorageService::class);
            $ossKey = $ossStorage->makeObjectKey('chapters', 'txt');
            $this->storeContent($ossKey, $content);
        }

        $chapter = DB::transaction(function () use ($bookId, $validated, $ossKey) {
            $chapter = Chapter::create([
                'novel_id' => $bookId,
                'chapter_no' => $validated['chapter_no'],
                'title' => $validated['title'],
                'content_oss_key' => $ossKey,
                'content_cdn_url' => app(OssStorageService::class)->getCdnUrl($ossKey),
                'word_count' => mb_strlen($validated['content'] ?? ''),
                'is_free' => $validated['is_free'],
                'price' => $validated['price'] ?? 0,
                'status' => 0, // 草稿
                'audit_status' => 0, // 待审
            ]);

            return $chapter;
        });

        // 投递自动审核
        \App\Jobs\AutoAuditChapter::dispatch($chapter->id);

        return response()->success($chapter, '章节已提交，待审核');
    }

    /**
     * 章节审核
     *
     * POST /api/admin/chapters/{id}/audit
     */
    public function audit(Request $request, int $id)
    {
        $validated = $request->validate([
            'action' => 'required|in:pass,reject',
            'remark' => 'nullable|string|max:200',
        ]);

        $chapter = app(AuditService::class)->manualAuditChapter($id, $validated['action'], $validated['remark']);

        return response()->success($chapter, $validated['action'] === 'pass' ? '章节已发布' : '章节已驳回');
    }

    /**
     * 存储章节正文（模拟：本地文件；生产：OSS）
     */
    protected function storeContent(string $ossKey, string $content): void
    {
        if (config('mock.oss')) {
            $path = storage_path('oss/' . $ossKey);
            $dir = dirname($path);
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            file_put_contents($path, $content);
            return;
        }

        // 生产：上传 OSS
        // $oss->putObject($bucket, $ossKey, $content);
    }
}
