<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * 认证控制器
 *
 * 小程序 openid 登录 / 手机验证码登录 / 后台账号密码登录 / 登出。
 */
class AuthController extends Controller
{
    public function __construct(private AuthService $authService)
    {
    }

    /**
     * 小程序 openid 登录（自动注册）
     *
     * POST /api/auth/login
     * body: { platform: wechat, code | openid, nickname?, avatar_url?, device_id? }
     */
    public function login(Request $request)
    {
        $request->validate([
            'platform' => 'required|in:wechat,douyin,redbook,bilibili',
            // 小程序可通过 code 换 openid，此处简化：直接传 openid（生产需 code2session 换取）
            'openid' => 'required|string',
            'unionid' => 'nullable|string',
            'nickname' => 'nullable|string|max:50',
            'avatar_url' => 'nullable|url',
            'device_id' => 'nullable|string',
        ]);

        $result = $this->authService->loginByOpenid(
            $request->input('platform'),
            $request->input('openid'),
            $request->input('unionid'),
            $request->only(['nickname', 'avatar_url', 'device_id'])
        );

        return response()->success($result);
    }

    /**
     * 手机号验证码登录
     */
    public function loginByPhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|size:11',
            'code' => 'required|string|size:6',
        ]);

        $result = $this->authService->loginByPhone($request->input('phone'), $request->input('code'));

        return response()->success($result);
    }

    /**
     * 发送短信验证码
     */
    public function sendSmsCode(Request $request)
    {
        $request->validate(['phone' => 'required|string|size:11']);

        return response()->success($this->authService->sendSmsCode($request->input('phone')));
    }

    /**
     * 后台账号密码登录
     */
    public function adminLogin(Request $request)
    {
        $request->validate([
            'account' => 'required|string',
            'password' => 'required|string',
        ]);

        $result = $this->authService->adminLogin($request->input('account'), $request->input('password'));

        return response()->success($result);
    }

    /**
     * 刷新 Token
     */
    public function refresh()
    {
        return response()->success([
            'token' => JWTAuth::refresh(),
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ]);
    }

    /**
     * 登出（拉黑 Token）
     */
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->success(null, '已退出登录');
    }

    /**
     * 获取当前登录用户信息
     */
    public function me(Request $request)
    {
        return response()->success($request->user());
    }
}
