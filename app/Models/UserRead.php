<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRead extends Model
{
    use HasFactory;

    protected $table = 'user_reads';

    protected $fillable = [
        'user_id', 'novel_id', 'chapter_id', 'read_duration', 'progress',
        'device_type', 'ip', 'read_at',
    ];

    protected function casts(): array
    {
        return [
            'progress' => 'float',
            'read_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function novel()
    {
        return $this->belongsTo(Book::class, 'novel_id');
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}
