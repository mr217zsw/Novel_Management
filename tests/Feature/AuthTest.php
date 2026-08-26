<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 小程序 openid 登录（自动注册）
     */
    public function test_openid_login_creates_user(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'platform' => 'wechat',
            'openid' => 'wx_openid_123456',
            'nickname' => '测试用户',
        ]);

        $response->assertOk()
            ->assertJson(['code' => 0])
            ->assertJsonStructure(['data' => ['token', 'user']]);

        $this->assertDatabaseHas('users', ['openid_wechat' => 'wx_openid_123456']);
    }

    /**
     * 相同 openid 重复登录返回同一用户
     */
    public function test_same_openid_returns_same_user(): void
    {
        $this->postJson('/api/v1/auth/login', ['platform' => 'douyin', 'openid' => 'dy_001']);
        $first = $this->postJson('/api/v1/auth/login', ['platform' => 'douyin', 'openid' => 'dy_001']);

        $first->assertOk();
        $this->assertEquals(1, User::where('openid_douyin', 'dy_001')->count());
    }

    /**
     * 后台密码登录
     */
    public function test_admin_login(): void
    {
        User::create([
            'phone' => '13800000000',
            'password' => Hash::make('Admin@123456'),
            'nickname' => '管理员',
            'is_super_admin' => true,
            'status' => 0,
        ]);

        $response = $this->postJson('/api/admin/login', [
            'account' => '13800000000',
            'password' => 'Admin@123456',
        ]);

        $response->assertOk()->assertJson(['code' => 0]);
    }

    /**
     * 错误密码登录失败
     */
    public function test_admin_login_wrong_password(): void
    {
        User::create([
            'phone' => '13800000000',
            'password' => Hash::make('Admin@123456'),
            'nickname' => '管理员',
        ]);

        $this->postJson('/api/admin/login', [
            'account' => '13800000000',
            'password' => 'wrong',
        ])->assertStatus(400);
    }
}
