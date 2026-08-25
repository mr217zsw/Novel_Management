<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 订单模型
 *
 * platform: wechat/douyin/apple
 * product_type: recharge/chapter/vip
 * status: 0待付 1已付 2已取消 3已退款
 */
class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'order_no', 'user_id', 'platform', 'platform_order_id', 'product_type',
        'product_id', 'product_name', 'amount', 'pay_amount', 'status',
        'pay_time', 'expire_time', 'callback_data',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'pay_amount' => 'float',
            'status' => 'integer',
            'pay_time' => 'datetime',
            'expire_time' => 'datetime',
            'callback_data' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 状态常量
    public const STATUS_PENDING = 0;
    public const STATUS_PAID = 1;
    public const STATUS_CANCELLED = 2;
    public const STATUS_REFUNDED = 3;

    /**
     * 生成唯一订单号
     */
    public static function generateOrderNo(string $platform = 'wechat'): string
    {
        $prefix = match ($platform) {
            'douyin' => 'DY',
            'apple' => 'AP',
            default => 'WX',
        };

        return $prefix . date('YmdHis') . strtoupper(substr(uniqid(), -8)) . mt_rand(10, 99);
    }
}
