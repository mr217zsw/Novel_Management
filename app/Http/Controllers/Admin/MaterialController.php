<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\Request;

/**
 * 投放素材管理（投放专员 + 投放经理审核）
 */
class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $query = Material::with('campaign:id,name', 'creator:id,nickname');

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->input('campaign_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        return response()->success($query->orderByDesc('id')->paginate($request->input('per_page', 20)));
    }

    /**
     * 素材上传（前端先走 OSS 直传，完成后调用 /api/oss/complete 登记）
     * 此接口用于手动登记（可选）
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'campaign_id' => 'nullable|exists:campaigns,id',
            'name' => 'required|string|max:100',
            'type' => 'required|in:1,2',
            'oss_key' => 'required|string',
            'file_size' => 'required|integer|min:0',
            'mime_type' => 'required|string',
            'width' => 'nullable|integer',
            'height' => 'nullable|integer',
            'duration' => 'nullable|integer',
        ]);

        $validated['cdn_url'] = app(\App\Services\OSS\OssStorageService::class)->getCdnUrl($validated['oss_key']);
        $validated['status'] = 0; // 待审核
        $validated['created_by'] = $request->user()->id;

        $material = Material::create($validated);

        return response()->success($material, '素材已上传');
    }

    /**
     * 素材审核（投放经理）
     *
     * POST /api/admin/materials/{id}/audit
     * body: { action: pass|reject, remark? }
     */
    public function audit(Request $request, int $id)
    {
        $validated = $request->validate([
            'action' => 'required|in:pass,reject',
            'remark' => 'nullable|string|max:200',
        ]);

        $material = Material::findOrFail($id);

        $material->status = $validated['action'] === 'pass' ? 1 : 2;
        $material->audit_remark = $validated['remark'];
        $material->save();

        return response()->success($material, $validated['action'] === 'pass' ? '素材已通过' : '素材已驳回');
    }

    /**
     * 删除素材（软删）
     */
    public function destroy(int $id)
    {
        $material = Material::findOrFail($id);
        $material->status = 3; // 已删除
        $material->save();

        return response()->success(null, '素材已删除');
    }
}
