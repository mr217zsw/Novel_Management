<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 广告奖励记录表（IAA 变现）
 *
 * 记录用户观看激励视频获得的阅读币收益及平台 eCPM。
 * ad_type: reward激励视频 / interstitial插屏 / splash开屏
 * ad_platform: 穿山甲/优量汇
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index()->comment('用户ID');
            $table->string('ad_type', 20)->default('reward')->comment('reward/interstitial/splash');
            $table->string('ad_platform', 20)->nullable()->comment('广告平台(穿山甲/优量汇)');
            $table->integer('reward_coins')->default(0)->comment('获得阅读币');
            $table->decimal('ecpm', 10, 2)->default(0)->comment('千次展示收益(分)');
            $table->string('ad_id', 64)->nullable()->comment('广告ID');
            $table->string('request_id', 64)->nullable()->index()->comment('广告请求ID(防重复)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_rewards');
    }
};
