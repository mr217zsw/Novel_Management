<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\UserRead;
use Illuminate\Http\Request;

/**
 * 用户中心接口
 */
class UserController extends Controller
{
    /**
     * 用户资料（含余额/VIP状态）
     *
     * GET /api/user/profile
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        return response()->success([
            'id' => $user->id,
            'nickname' => $user->nickname,
            'avatar_url' => $user->avatar_url,
            'phone' => $user->phone,
            'balance' => $user->balance,
            'is_vip' => $user->isVip(),
            'vip_expire_at' => $user->vip_expire_at?->toDateString(),
            'register_channel' => $user->register_channel,
        ]);
    }

    /**
     * 更新资料
     *
     * PUT /api/user/profile
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'nickname' => 'nullable|string|max:50',
            'avatar_url' => 'nullable|url',
        ]);

        $user = $request->user();
        $user->update($request->only(['nickname', 'avatar_url']));

        return response()->success($user, '资料已更新');
    }

    /**
     * 我的书架
     *
     * GET /api/user/shelf
     */
    public function shelf(Request $request)
    {
        $reads = UserRead::where('user_id', $request->user()->id)
            ->with('novel:id,title,cover_url,status')
            ->orderByDesc('read_at')
            ->paginate(20);

        return response()->success($reads);
    }

    /**
     * 充值/VIP 商品列表
     *
     * GET /api/user/products?type=recharge|vip
     */
    public function products(Request $request)
    {
        $type = $request->input('type', 'recharge');

        $products = Product::where('product_type', $type)
            ->where('is_active', 1)
            ->orderBy('sort')
            ->get();

        return response()->success($products);
    }
}
