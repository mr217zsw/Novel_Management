<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyStatistic extends Model
{
    use HasFactory;

    protected $table = 'daily_statistics';

    protected $fillable = [
        'date', 'dau', 'mau', 'new_users', 'pay_users', 'pay_amount',
        'ad_revenue', 'total_revenue', 'cost', 'gross_profit', 'roi',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'pay_amount' => 'float',
            'ad_revenue' => 'float',
            'total_revenue' => 'float',
            'cost' => 'float',
            'gross_profit' => 'float',
            'roi' => 'float',
        ];
    }
}
