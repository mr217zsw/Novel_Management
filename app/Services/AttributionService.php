<?php

namespace App\Services;

use App\Models\AdReward;
use App\Models\AttributionRecord;
use App\Models\Campaign;
use App\Models\CampaignDailyStat;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * 投流归因服务（核心亮点）
 *
 * 全链路：用户点击广告 → 注册 → 付费 → ROI 计算。
 * 归因维度：渠道 / 计划 / 素材 / 书籍。
 */
class AttributionService
{
    /**
     * 记录点击（广告平台回调落地）
     */
    public function recordClick(
        int $channelId,
        string $clickId,
        string $deviceId,
        ?string $ip = null,
        ?string $ua = null,
        ?int $campaignId = null,
        ?int $materialId = null,
        ?string $referer = null
    ): AttributionRecord {
        return AttributionRecord::updateOrCreate(
            ['click_id' => $clickId],
            [
                'channel_id' => $channelId,
                'campaign_id' => $campaignId,
                'material_id' => $materialId,
                'device_id' => $deviceId,
                'ip' => $ip,
                'user_agent' => $ua,
                'referer' => $referer,
                'click_time' => now(),
            ]
        );
    }

    /**
     * 用户注册后归因
     *
     * 通过设备ID匹配最近的未归因点击记录，绑定 user_id。
     */
    public function attributeUser(int $userId, string $deviceId, string $platform = 'wechat'): void
    {
        $record = AttributionRecord::where('device_id', $deviceId)
            ->whereNull('user_id')
            ->latest('click_time')
            ->first();

        if (!$record) {
            return;
        }

        $record->user_id = $userId;
        $record->register_time = now();
        $record->save();

        User::where('id', $userId)->update([
            'channel_id' => $record->channel_id,
            'register_channel' => $platform,
        ]);
    }

    /**
     * 用户付费后更新归因首付信息
     */
    public function attributePay(int $userId, float $amount): void
    {
        $record = AttributionRecord::where('user_id', $userId)
            ->whereNull('pay_time')
            ->latest('click_time')
            ->first();

        if ($record) {
            $record->pay_time = now();
            $record->pay_amount = $amount;
            $record->save();
        }
    }

    /**
     * 计算渠道 ROI
     *
     * @param int $channelId
     * @param array $dateRange ['start' => Carbon, 'end' => Carbon]
     */
    public function calculateChannelROI(int $channelId, array $dateRange): array
    {
        $campaigns = Campaign::where('channel_id', $channelId)->get();
        $campaignIds = $campaigns->pluck('id');
        $campaignCost = $campaigns->sum('cost');

        $userIds = User::where('channel_id', $channelId)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->pluck('id');

        $payAmount = Order::whereIn('user_id', $userIds)
            ->where('status', Order::STATUS_PAID)
            ->sum('amount');

        $adRevenue = AdReward::whereIn('user_id', $userIds)
            ->sum('ecpm');

        $revenue = $payAmount + $adRevenue;

        return [
            'channel_id' => $channelId,
            'cost' => $campaignCost,
            'pay_amount' => $payAmount,
            'ad_revenue' => $adRevenue,
            'revenue' => $revenue,
            'roi' => $campaignCost > 0 ? round($revenue / $campaignCost, 2) : 0,
            'users' => count($userIds),
        ];
    }

    /**
     * 计算单本小说 ROI
     */
    public function calculateBookROI(int $bookId, array $dateRange): array
    {
        $campaignIds = Campaign::where('book_id', $bookId)->pluck('id');
        $cost = Campaign::whereIn('id', $campaignIds)->sum('cost');

        // 通过归因记录找到该计划带来的付费
        $payAmount = AttributionRecord::whereIn('campaign_id', $campaignIds)
            ->whereNotNull('pay_time')
            ->whereBetween('pay_time', [$dateRange['start'], $dateRange['end']])
            ->sum('pay_amount');

        return [
            'book_id' => $bookId,
            'cost' => $cost,
            'revenue' => $payAmount,
            'roi' => $cost > 0 ? round($payAmount / $cost, 2) : 0,
        ];
    }

    /**
     * 按渠道汇总 ROI（用于 ROI 分析报表）
     */
    public function channelROIList(array $dateRange, ?int $campaignId = null, ?int $materialId = null): array
    {
        $query = CampaignDailyStat::whereBetween('date', [
            Carbon::parse($dateRange['start'])->toDateString(),
            Carbon::parse($dateRange['end'])->toDateString(),
        ]);

        if ($campaignId) {
            $query->where('campaign_id', $campaignId);
        }
        if ($materialId) {
            $query->where('material_id', $materialId);
        }

        return $query->with('channel:id,name')
            ->get()
            ->groupBy('channel_id')
            ->map(function ($items) {
                $cost = $items->sum('cost');
                $revenue = $items->sum('revenue') + $items->sum('ad_revenue');
                $clicks = $items->sum('clicks');
                $registrations = $items->sum('registrations');

                return [
                    'channel_id' => $items->first()->channel_id,
                    'channel_name' => $items->first()->channel?->name,
                    'cost' => $cost,
                    'revenue' => $revenue,
                    'clicks' => $clicks,
                    'registrations' => $registrations,
                    'roi' => $cost > 0 ? round($revenue / $cost, 2) : 0,
                    'cvr' => $clicks > 0 ? round($registrations / $clicks * 100, 2) : 0,
                ];
            })
            ->values()
            ->toArray();
    }
}
