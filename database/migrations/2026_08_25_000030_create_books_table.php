<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 书籍表
 *
 * copyright_type: 1买断 2分成 3独家 4非独家
 * status: 0草稿 1待审 2已上架 3已下架
 * audit_status: 0待审 1通过 2驳回
 * cover_url 存 OSS/CDN。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('author_id')->nullable()->index()->comment('作者ID');
            $table->string('title', 100)->comment('小说标题');
            $table->string('cover_url', 255)->nullable()->comment('封面图(OSS)');
            $table->text('description')->nullable()->comment('简介');
            $table->unsignedBigInteger('category_id')->nullable()->index()->comment('分类ID');
            $table->json('tags')->nullable()->comment('标签数组');
            $table->tinyInteger('copyright_type')->default(1)->comment('1买断 2分成 3独家 4非独家');
            $table->decimal('copyright_price', 10, 2)->default(0)->comment('版权采购价格');
            $table->date('contract_start')->nullable()->comment('合同开始');
            $table->date('contract_end')->nullable()->comment('合同到期');
            $table->decimal('royalty_rate', 5, 2)->default(0)->comment('分成比例(分成模式)');
            $table->integer('total_chapters')->default(0)->comment('总章节数');
            $table->integer('total_words')->default(0)->comment('总字数');
            $table->integer('total_views')->default(0)->comment('阅读量');
            $table->integer('total_likes')->default(0)->comment('点赞量');
            $table->integer('total_favorites')->default(0)->comment('收藏量');
            $table->decimal('min_price', 10, 2)->default(0)->comment('最低付费章节价格');
            $table->tinyInteger('status')->default(0)->comment('0草稿 1待审 2已上架 3已下架');
            $table->tinyInteger('audit_status')->default(0)->comment('0待审 1通过 2驳回');
            $table->string('audit_remark')->nullable()->comment('审核备注');
            $table->timestamp('published_at')->nullable()->index()->comment('上架时间');
            $table->timestamps();

            // 联合索引
            $table->index(['author_id', 'status', 'published_at'], 'idx_author_status_published');
            $table->index(['category_id', 'status', 'total_views'], 'idx_category_status_views');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
