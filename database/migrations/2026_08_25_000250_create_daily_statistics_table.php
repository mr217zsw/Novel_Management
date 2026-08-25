<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 日报表
 *
 * 每日统计：DAU/MAU/新增/付费/广告收入/投放成本/ROI。
 * 由 CalculateDailyStatistics 定时任务生成。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_statistics', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique()->comment('日期');
            $table->integer('dau')->default(0)->comment('日活');
            $table->integer('mau')->default(0)->comment('月活');
            $table->integer('new_users')->default(0)->comment('新增用户');
            $table->integer('pay_users')->default(0)->comment('付费用户');
            $table->decimal('pay_amount', 10, 2)->default(0)->comment('内购收入');
            $table->decimal('ad_revenue', 10, 2)->default(0)->comment('广告收入');
            $table->decimal('total_revenue', 10, 2)->default(0)->comment('总收入');
            $table->decimal('cost', 10, 2)->default(0)->comment('投放成本');
            $table->decimal('gross_profit', 10, 2)->default(0)->comment('毛利');
            $table->decimal('roi', 5, 2)->default(0)->comment('ROI');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_statistics');
    }
};
