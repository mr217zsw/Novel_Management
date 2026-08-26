<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 虚拟商品表
 *
 * 充值档位 / VIP 套餐。用于小程序端充值、会员开通。
 * product_type: recharge充值 / vip会员
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('商品名称');
            $table->string('product_type', 20)->default('recharge')->comment('recharge/vip');
            $table->decimal('price', 10, 2)->comment('售价(分)');
            $table->decimal('coin_amount', 10, 2)->default(0)->comment('赠送阅读币数量');
            $table->integer('vip_days')->default(0)->comment('VIP天数(vip商品)');
            $table->tinyInteger('is_active')->default(1)->comment('0下架 1上架');
            $table->integer('sort')->default(0)->comment('排序');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
