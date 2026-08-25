<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $table = 'comments';

    protected $fillable = [
        'user_id', 'novel_id', 'chapter_id', 'parent_id', 'content',
        'like_count', 'status',
    ];

    protected function casts(): array
    {
        return [
            'like_count' => 'integer',
            'status' => 'integer',
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

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }
}
