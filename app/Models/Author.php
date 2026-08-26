<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory;

    protected $table = 'authors';

    protected $fillable = [
        'user_id', 'pen_name', 'real_name', 'id_card_encrypted', 'phone',
        'bank_card_encrypted', 'bank_name', 'royalty_rate', 'status',
        'contract_start', 'contract_end', 'contract_url', 'remark',
    ];

    protected function casts(): array
    {
        return [
            'royalty_rate' => 'float',
            'contract_start' => 'date',
            'contract_end' => 'date',
            'id_card_encrypted' => 'encrypted',
            'bank_card_encrypted' => 'encrypted',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function books()
    {
        return $this->hasMany(Book::class);
    }

    public function settlements()
    {
        return $this->hasMany(AuthorSettlement::class);
    }
}
