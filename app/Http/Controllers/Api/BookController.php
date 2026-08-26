<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

/**
 * 小程序端书籍接口（公开）
 */
class BookController extends Controller
{
    /**
     * 小说列表
     *
     * GET /api/books?category_id=&keyword=&sort=views|newest&page=&per_page=
     */
    public function index(Request $request)
    {
        $query = Book::where('status', 2); // 已上架

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('keyword')) {
            $query->where('title', 'like', '%' . $request->input('keyword') . '%');
        }

        // 排序
        switch ($request->input('sort', 'views')) {
            case 'newest':
                $query->orderByDesc('published_at');
                break;
            case 'likes':
                $query->orderByDesc('total_likes');
                break;
            default:
                $query->orderByDesc('total_views');
        }

        $books = $query->select('id', 'title', 'cover_url', 'description', 'category_id', 'tags', 'total_chapters', 'total_words', 'total_views', 'min_price')
            ->with('category:id,name')
            ->paginate($request->input('per_page', 20));

        return response()->success($books);
    }

    /**
     * 小说详情
     *
     * GET /api/books/{id}
     */
    public function show(int $id)
    {
        $book = Book::with('category:id,name')
            ->withCount(['chapters as published_chapters' => fn ($q) => $q->where('status', 1)])
            ->findOrFail($id);

        if ((int) $book->status !== 2) {
            return response()->fail('该书籍未上架', 404);
        }

        return response()->success($book);
    }

    /**
     * 书籍章节列表
     *
     * GET /api/books/{id}/chapters
     */
    public function chapters(int $id)
    {
        $chapters = \App\Models\Chapter::where('novel_id', $id)
            ->where('status', 1)
            ->orderBy('chapter_no')
            ->select('id', 'chapter_no', 'title', 'is_free', 'price', 'word_count', 'created_at')
            ->paginate(50);

        return response()->success($chapters);
    }
}
