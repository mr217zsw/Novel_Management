<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 审核操作日志表
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('novel_id')->nullable()->index()->comment('书籍ID');
            $table->unsignedBigInteger('chapter_id')->nullable()->index()->comment('章节ID');
            $table->string('action', 20)->comment('pass/reject');
            $table->string('remark')->nullable()->comment('备注');
            $table->unsignedBigInteger('auditor_id')->nullable()->comment('审核人');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
