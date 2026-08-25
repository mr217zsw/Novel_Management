<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 章节模型
 *
 * 核心：内容存 OSS，MySQL 只存 oss_key 与 cdn_url。
 * content() 方法从 OSS 拉取正文。
 */
class Chapter extends Model
{
    use HasFactory;

    protected $table = 'chapters';

    protected $fillable = [
        'novel_id', 'chapter_no', 'title', 'content_oss_key', 'content_cdn_url',
        'word_count', 'is_free', 'price', 'status', 'audit_status',
        'audit_remark', 'audit_time', 'auditor_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'audit_time' => 'datetime',
        ];
    }

    public function novel()
    {
        return $this->belongsTo(Book::class, 'novel_id');
    }

    public function purchases()
    {
        return $this->hasMany(UserChapterPurchase::class);
    }

    /**
     * 拉取章节正文（从 OSS）
     */
    public function getContentAttribute(): string
    {
        return app(\App\Services\OSS\OssStorageService::class)->getContent($this->content_oss_key);
    }

    /**
     * 判断用户是否已解锁本章
     */
    public function isUnlockedBy(int $userId): bool
    {
        if ((int) $this->is_free === 1) {
            return true;
        }

        $user = User::find($userId);
        if ($user && $user->isVip()) {
            return true;
        }

        return UserChapterPurchase::where('user_id', $userId)
            ->where('chapter_id', $this->id)
            ->exists();
    }
}
