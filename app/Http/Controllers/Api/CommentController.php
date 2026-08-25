<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

/**
 * 评论接口
 */
class CommentController extends Controller
{
    /**
     * 获取书籍/章节评论
     *
     * GET /api/comments?novel_id=&chapter_id=
     */
    public function index(Request $request)
    {
        $request->validate([
            'novel_id' => 'required|integer',
            'chapter_id' => 'nullable|integer',
        ]);

        $query = Comment::where('novel_id', $request->input('novel_id'))
            ->where('status', 1)
            ->where('parent_id', 0)
            ->with('user:id,nickname,avatar_url')
            ->withCount('replies')
            ->latest();

        if ($request->filled('chapter_id')) {
            $query->where('chapter_id', $request->input('chapter_id'));
        }

        return response()->success($query->paginate(20));
    }

    /**
     * 发表评论
     *
     * POST /api/comments
     */
    public function store(Request $request)
    {
        $request->validate([
            'novel_id' => 'required|integer',
            'chapter_id' => 'nullable|integer',
            'parent_id' => 'nullable|integer',
            'content' => 'required|string|max:500',
        ]);

        // 【内容审核】开发环境模拟通过，生产走腾讯云
        $audit = app(\App\Services\AuditService::class)->autoAudit($request->input('content'));
        if ($audit['status'] === 'block') {
            return response()->fail('评论内容包含违规信息', 400);
        }

        $comment = Comment::create([
            'user_id' => $request->user()->id,
            'novel_id' => $request->input('novel_id'),
            'chapter_id' => $request->input('chapter_id'),
            'parent_id' => $request->input('parent_id', 0),
            'content' => $request->input('content'),
            'status' => $audit['status'] === 'pass' ? 1 : 0, // review 置为待审核
        ]);

        return response()->success($comment, '评论成功');
    }

    /**
     * 点赞评论
     */
    public function like(int $id)
    {
        Comment::where('id', $id)->increment('like_count');
        return response()->success(null, '点赞成功');
    }
}
