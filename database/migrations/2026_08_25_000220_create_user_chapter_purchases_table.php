<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 用户章节购买记录表
 *
 * 记录用户解锁的付费章节，用于阅读器鉴权（该章节是否已解锁）。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_chapter_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index()->comment('用户ID');
            $table->unsignedBigInteger('chapter_id')->index()->comment('章节ID');
            $table->unsignedBigInteger('order_id')->nullable()->comment('关联订单ID');
            $table->decimal('price', 10, 2)->default(0)->comment('购买价格(分)');
            $table->timestamps();

            $table->unique(['user_id', 'chapter_id'], 'uniq_user_chapter');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_chapter_purchases');
    }
};
