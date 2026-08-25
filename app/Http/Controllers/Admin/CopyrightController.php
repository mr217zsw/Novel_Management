<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

/**
 * 版权采购管理（版权经理）
 *
 * 版权信息挂在 books 表上（copyright_* 字段），此处提供采购视角的管理接口。
 */
class CopyrightController extends Controller
{
    /**
     * 版权列表
     *
     * GET /api/admin/copyrights
     */
    public function index(Request $request)
    {
        $query = Book::whereNotNull('copyright_price')
            ->orWhere('copyright_type', '!=', 1);

        if ($request->filled('copyright_type')) {
            $query->where('copyright_type', $request->input('copyright_type'));
        }
        if ($request->filled('keyword')) {
            $query->where('title', 'like', '%' . $request->input('keyword') . '%');
        }

        return response()->success(
            $query->with('author:id,pen_name')
                ->select('id', 'title', 'copyright_type', 'copyright_price', 'contract_start', 'contract_end', 'author_id', 'created_at')
                ->orderByDesc('id')
                ->paginate($request->input('per_page', 20))
        );
    }

    /**
     * 登记版权采购
     *
     * POST /api/admin/copyrights
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'copyright_type' => 'required|in:1,2,3,4',
            'copyright_price' => 'required|numeric|min:0',
            'contract_start' => 'required|date',
            'contract_end' => 'required|date|after_or_equal:contract_start',
        ]);

        $book = Book::findOrFail($validated['book_id']);
        $book->update([
            'copyright_type' => $validated['copyright_type'],
            'copyright_price' => $validated['copyright_price'],
            'contract_start' => $validated['contract_start'],
            'contract_end' => $validated['contract_end'],
        ]);

        return response()->success($book, '版权登记成功');
    }

    /**
     * 付款审批
     *
     * POST /api/admin/copyrights/{id}/pay
     */
    public function pay(int $id)
    {
        $book = Book::findOrFail($id);
        // 记录付款（生产接入财务流程/资金流水表）
        // 此处简化为返回确认
        return response()->success(['book_id' => $book->id, 'amount' => $book->copyright_price], '版权付款已提交审批');
    }
}
