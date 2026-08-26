<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'unionid',
        'openid_wechat',
        'openid_douyin',
        'openid_redbook',
        'openid_bilibili',
        'nickname',
        'avatar_url',
        'phone',
        'password',
        'balance',
        'vip_expire_at',
        'status',
        'channel_id',
        'register_channel',
        'last_active_at',
        'is_super_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'float',
            'vip_expire_at' => 'datetime',
            'last_active_at' => 'datetime',
            'is_super_admin' => 'boolean',
        ];
    }

    // ---- JWT ----

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'platform' => $this->register_channel ?? 'web',
        ];
    }

    // ---- 关系 ----

    public function books()
    {
        return $this->hasMany(Book::class, 'author_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function readRecords()
    {
        return $this->hasMany(UserRead::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function chapterPurchases()
    {
        return $this->hasMany(UserChapterPurchase::class);
    }

    public function adRewards()
    {
        return $this->hasMany(AdReward::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'user_departments');
    }

    public function author()
    {
        return $this->hasOne(Author::class);
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class, 'channel_id');
    }

    // ---- 业务辅助 ----

    public function isVip(): bool
    {
        return $this->vip_expire_at !== null && $this->vip_expire_at->isFuture();
    }

    public function isBanned(): bool
    {
        return (int) $this->status === 1;
    }

    /**
     * 是否拥有指定权限（供业务内快速判断）
     */
    public function hasPermission(string $code): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', fn ($q) => $q->where('code', $code))
            ->exists();
    }
}
