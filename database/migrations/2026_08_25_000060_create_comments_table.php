<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 评论表
 *
 * 支持章评/书评，parent_id 支持楼中楼回复。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index()->comment('用户ID');
            $table->unsignedBigInteger('novel_id')->index()->comment('小说ID');
            $table->unsignedBigInteger('chapter_id')->nullable()->index()->comment('章节ID');
            $table->unsignedBigInteger('parent_id')->default(0)->comment('父评论ID');
            $table->text('content')->comment('评论内容');
            $table->integer('like_count')->default(0)->comment('点赞数');
            $table->tinyInteger('status')->default(1)->comment('0隐藏 1正常 2删除');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
