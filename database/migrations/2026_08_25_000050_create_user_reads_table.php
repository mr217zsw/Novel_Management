<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 用户阅读记录表
 *
 * 用于书架进度、阅读统计、阅读时长分析。
 * read_duration 秒，progress 百分比。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_reads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index()->comment('用户ID');
            $table->unsignedBigInteger('novel_id')->index()->comment('小说ID');
            $table->unsignedBigInteger('chapter_id')->nullable()->comment('章节ID');
            $table->integer('read_duration')->default(0)->comment('阅读时长(秒)');
            $table->decimal('progress', 5, 2)->default(0)->comment('进度(百分比)');
            $table->string('device_type', 20)->nullable()->comment('设备类型');
            $table->string('ip', 45)->nullable()->comment('IP');
            $table->timestamp('read_at')->nullable()->comment('最近阅读时间');
            $table->timestamps();

            $table->unique(['user_id', 'novel_id']);
            $table->index(['user_id', 'read_at'], 'idx_user_read_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_reads');
    }
};
