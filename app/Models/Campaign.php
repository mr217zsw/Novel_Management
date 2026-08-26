<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 投放计划模型
 *
 * status: 0草稿 1投放中 2暂停 3结束
 * bid_strategy: 1智能 2手动
 */
class Campaign extends Model
{
    use HasFactory;

    protected $table = 'campaigns';

    protected $fillable = [
        'channel_id', 'name', 'book_id', 'target_url', 'daily_budget',
        'total_budget', 'bid_strategy', 'bid_price', 'status',
        'start_date', 'end_date', 'cost', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'daily_budget' => 'float',
            'total_budget' => 'float',
            'bid_price' => 'float',
            'cost' => 'float',
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => 'integer',
        ];
    }

    // 状态常量
    public const STATUS_DRAFT = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_PAUSED = 2;
    public const STATUS_ENDED = 3;

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function materials()
    {
        return $this->hasMany(Material::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
