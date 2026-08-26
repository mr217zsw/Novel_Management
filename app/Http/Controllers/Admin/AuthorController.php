<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;

/**
 * 作者管理（编辑）
 */
class AuthorController extends Controller
{
    public function index(Request $request)
    {
        $query = Author::withCount('books');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('keyword')) {
            $query->where('pen_name', 'like', '%' . $request->input('keyword') . '%');
        }

        return response()->success($query->orderByDesc('id')->paginate($request->input('per_page', 20)));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'pen_name' => 'required|string|max:50|unique:authors,pen_name',
            'real_name' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'bank_name' => 'nullable|string|max:50',
            'royalty_rate' => 'required|numeric|min:0|max:100',
            'contract_start' => 'nullable|date',
            'contract_end' => 'nullable|date|after_or_equal:contract_start',
        ]);

        $validated['status'] = 1; // 签约
        $author = Author::create($validated);

        return response()->success($author, '作者签约成功');
    }

    public function update(Request $request, int $id)
    {
        $author = Author::findOrFail($id);

        $validated = $request->validate([
            'pen_name' => 'sometimes|string|max:50|unique:authors,pen_name,' . $id,
            'real_name' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'bank_name' => 'nullable|string|max:50',
            'royalty_rate' => 'sometimes|numeric|min:0|max:100',
            'status' => 'sometimes|in:1,2',
            'contract_start' => 'nullable|date',
            'contract_end' => 'nullable|date|after_or_equal:contract_start',
        ]);

        $author->update($validated);

        return response()->success($author, '作者信息已更新');
    }

    /**
     * 上传作者合同（合同文件走 OSS 直传后登记 URL）
     *
     * POST /api/admin/authors/{id}/contract
     */
    public function contract(Request $request, int $id)
    {
        $validated = $request->validate([
            'contract_url' => 'required|url',
            'contract_start' => 'nullable|date',
            'contract_end' => 'nullable|date',
        ]);

        $author = Author::findOrFail($id);
        $author->update([
            'contract_url' => $validated['contract_url'],
            'contract_start' => $validated['contract_start'] ?? $author->contract_start,
            'contract_end' => $validated['contract_end'] ?? $author->contract_end,
        ]);

        return response()->success($author, '合同已上传');
    }

    /**
     * 作者分成结算（财务）
     *
     * GET /api/admin/authors/{id}/settlements
     */
    public function settlements(Request $request, int $id)
    {
        return response()->success(
            \App\Models\AuthorSettlement::where('author_id', $id)
                ->orderByDesc('settle_date')
                ->paginate($request->input('per_page', 20))
        );
    }
}
