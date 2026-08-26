<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 归因记录表
 *
 * 用户点击广告时记录（click_id 唯一），注册后回填 user_id，付费后回填 pay_time/amount。
 * 核心用于 ROI 精确计算：渠道 → 计划 → 素材 → 用户 → 付费。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attribution_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('channel_id')->index()->comment('渠道ID');
            $table->unsignedBigInteger('campaign_id')->nullable()->index()->comment('投放计划ID');
            $table->unsignedBigInteger('material_id')->nullable()->index()->comment('素材ID');
            $table->string('click_id', 64)->nullable()->unique()->comment('平台点击ID');
            $table->unsignedBigInteger('user_id')->nullable()->index()->comment('用户ID(注册后回填)');
            $table->string('device_id', 64)->nullable()->index()->comment('设备ID');
            $table->string('ip', 45)->nullable()->comment('IP地址');
            $table->text('user_agent')->nullable()->comment('设备信息');
            $table->string('referer', 255)->nullable()->comment('来源页');
            $table->timestamp('click_time')->nullable()->index()->comment('点击时间');
            $table->timestamp('register_time')->nullable()->comment('注册时间');
            $table->timestamp('pay_time')->nullable()->comment('首付时间');
            $table->decimal('pay_amount', 10, 2)->default(0)->comment('首付金额');
            $table->timestamps();

            $table->index(['campaign_id', 'click_time'], 'idx_campaign_click');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribution_records');
    }
};
