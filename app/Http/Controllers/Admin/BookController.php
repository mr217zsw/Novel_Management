<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Services\AuditService;
use Illuminate\Http\Request;

/**
 * 后台书籍管理（编辑）
 */
class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with('author:id,pen_name', 'category:id,name');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('audit_status')) {
            $query->where('audit_status', $request->input('audit_status'));
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }
        if ($request->filled('keyword')) {
            $query->where('title', 'like', '%' . $request->input('keyword') . '%');
        }

        return response()->success($query->orderByDesc('id')->paginate($request->input('per_page', 20)));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'author_id' => 'nullable|exists:authors,id',
            'cover_url' => 'nullable|url',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
            'copyright_type' => 'required|in:1,2,3,4',
            'copyright_price' => 'nullable|numeric|min:0',
            'contract_start' => 'nullable|date',
            'contract_end' => 'nullable|date',
            'royalty_rate' => 'nullable|numeric|min:0|max:100',
            'min_price' => 'nullable|numeric|min:0',
        ]);

        $validated['tags'] = $request->input('tags', []);
        $validated['status'] = Book::STATUS_PENDING_AUDIT;
        $validated['audit_status'] = Book::AUDIT_PENDING;

        $book = Book::create($validated);

        return response()->success($book, '书籍创建成功');
    }

    public function update(Request $request, int $id)
    {
        $book = Book::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:100',
            'cover_url' => 'nullable|url',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'nullable|array',
            'copyright_type' => 'sometimes|in:1,2,3,4',
            'copyright_price' => 'nullable|numeric|min:0',
            'contract_start' => 'nullable|date',
            'contract_end' => 'nullable|date',
            'royalty_rate' => 'nullable|numeric|min:0|max:100',
            'min_price' => 'nullable|numeric|min:0',
        ]);

        $book->update($validated);

        return response()->success($book, '书籍已更新');
    }

    public function show(int $id)
    {
        return response()->success(Book::with('author:id,pen_name', 'category:id,name')->findOrFail($id));
    }

    public function destroy(int $id)
    {
        $book = Book::findOrFail($id);
        if ($book->chapters()->exists()) {
            return response()->fail('该书籍存在章节，无法删除', 400);
        }
        $book->delete();

        return response()->success(null, '书籍已删除');
    }

    /**
     * 书籍审核（编辑主管）
     *
     * POST /api/admin/books/{id}/audit
     */
    public function audit(Request $request, int $id)
    {
        $validated = $request->validate([
            'action' => 'required|in:pass,reject',
            'remark' => 'nullable|string|max:200',
        ]);

        $book = app(AuditService::class)->manualAuditBook($id, $validated['action'], $validated['remark']);

        return response()->success($book, $validated['action'] === 'pass' ? '书籍已上架' : '书籍已驳回');
    }

    /**
     * 上下架
     *
     * POST /api/admin/books/{id}/toggle
     */
    public function toggle(int $id)
    {
        $book = Book::findOrFail($id);
        $book->status = $book->status === 2 ? 3 : 2; // 上架<->下架
        $book->save();

        return response()->success(['status' => $book->status], $book->status === 2 ? '已上架' : '已下架');
    }
}
