<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 站内信/通知表
 *
 * type: order订单 / audit审核 / system系统 / ad广告奖励
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index()->comment('接收用户ID');
            $table->string('type', 20)->default('system')->comment('order/audit/system/ad');
            $table->string('title', 100)->comment('标题');
            $table->text('content')->nullable()->comment('内容');
            $table->json('extras')->nullable()->comment('扩展数据');
            $table->tinyInteger('is_read')->default(0)->comment('0未读 1已读');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
