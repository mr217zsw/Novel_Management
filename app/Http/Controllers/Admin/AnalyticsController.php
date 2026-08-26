<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 数据报表控制器（运营/财务）
 */
class AnalyticsController extends Controller
{
    /**
     * 收入明细
     *
     * GET /api/admin/analytics/revenue?start=&end=&platform=&product_type=
     */
    public function revenue(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
        ]);

        $query = Order::where('status', Order::STATUS_PAID)
            ->whereBetween('pay_time', [$request->input('start'), $request->input('end')]);

        if ($request->filled('platform')) {
            $query->where('platform', $request->input('platform'));
        }
        if ($request->filled('product_type')) {
            $query->where('product_type', $request->input('product_type'));
        }

        $total = $query->sum('pay_amount');

        // 按平台分组
        $byPlatform = (clone $query)
            ->select('platform', DB::raw('SUM(pay_amount) as amount'), DB::raw('COUNT(*) as orders'))
            ->groupBy('platform')
            ->get();

        return response()->success([
            'total_amount' => (float) $total,
            'by_platform' => $byPlatform,
        ]);
    }

    /**
     * 用户维度统计
     *
     * GET /api/admin/analytics/users?start=&end=
     */
    public function users(Request $request)
    {
        $request->validate(['start' => 'required|date', 'end' => 'required|date']);

        return response()->success([
            'total_users' => DB::table('users')->count(),
            'new_users' => DB::table('users')->whereBetween('created_at', [$request->input('start'), $request->input('end')])->count(),
            'pay_users' => Order::where('status', Order::STATUS_PAID)
                ->distinct('user_id')->count('user_id'),
            'pay_rate' => null, // 计算需结合总用户
        ]);
    }
}
