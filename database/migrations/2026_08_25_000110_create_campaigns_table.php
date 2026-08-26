<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 投放计划表
 *
 * 归属渠道，推广指定书籍，控制预算与出价。
 * status: 0草稿 1投放中 2暂停 3结束
 * bid_strategy: 1智能出价 2手动出价
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('channel_id')->index()->comment('渠道ID');
            $table->string('name', 100)->comment('计划名称');
            $table->unsignedBigInteger('book_id')->nullable()->index()->comment('推广的小说');
            $table->string('target_url', 255)->nullable()->comment('落地页URL');
            $table->decimal('daily_budget', 10, 2)->default(0)->comment('日预算(分)');
            $table->decimal('total_budget', 10, 2)->default(0)->comment('总预算(分)');
            $table->tinyInteger('bid_strategy')->default(1)->comment('1智能 2手动');
            $table->decimal('bid_price', 10, 2)->default(0)->comment('出价(分)');
            $table->tinyInteger('status')->default(0)->comment('0草稿 1投放中 2暂停 3结束');
            $table->date('start_date')->nullable()->comment('开始日期');
            $table->date('end_date')->nullable()->comment('结束日期');
            $table->decimal('cost', 10, 2)->default(0)->comment('累计消耗(分)');
            $table->unsignedBigInteger('created_by')->nullable()->comment('创建人');
            $table->timestamps();

            $table->index(['channel_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
