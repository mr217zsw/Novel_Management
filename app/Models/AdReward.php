<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdReward extends Model
{
    use HasFactory;

    protected $table = 'ad_rewards';

    protected $fillable = [
        'user_id', 'ad_type', 'ad_platform', 'reward_coins', 'ecpm',
        'ad_id', 'request_id',
    ];

    protected function casts(): array
    {
        return [
            'ecpm' => 'float',
            'reward_coins' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
