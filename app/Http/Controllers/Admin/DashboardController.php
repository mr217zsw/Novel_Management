<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

/**
 * 数据看板控制器（管理层）
 */
class DashboardController extends Controller
{
    public function __construct(private AnalyticsService $analytics)
    {
    }

    /**
     * 实时概览
     *
     * GET /api/admin/dashboard/overview?date=
     */
    public function overview(Request $request)
    {
        return response()->success($this->analytics->overview($request->input('date', 'today')));
    }

    /**
     * 趋势数据
     *
     * GET /api/admin/dashboard/trend?days=7&type=revenue
     */
    public function trend(Request $request)
    {
        $days = (int) $request->input('days', 7);
        $type = $request->input('type', 'revenue');

        return response()->success($this->analytics->trend($days, $type));
    }

    /**
     * 留存分析
     *
     * GET /api/admin/dashboard/retention?date=
     */
    public function retention(Request $request)
    {
        return response()->success($this->analytics->retention($request->input('date', now()->toDateString())));
    }

    /**
     * 书籍排行榜
     *
     * GET /api/admin/dashboard/book-ranking?type=views&limit=10
     */
    public function bookRanking(Request $request)
    {
        return response()->success(
            $this->analytics->bookRanking($request->input('type', 'views'), (int) $request->input('limit', 10))
        );
    }
}
