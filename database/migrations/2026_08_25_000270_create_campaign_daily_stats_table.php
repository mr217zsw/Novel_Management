<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 投放计划日报表
 *
 * 用于渠道/计划/素材维度 ROI 分析。
 * 由统计任务按日汇总归因与订单数据生成。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date')->comment('日期');
            $table->unsignedBigInteger('channel_id')->index()->comment('渠道ID');
            $table->unsignedBigInteger('campaign_id')->nullable()->index()->comment('投放计划ID');
            $table->unsignedBigInteger('material_id')->nullable()->index()->comment('素材ID');
            $table->integer('clicks')->default(0)->comment('点击数');
            $table->integer('registrations')->default(0)->comment('注册数');
            $table->integer('pay_users')->default(0)->comment('付费用户数');
            $table->decimal('revenue', 10, 2)->default(0)->comment('收入');
            $table->decimal('ad_revenue', 10, 2)->default(0)->comment('广告收入');
            $table->decimal('cost', 10, 2)->default(0)->comment('投放成本');
            $table->decimal('roi', 5, 2)->default(0)->comment('ROI');
            $table->decimal('cvr', 5, 2)->default(0)->comment('转化率(注册/点击)');
            $table->timestamps();

            $table->unique(['date', 'channel_id', 'campaign_id'], 'uniq_date_channel_campaign');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_daily_stats');
    }
};
