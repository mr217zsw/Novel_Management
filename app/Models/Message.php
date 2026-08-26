<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $table = 'messages';

    protected $fillable = [
        'user_id', 'type', 'title', 'content', 'extras', 'is_read', 'read_at',
    ];

    protected function casts(): array
    {
        return [
            'extras' => 'array',
            'is_read' => 'integer',
            'read_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->is_read = 1;
            $this->read_at = now();
            $this->save();
        }
    }
}
