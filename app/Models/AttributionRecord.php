<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 归因记录模型
 *
 * 追踪用户 点击广告 → 注册 → 付费 全链路，用于 ROI 计算。
 */
class AttributionRecord extends Model
{
    use HasFactory;

    protected $table = 'attribution_records';

    protected $fillable = [
        'channel_id', 'campaign_id', 'material_id', 'click_id', 'user_id',
        'device_id', 'ip', 'user_agent', 'referer', 'click_time',
        'register_time', 'pay_time', 'pay_amount',
    ];

    protected function casts(): array
    {
        return [
            'click_time' => 'datetime',
            'register_time' => 'datetime',
            'pay_time' => 'datetime',
            'pay_amount' => 'float',
        ];
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
