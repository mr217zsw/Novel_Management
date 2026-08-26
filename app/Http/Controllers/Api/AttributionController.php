<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AttributionService;
use Illuminate\Http\Request;

/**
 * 归因点击落地接口（广告平台回调）
 *
 * 用户点击广告时，广告平台回调此接口记录点击（click_id）。
 */
class AttributionController extends Controller
{
    public function __construct(private AttributionService $attributionService)
    {
    }

    /**
     * 记录广告点击
     *
     * POST /api/v1/attribution/click
     * body: { channel_id, click_id, device_id, campaign_id?, material_id?, ip?, user_agent?, referer? }
     */
    public function recordClick(Request $request)
    {
        $validated = $request->validate([
            'channel_id' => 'required|exists:channels,id',
            'click_id' => 'required|string|max:64',
            'device_id' => 'required|string|max:64',
            'campaign_id' => 'nullable|exists:campaigns,id',
            'material_id' => 'nullable|exists:materials,id',
            'referer' => 'nullable|string|max:255',
        ]);

        $record = $this->attributionService->recordClick(
            $validated['channel_id'],
            $validated['click_id'],
            $validated['device_id'],
            $request->ip(),
            $request->header('User-Agent'),
            $validated['campaign_id'] ?? null,
            $validated['material_id'] ?? null,
            $validated['referer'] ?? null
        );

        return response()->success(['attribution_id' => $record->id], '点击已记录');
    }
}
