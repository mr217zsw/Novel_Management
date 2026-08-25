<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * 认证服务
 *
 * 支持：
 * - 小程序 openid 登录（微信/抖音/小红书/B站）
 * - 手机号验证码登录（模拟模式）
 * - 后台账号密码登录
 */
class AuthService
{
    /**
     * 小程序 openid 登录（自动注册）
     *
     * @param string $platform wechat/douyin/redbook/bilibili
     * @param string $openid 平台 openid
     * @param string|null $unionid 统一身份ID
     */
    public function loginByOpenid(string $platform, string $openid, ?string $unionid = null, array $attrs = []): array
    {
        if (empty($openid)) {
            throw new RuntimeException('openid 不能为空');
        }

        $field = 'openid_' . $platform;

        $user = User::where($field, $openid)->first();

        if (!$user && $unionid) {
            $user = User::where('unionid', $unionid)->first();
            if ($user) {
                $user->{$field} = $openid;
                $user->save();
            }
        }

        $isNew = false;
        if (!$user) {
            $isNew = true;
            $user = User::create(array_merge([
                $field => $openid,
                'unionid' => $unionid,
                'nickname' => $attrs['nickname'] ?? '书友_' . Str::random(4),
                'avatar_url' => $attrs['avatar_url'] ?? null,
                'register_channel' => $platform,
            ], $attrs));

            // 归因绑定
            $deviceId = $attrs['device_id'] ?? null;
            if ($deviceId) {
                app(AttributionService::class)->attributeUser($user->id, $deviceId, $platform);
            }
        }

        $user->last_active_at = now();
        $user->save();

        $token = JWTAuth::fromUser($user);

        return [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'user' => $user,
            'is_new' => $isNew,
        ];
    }

    /**
     * 手机号验证码登录（模拟模式：任意手机号 + 验证码 123456）
     */
    public function loginByPhone(string $phone, string $code): array
    {
        if (config('mock.sms')) {
            if ($code !== '123456') {
                throw new RuntimeException('验证码错误');
            }
        }

        $user = User::where('phone', $phone)->first();
        $isNew = false;
        if (!$user) {
            $isNew = true;
            $user = User::create([
                'phone' => $phone,
                'nickname' => '书友_' . Str::random(4),
                'register_channel' => 'web',
            ]);
        }

        $user->last_active_at = now();
        $user->save();

        $token = JWTAuth::fromUser($user);

        return [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'user' => $user,
            'is_new' => $isNew,
        ];
    }

    /**
     * 后台账号密码登录
     */
    public function adminLogin(string $phoneOrEmail, string $password): array
    {
        // users 表无 email 字段，仅支持手机号/账号登录
        $user = User::where('phone', $phoneOrEmail)->first();

        if (!$user) {
            throw new RuntimeException('账号或密码错误');
        }

        if (!$user || !Hash::check($password, $user->password ?? '')) {
            throw new RuntimeException('账号或密码错误');
        }

        if ($user->isBanned()) {
            throw new RuntimeException('账号已被封禁');
        }

        $token = JWTAuth::fromUser($user);

        return [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'user' => $user,
        ];
    }

    /**
     * 发送短信验证码（模拟模式：仅记录日志）
     */
    public function sendSmsCode(string $phone): array
    {
        $code = (string) mt_rand(100000, 999999);

        if (config('mock.sms')) {
            \Illuminate\Support\Facades\Log::info('【模拟短信验证码】', [
                'phone' => $phone,
                'code' => '123456',
            ]);
            $code = '123456';
        }

        // 生产环境：调用阿里云/腾讯云短信服务
        // $sms->send($phone, $code);

        return ['sent' => true, 'expire' => 300];
    }
}
