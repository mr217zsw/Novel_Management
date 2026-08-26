<?php

namespace App\Services;

use App\Models\AdReward;
use App\Models\Campaign;
use App\Models\DailyStatistic;
use App\Models\Order;
use App\Models\User;
use App\Models\UserRead;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * 数据分析服务
 *
 * 实时看板 / ROI 分析 / 用户分析 / 内容分析。
 */
class AnalyticsService
{
    /**
     * 实时看板概览
     */
    public function overview(string $date = 'today'): array
    {
        $date = $this->resolveDate($date);

        return [
            'date' => $date->toDateString(),
            'dau' => $this->dau($date),
            'new_users' => User::whereDate('created_at', $date)->count(),
            'pay_amount' => (float) Order::where('status', Order::STATUS_PAID)
                ->whereDate('pay_time', $date)
                ->sum('amount'),
            'ad_revenue' => (float) AdReward::whereDate('created_at', $date)->sum('ecpm'),
            'cost' => (float) Campaign::sum('cost'),
            'orders' => Order::where('status', Order::STATUS_PAID)
                ->whereDate('pay_time', $date)
                ->count(),
        ];
    }

    /**
     * 日活（有阅读记录的用户数）
     */
    public function dau(Carbon $date): int
    {
        return UserRead::whereDate('read_at', $date)
            ->distinct('user_id')
            ->count('user_id');
    }

    /**
     * 近 N 天趋势数据
     */
    public function trend(int $days = 7, string $type = 'revenue'): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $stats = DailyStatistic::where('date', '>=', $start->toDateString())
            ->orderBy('date')
            ->get();

        return $stats->map(function ($item) use ($type) {
            $value = match ($type) {
                'dau' => $item->dau,
                'new_users' => $item->new_users,
                'pay_amount' => $item->pay_amount,
                'ad_revenue' => $item->ad_revenue,
                'cost' => $item->cost,
                'roi' => $item->roi,
                default => $item->total_revenue,
            };

            return [
                'date' => $item->date,
                'value' => $value,
            ];
        })->toArray();
    }

    /**
     * 用户留存分析（按注册日期）
     */
    public function retention(string $date): array
    {
        $registerDate = Carbon::parse($date);

        $dayUsers = User::whereDate('created_at', $registerDate)->pluck('id');

        $day1 = $this->activeUsers($dayUsers, $registerDate->copy()->addDay());
        $day7 = $this->activeUsers($dayUsers, $registerDate->copy()->addDays(7));
        $day30 = $this->activeUsers($dayUsers, $registerDate->copy()->addDays(30));

        $total = count($dayUsers);

        return [
            'register_date' => $registerDate->toDateString(),
            'new_users' => $total,
            'retention_1d' => $total > 0 ? round($day1 / $total * 100, 2) : 0,
            'retention_7d' => $total > 0 ? round($day7 / $total * 100, 2) : 0,
            'retention_30d' => $total > 0 ? round($day30 / $total * 100, 2) : 0,
        ];
    }

    protected function activeUsers($userIds, Carbon $onDate): int
    {
        if ($userIds->isEmpty()) {
            return 0;
        }

        return UserRead::whereIn('user_id', $userIds)
            ->whereDate('read_at', $onDate)
            ->distinct('user_id')
            ->count('user_id');
    }

    /**
     * 书籍热度榜（按阅读量/付费）
     */
    public function bookRanking(string $type = 'views', int $limit = 10): array
    {
        $column = match ($type) {
            'likes' => 'total_likes',
            'favorites' => 'total_favorites',
            'pay' => 'total_chapters',
            default => 'total_views',
        };

        return DB::table('books')
            ->where('status', 2)
            ->orderByDesc($column)
            ->limit($limit)
            ->select('id', 'title', 'cover_url', 'total_views', 'total_likes', 'total_favorites', 'total_chapters')
            ->get()
            ->toArray();
    }

    protected function resolveDate(string $date): Carbon
    {
        if ($date === 'today') {
            return now();
        }
        return Carbon::parse($date);
    }
}
