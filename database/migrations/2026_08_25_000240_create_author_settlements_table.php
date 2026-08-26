<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 作者分成结算表
 *
 * 分成模式书籍，按日生成结算记录，财务确认后打款。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('author_settlements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('author_id')->index()->comment('作者ID');
            $table->unsignedBigInteger('order_id')->comment('关联订单ID');
            $table->decimal('amount', 10, 2)->default(0)->comment('分成金额(分)');
            $table->date('settle_date')->comment('结算日期');
            $table->tinyInteger('status')->default(0)->comment('0待确认 1已确认 2已打款 3已驳回');
            $table->string('remark')->nullable()->comment('备注');
            $table->timestamps();

            $table->index(['author_id', 'settle_date']);
            $table->index(['status', 'settle_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('author_settlements');
    }
};
