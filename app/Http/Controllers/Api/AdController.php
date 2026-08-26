<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdReward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

/**
 * IAA 广告变现接口
 *
 * 用户观看激励视频后可获得阅读币奖励。
 * 通过 request_id 幂等，防止刷奖励。
 */
class AdController extends Controller
{
    /**
     * 激励视频观看完成，发放奖励
     *
     * POST /api/ad/reward
     * body: { ad_type, ad_platform, request_id, ecpm? }
     */
    public function reward(Request $request)
    {
        $validated = $request->validate([
            'ad_type' => 'required|in:reward,interstitial,splash',
            'ad_platform' => 'nullable|string|max:20',
            'request_id' => 'required|string|max:64',
            'ecpm' => 'nullable|numeric|min:0',
            'ad_id' => 'nullable|string|max:64',
        ]);

        $user = $request->user();

        // 幂等：同一广告请求只能发一次奖励
        $lockKey = "ad:reward:{$validated['request_id']}";
        if (!Redis::set($lockKey, '1', 'EX', 3600, 'NX')) {
            return response()->fail('该广告奖励已发放', 400);
        }

        // 奖励阅读币：激励视频默认 100 币
        $rewardCoins = $validated['ad_type'] === 'reward' ? 100 : 20;

        AdReward::create([
            'user_id' => $user->id,
            'ad_type' => $validated['ad_type'],
            'ad_platform' => $validated['ad_platform'],
            'reward_coins' => $rewardCoins,
            'ecpm' => $validated['ecpm'] ?? 0,
            'ad_id' => $validated['ad_id'] ?? null,
            'request_id' => $validated['request_id'],
        ]);

        // 发放阅读币
        $user->increment('balance', $rewardCoins);

        return response()->success([
            'reward_coins' => $rewardCoins,
            'balance' => $user->fresh()->balance,
        ], '奖励已发放');
    }
}
