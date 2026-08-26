<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 订单表
 *
 * platform: wechat/douyin/apple
 * product_type: recharge充值 / chapter章节 / vip会员
 * status: 0待付 1已付 2已取消 3已退款
 * 金额单位分。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 32)->unique()->comment('订单号');
            $table->unsignedBigInteger('user_id')->index()->comment('用户ID');
            $table->string('platform', 20)->default('wechat')->comment('wechat/douyin/apple');
            $table->string('platform_order_id', 64)->nullable()->index()->comment('平台订单ID');
            $table->string('product_type', 20)->default('recharge')->comment('recharge/chapter/vip');
            $table->unsignedBigInteger('product_id')->nullable()->comment('商品ID');
            $table->string('product_name', 100)->nullable()->comment('商品名称');
            $table->decimal('amount', 10, 2)->default(0)->comment('金额(分)');
            $table->decimal('pay_amount', 10, 2)->default(0)->comment('实付金额(分)');
            $table->tinyInteger('status')->default(0)->comment('0待付 1已付 2已取消 3已退款');
            $table->timestamp('pay_time')->nullable()->comment('支付时间');
            $table->timestamp('expire_time')->nullable()->comment('订单过期时间');
            $table->json('callback_data')->nullable()->comment('回调原始数据');
            $table->timestamps();

            $table->index(['user_id', 'status', 'created_at'], 'idx_user_status_created');
            $table->index('platform');
            $table->index('product_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
