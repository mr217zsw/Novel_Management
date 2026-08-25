<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 用户表
 *
 * 支持微信/抖音/小红书/B站 多端 openid，unionid 统一身份。
 * balance 单位为分（decimal），避免浮点精度问题。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('unionid', 64)->nullable()->unique()->comment('微信/抖音统一ID');
            $table->string('openid_wechat', 64)->nullable()->index()->comment('微信小程序OpenID');
            $table->string('openid_douyin', 64)->nullable()->index()->comment('抖音小程序OpenID');
            $table->string('openid_redbook', 64)->nullable()->index()->comment('小红书OpenID');
            $table->string('openid_bilibili', 64)->nullable()->index()->comment('B站OpenID');
            $table->string('nickname', 50)->nullable()->comment('用户昵称');
            $table->string('avatar_url', 255)->nullable()->comment('头像URL(OSS)');
            $table->string('phone', 20)->nullable()->index()->comment('手机号');
            $table->decimal('balance', 10, 2)->default(0)->comment('阅读币余额(分)');
            $table->timestamp('vip_expire_at')->nullable()->index()->comment('VIP到期时间');
            $table->tinyInteger('status')->default(0)->comment('0正常 1封禁');
            $table->integer('channel_id')->nullable()->index()->comment('注册渠道ID');
            $table->string('register_channel', 20)->nullable()->comment('注册来源');
            $table->timestamp('last_active_at')->nullable()->comment('最后活跃时间');
            $table->boolean('is_super_admin')->default(false)->comment('是否超级管理员');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
