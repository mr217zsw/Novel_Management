<?php

namespace App\Console\Commands;

use App\Models\AdReward;
use App\Models\Campaign;
use App\Models\DailyStatistic;
use App\Models\Order;
use App\Models\User;
use App\Models\UserRead;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * 计算日报统计
 *
 * 生成指定日期（默认昨日）的 DAU / 新增 / 收入 / 广告 / 成本 / ROI。
 */
class CalculateDailyStatistics extends Command
{
    protected $signature = 'novel:daily-statistics {--date= : 统计日期，默认昨日}';

    protected $description = '计算每日统计（DAU/收入/ROI）';

    public function handle(): int
    {
        $date = $this->option('date') ?: now()->subDay()->toDateString();

        $this->info("开始统计日期：{$date}");

        $start = $date . ' 00:00:00';
        $end = $date . ' 23:59:59';

        $dau = UserRead::whereBetween('read_at', [$start, $end])->distinct('user_id')->count('user_id');
        $newUsers = User::whereBetween('created_at', [$start, $end])->count();
        $payUsers = Order::where('status', Order::STATUS_PAID)
            ->whereBetween('pay_time', [$start, $end])
            ->distinct('user_id')->count('user_id');
        $payAmount = Order::where('status', Order::STATUS_PAID)
            ->whereBetween('pay_time', [$start, $end])
            ->sum('pay_amount');
        $adRevenue = AdReward::whereBetween('created_at', [$start, $end])->sum('ecpm');

        $totalRevenue = $payAmount + $adRevenue;

        // 当日投放成本（简化：按 campaign 当日 created 估算，生产应从渠道API拉取实际消耗）
        $cost = Campaign::whereBetween('created_at', [$start, $end])->sum('cost');

        $grossProfit = $totalRevenue - $cost;
        $roi = $cost > 0 ? round($totalRevenue / $cost, 2) : 0;

        // MAU：近30天活跃
        $mau = UserRead::where('read_at', '>=', now()->subDays(30)->toDateString())
            ->distinct('user_id')->count('user_id');

        DailyStatistic::updateOrCreate(
            ['date' => $date],
            [
                'dau' => $dau,
                'mau' => $mau,
                'new_users' => $newUsers,
                'pay_users' => $payUsers,
                'pay_amount' => $payAmount,
                'ad_revenue' => $adRevenue,
                'total_revenue' => $totalRevenue,
                'cost' => $cost,
                'gross_profit' => $grossProfit,
                'roi' => $roi,
            ]
        );

        $this->info("统计完成：DAU={$dau} 收入={$totalRevenue} ROI={$roi}");

        return self::SUCCESS;
    }
}
