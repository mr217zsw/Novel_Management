<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;

/**
 * 投放计划管理（投放专员）
 */
class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $query = Campaign::with('channel:id,name', 'book:id,title')
            ->withCount('materials');

        if ($request->filled('channel_id')) {
            $query->where('channel_id', $request->input('channel_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('keyword')) {
            $query->where('name', 'like', '%' . $request->input('keyword') . '%');
        }

        return response()->success($query->orderByDesc('id')->paginate($request->input('per_page', 20)));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'channel_id' => 'required|exists:channels,id',
            'name' => 'required|string|max:100',
            'book_id' => 'nullable|exists:books,id',
            'target_url' => 'nullable|url',
            'daily_budget' => 'required|numeric|min:0',
            'total_budget' => 'required|numeric|min:0',
            'bid_strategy' => 'required|in:1,2',
            'bid_price' => 'required|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $validated['status'] = Campaign::STATUS_DRAFT;
        $validated['created_by'] = $request->user()->id;

        $campaign = Campaign::create($validated);

        return response()->success($campaign, '投放计划创建成功');
    }

    public function update(Request $request, int $id)
    {
        $campaign = Campaign::findOrFail($id);

        $validated = $request->validate([
            'channel_id' => 'sometimes|exists:channels,id',
            'name' => 'sometimes|string|max:100',
            'book_id' => 'nullable|exists:books,id',
            'target_url' => 'nullable|url',
            'daily_budget' => 'sometimes|numeric|min:0',
            'total_budget' => 'sometimes|numeric|min:0',
            'bid_strategy' => 'sometimes|in:1,2',
            'bid_price' => 'sometimes|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $campaign->update($validated);

        return response()->success($campaign, '投放计划已更新');
    }

    public function destroy(int $id)
    {
        $campaign = Campaign::findOrFail($id);
        if ($campaign->materials()->where('status', '!=', 3)->exists()) {
            return response()->fail('该计划下存在未删除素材，请先处理', 400);
        }
        $campaign->delete();

        return response()->success(null, '投放计划已删除');
    }

    /**
     * 暂停/恢复投放
     *
     * POST /api/admin/campaigns/{id}/toggle
     */
    public function toggle(int $id)
    {
        $campaign = Campaign::findOrFail($id);

        $campaign->status = $campaign->status === Campaign::STATUS_ACTIVE
            ? Campaign::STATUS_PAUSED
            : Campaign::STATUS_ACTIVE;
        $campaign->save();

        return response()->success(['status' => $campaign->status], $campaign->status === Campaign::STATUS_ACTIVE ? '已开始投放' : '已暂停投放');
    }
}
