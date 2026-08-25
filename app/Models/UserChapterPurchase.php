<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserChapterPurchase extends Model
{
    use HasFactory;

    protected $table = 'user_chapter_purchases';

    protected $fillable = ['user_id', 'chapter_id', 'order_id', 'price'];

    protected function casts(): array
    {
        return ['price' => 'float'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
