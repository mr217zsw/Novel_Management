<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 投放渠道模型
 */
class Channel extends Model
{
    use HasFactory;

    protected $table = 'channels';

    protected $fillable = [
        'name', 'code', 'app_id', 'secret_key', 'callback_url', 'config',
        'status', 'owner_id',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'status' => 'integer',
        ];
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
