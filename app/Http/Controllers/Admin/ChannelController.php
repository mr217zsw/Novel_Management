<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use Illuminate\Http\Request;

/**
 * 投放渠道管理（投放经理）
 */
class ChannelController extends Controller
{
    public function index(Request $request)
    {
        return response()->success(
            Channel::with('owner:id,nickname')
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
                ->orderBy('id')
                ->paginate($request->input('per_page', 20))
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => 'required|string|max:30|unique:channels,code',
            'app_id' => 'nullable|string|max:64',
            'secret_key' => 'nullable|string',
            'callback_url' => 'nullable|url',
            'config' => 'nullable|array',
            'owner_id' => 'nullable|integer',
        ]);

        $channel = Channel::create($validated);

        return response()->success($channel, '渠道创建成功');
    }

    public function update(Request $request, int $id)
    {
        $channel = Channel::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:50',
            'code' => 'sometimes|string|max:30|unique:channels,code,' . $id,
            'app_id' => 'nullable|string|max:64',
            'secret_key' => 'nullable|string',
            'callback_url' => 'nullable|url',
            'config' => 'nullable|array',
            'status' => 'sometimes|in:0,1',
            'owner_id' => 'nullable|integer',
        ]);

        $channel->update($validated);

        return response()->success($channel, '渠道更新成功');
    }

    public function destroy(int $id)
    {
        $channel = Channel::findOrFail($id);
        if ($channel->campaigns()->exists()) {
            return response()->fail('该渠道下存在投放计划，无法删除', 400);
        }
        $channel->delete();

        return response()->success(null, '渠道已删除');
    }
}
