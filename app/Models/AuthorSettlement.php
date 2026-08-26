<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthorSettlement extends Model
{
    use HasFactory;

    protected $table = 'author_settlements';

    protected $fillable = [
        'author_id', 'order_id', 'amount', 'settle_date', 'status', 'remark',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'settle_date' => 'date',
            'status' => 'integer',
        ];
    }

    public const STATUS_PENDING = 0;
    public const STATUS_CONFIRMED = 1;
    public const STATUS_PAID = 2;
    public const STATUS_REJECTED = 3;

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
