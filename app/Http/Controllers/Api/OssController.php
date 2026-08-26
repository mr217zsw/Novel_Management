<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Services\OSS\OssStorageService;
use App\Services\OSS\StsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * OSS 直传控制器
 *
 * - GET  /api/oss/sts-token   前端上传前获取临时凭证
 * - POST /api/oss/complete    上传完成后回调登记元数据
 */
class OssController extends Controller
{
    public function __construct(
        private StsService $stsService,
        private OssStorageService $ossStorageService
    ) {
    }

    /**
     * 获取 STS 临时上传凭证
     *
     * GET /api/oss/sts-token?prefix=materials|chapters|covers|avatars
     */
    public function stsToken(Request $request)
    {
        $prefix = $request->input('prefix', 'materials');

        $allowedPrefixes = config('aliyun.oss.allowed_prefixes', ['materials/']);

        // 规范化前缀
        $prefix = rtrim($prefix, '/') . '/';
        if (!in_array($prefix, $allowedPrefixes, true)) {
            return response()->fail('不允许的目录前缀', 400);
        }

        $token = $this->stsService->generateToken($request->user()->id, $prefix);

        return response()->success($token);
    }

    /**
     * 上传完成回调
     *
     * POST /api/oss/complete
     * body: { oss_key, file_name, file_size, mime_type, campaign_id?, material_type? }
     */
    public function complete(Request $request)
    {
        $validated = $request->validate([
            'oss_key' => 'required|string',
            'file_name' => 'required|string|max:100',
            'file_size' => 'required|integer|min:0',
            'mime_type' => 'required|string',
            'campaign_id' => 'nullable|exists:campaigns,id',
        ]);

        $isImage = str_contains($validated['mime_type'], 'image');
        $isVideo = str_contains($validated['mime_type'], 'video');

        // 若属于素材目录，创建素材记录
        if (str_contains($validated['oss_key'], 'materials/')) {
            $material = Material::create([
                'campaign_id' => $validated['campaign_id'] ?? null,
                'name' => $validated['file_name'],
                'type' => $isImage ? 1 : ($isVideo ? 2 : 1),
                'oss_key' => $validated['oss_key'],
                'cdn_url' => $this->ossStorageService->getCdnUrl($validated['oss_key']),
                'file_size' => $validated['file_size'],
                'mime_type' => $validated['mime_type'],
                'status' => 0, // 待审核
                'created_by' => $request->user()->id,
            ]);

            // 视频素材：投递后处理任务（截图/审核）
            if ($isVideo) {
                \App\Jobs\ProcessUploadedVideo::dispatch($material->id);
            }

            return response()->success(['material_id' => $material->id], '上传登记成功');
        }

        // 其他目录（章节/封面/头像）：仅登记 oss_key，由对应业务创建
        return response()->success(['oss_key' => $validated['oss_key']], '上传完成');
    }

    /**
     * 登记章节内容（作者上传章节正文到 OSS 后）
     *
     * POST /api/oss/chapter
     * body: { novel_id, chapter_no, title, oss_key, word_count, is_free, price? }
     */
    public function chapter(Request $request)
    {
        $validated = $request->validate([
            'novel_id' => 'required|exists:books,id',
            'chapter_no' => 'required|integer|min:1',
            'title' => 'required|string|max:100',
            'oss_key' => 'required|string',
            'word_count' => 'required|integer|min:0',
            'is_free' => 'required|in:0,1',
            'price' => 'nullable|numeric|min:0',
        ]);

        $chapter = \App\Models\Chapter::create([
            'novel_id' => $validated['novel_id'],
            'chapter_no' => $validated['chapter_no'],
            'title' => $validated['title'],
            'content_oss_key' => $validated['oss_key'],
            'content_cdn_url' => $this->ossStorageService->getCdnUrl($validated['oss_key']),
            'word_count' => $validated['word_count'],
            'is_free' => $validated['is_free'],
            'price' => $validated['price'] ?? 0,
            'status' => 0, // 草稿
            'audit_status' => 0, // 待审
        ]);

        // 投递自动审核任务
        \App\Jobs\AutoAuditChapter::dispatch($chapter->id);

        return response()->success($chapter, '章节已提交，等待审核');
    }
}
