<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 投放素材模型
 *
 * type: 1图片 2视频
 * status: 0待审 1通过 2驳回 3已删除
 */
class Material extends Model
{
    use HasFactory;

    protected $table = 'materials';

    protected $fillable = [
        'campaign_id', 'name', 'type', 'oss_key', 'cdn_url', 'file_size',
        'mime_type', 'width', 'height', 'duration', 'status', 'audit_remark',
        'ctr', 'conversion_rate', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'duration' => 'integer',
            'ctr' => 'float',
            'conversion_rate' => 'float',
            'status' => 'integer',
            'type' => 'integer',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
