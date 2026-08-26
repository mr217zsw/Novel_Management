<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'name', 'product_type', 'price', 'coin_amount', 'vip_days',
        'is_active', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'coin_amount' => 'float',
            'vip_days' => 'integer',
            'is_active' => 'integer',
        ];
    }

    public const TYPE_RECHARGE = 'recharge';
    public const TYPE_VIP = 'vip';
}
