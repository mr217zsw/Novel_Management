<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 章节表 - 核心：内容存 OSS
 *
 * content_oss_key 指向 OSS 存储路径，MySQL 不存大文本。
 * content_cdn_url 为 CDN 加速地址，前端直接拉取。
 * is_free: 0付费 1免费
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('novel_id')->index()->comment('所属小说ID');
            $table->integer('chapter_no')->default(1)->comment('章节序号');
            $table->string('title', 100)->comment('章节标题');
            $table->string('content_oss_key', 255)->nullable()->comment('OSS存储路径');
            $table->string('content_cdn_url', 255)->nullable()->comment('CDN加速地址');
            $table->integer('word_count')->default(0)->comment('字数');
            $table->tinyInteger('is_free')->default(0)->comment('0付费 1免费');
            $table->decimal('price', 10, 2)->default(0)->comment('解锁价格(分)');
            $table->tinyInteger('status')->default(0)->comment('0草稿 1已发布');
            $table->tinyInteger('audit_status')->default(0)->comment('0待审 1通过 2驳回');
            $table->string('audit_remark')->nullable()->comment('审核备注');
            $table->timestamp('audit_time')->nullable()->comment('审核时间');
            $table->unsignedBigInteger('auditor_id')->nullable()->comment('审核人');
            $table->timestamps();

            $table->unique(['novel_id', 'chapter_no'], 'uniq_novel_chapter');
            $table->index('content_oss_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
