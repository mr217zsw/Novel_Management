<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 书籍模型
 *
 * status: 0草稿 1待审 2已上架 3已下架
 * audit_status: 0待审 1通过 2驳回
 */
class Book extends Model
{
    use HasFactory;

    // status 状态
    public const STATUS_DRAFT = 0;          // 草稿
    public const STATUS_PENDING_AUDIT = 1;  // 待审核
    public const STATUS_PUBLISHED = 2;      // 已上架
    public const STATUS_OFFLINE = 3;        // 已下架

    // audit_status 审核状态
    public const AUDIT_PENDING = 0;         // 待审
    public const AUDIT_PASSED = 1;          // 通过
    public const AUDIT_REJECTED = 2;        // 驳回

    protected $table = 'books';

    protected $fillable = [
        'author_id', 'title', 'cover_url', 'description', 'category_id', 'tags',
        'copyright_type', 'copyright_price', 'contract_start', 'contract_end',
        'royalty_rate', 'total_chapters', 'total_words', 'total_views',
        'total_likes', 'total_favorites', 'min_price', 'status', 'audit_status',
        'audit_remark', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'copyright_price' => 'float',
            'contract_start' => 'date',
            'contract_end' => 'date',
            'royalty_rate' => 'float',
            'min_price' => 'float',
            'published_at' => 'datetime',
        ];
    }

    public function author()
    {
        return $this->belongsTo(Author::class, 'author_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class, 'novel_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'novel_id');
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class, 'book_id');
    }
}
