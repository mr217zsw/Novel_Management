<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignDailyStat extends Model
{
    use HasFactory;

    protected $table = 'campaign_daily_stats';

    protected $fillable = [
        'date', 'channel_id', 'campaign_id', 'material_id', 'clicks',
        'registrations', 'pay_users', 'revenue', 'ad_revenue', 'cost',
        'roi', 'cvr',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'revenue' => 'float',
            'ad_revenue' => 'float',
            'cost' => 'float',
            'roi' => 'float',
            'cvr' => 'float',
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
}
