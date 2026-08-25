<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttributionRecord;
use App\Services\AttributionService;
use Illuminate\Http\Request;

/**
 * 归因分析控制器（核心亮点）
 */
class AttributionController extends Controller
{
    public function __construct(private AttributionService $attributionService)
    {
    }

    /**
     * 渠道 ROI 汇总
     *
     * GET /api/admin/attributions/roi?start=&end=&campaign_id=&material_id=
     */
    public function roi(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
        ]);

        $dateRange = ['start' => $request->input('start'), 'end' => $request->input('end')];

        $data = $this->attributionService->channelROIList(
            $dateRange,
            $request->input('campaign_id'),
            $request->input('material_id')
        );

        return response()->success($data);
    }

    /**
     * 归因明细列表
     *
     * GET /api/admin/attributions?channel_id=&user_id=&date=
     */
    public function index(Request $request)
    {
        $query = AttributionRecord::with('channel:id,name', 'campaign:id,name', 'user:id,nickname');

        if ($request->filled('channel_id')) {
            $query->where('channel_id', $request->input('channel_id'));
        }
        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->input('campaign_id'));
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }
        if ($request->filled('date')) {
            $query->whereDate('click_time', $request->input('date'));
        }

        return response()->success($query->orderByDesc('click_time')->paginate($request->input('per_page', 20)));
    }

    /**
     * 单本书 ROI
     *
     * GET /api/admin/attributions/book/{id}?start=&end=
     */
    public function bookRoi(Request $request, int $bookId)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
        ]);

        return response()->success(
            $this->attributionService->calculateBookROI($bookId, [
                'start' => $request->input('start'),
                'end' => $request->input('end'),
            ])
        );
    }
}
