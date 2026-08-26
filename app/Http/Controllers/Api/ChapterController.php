<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\UserRead;
use Illuminate\Http\Request;

/**
 * 章节内容接口（需登录）
 *
 * 付费章节需校验是否已解锁。
 */
class ChapterController extends Controller
{
    /**
     * 获取章节内容
     *
     * GET /api/chapters/{id}
     */
    public function show(Request $request, int $id)
    {
        $chapter = Chapter::where('id', $id)
            ->where('status', 1)
            ->with('novel:id,title')
            ->firstOrFail();

        $user = $request->user();

        // 付费章节解锁校验
        if ((int) $chapter->is_free !== 1 && !$chapter->isUnlockedBy($user->id)) {
            return response()->success([
                'locked' => true,
                'chapter' => $chapter->only(['id', 'chapter_no', 'title', 'price', 'is_free']),
                'message' => '该章节需要解锁',
            ]);
        }

        // 记录阅读进度
        UserRead::updateOrCreate(
            ['user_id' => $user->id, 'novel_id' => $chapter->novel_id],
            [
                'chapter_id' => $chapter->id,
                'read_at' => now(),
                'device_type' => $request->header('X-Device-Type', 'web'),
            ]
        );

        // 阅读量 +1（节流：Redis 计数后批量落库，此处简化直接累加）
        \App\Models\Book::where('id', $chapter->novel_id)->increment('total_views');

        return response()->success([
            'id' => $chapter->id,
            'novel_id' => $chapter->novel_id,
            'novel_title' => $chapter->novel->title,
            'chapter_no' => $chapter->chapter_no,
            'title' => $chapter->title,
            'content' => $chapter->content, // 从 OSS 拉取
            'word_count' => $chapter->word_count,
            'is_free' => $chapter->is_free,
            'prev_id' => Chapter::where('novel_id', $chapter->novel_id)->where('chapter_no', $chapter->chapter_no - 1)->value('id'),
            'next_id' => Chapter::where('novel_id', $chapter->novel_id)->where('chapter_no', $chapter->chapter_no + 1)->value('id'),
        ]);
    }

    /**
     * 上报阅读进度（翻页时）
     *
     * POST /api/chapters/{id}/progress
     * body: { progress: 50, duration: 60 }
     */
    public function progress(Request $request, int $id)
    {
        $request->validate([
            'progress' => 'required|numeric|min:0|max:100',
            'duration' => 'nullable|integer|min:0',
        ]);

        $chapter = Chapter::findOrFail($id);

        UserRead::updateOrCreate(
            ['user_id' => $request->user()->id, 'novel_id' => $chapter->novel_id],
            [
                'chapter_id' => $chapter->id,
                'progress' => $request->input('progress'),
                'read_duration' => $request->input('duration', 0),
                'read_at' => now(),
            ]
        );

        return response()->success(null, '进度已保存');
    }
}
