<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 作者表
 *
 * 身份证/银行卡敏感字段加密存储（casts 处理）。
 * royalty_rate 为分成比例（分成模式）。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index()->comment('关联用户ID');
            $table->string('pen_name', 50)->comment('笔名');
            $table->string('real_name', 50)->nullable()->comment('真实姓名');
            $table->string('id_card_encrypted')->nullable()->comment('身份证(加密)');
            $table->string('phone', 20)->nullable()->index()->comment('手机号');
            $table->string('bank_card_encrypted')->nullable()->comment('银行卡(加密)');
            $table->string('bank_name', 50)->nullable()->comment('开户行');
            $table->decimal('royalty_rate', 5, 2)->default(0)->comment('分成比例(分成模式)');
            $table->tinyInteger('status')->default(1)->comment('1签约 2解约');
            $table->date('contract_start')->nullable()->comment('合同开始');
            $table->date('contract_end')->nullable()->comment('合同到期');
            $table->string('contract_url', 255)->nullable()->comment('合同文件URL');
            $table->string('remark')->nullable()->comment('备注');
            $table->timestamps();

            $table->index('pen_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authors');
    }
};
