<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 投放渠道表
 *
 * 抖音/微信/快手/小红书等。存储授权配置与回调地址。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->comment('渠道名称');
            $table->string('code', 30)->unique()->comment('渠道编码 douyin/wechat/kuaishou');
            $table->string('app_id', 64)->nullable()->comment('平台AppID');
            $table->string('secret_key', 255)->nullable()->comment('授权密钥(加密)');
            $table->string('callback_url', 255)->nullable()->comment('回调地址');
            $table->json('config')->nullable()->comment('扩展配置');
            $table->tinyInteger('status')->default(1)->comment('0停用 1启用');
            $table->unsignedBigInteger('owner_id')->nullable()->index()->comment('负责人ID');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
