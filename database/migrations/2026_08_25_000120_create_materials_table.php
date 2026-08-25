<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 投放素材表 - OSS 直传存储
 *
 * 运营上传视频/图片素材，oss_key 指向 OSS，cdn_url 为加速地址。
 * type: 1图片 2视频
 * status: 0待审 1通过 2驳回 3已删除
 * ctr / conversion_rate 由统计任务自动更新。
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id')->nullable()->index()->comment('所属投放计划');
            $table->string('name', 100)->comment('素材名称');
            $table->tinyInteger('type')->default(1)->comment('1图片 2视频');
            $table->string('oss_key', 255)->comment('OSS存储路径');
            $table->string('cdn_url', 255)->nullable()->comment('CDN加速地址');
            $table->bigInteger('file_size')->default(0)->comment('文件大小(字节)');
            $table->string('mime_type', 50)->nullable()->comment('文件类型');
            $table->integer('width')->nullable()->comment('宽度(像素)');
            $table->integer('height')->nullable()->comment('高度');
            $table->integer('duration')->nullable()->comment('视频时长(秒)');
            $table->tinyInteger('status')->default(0)->comment('0待审 1通过 2驳回 3已删除');
            $table->string('audit_remark')->nullable()->comment('审核备注');
            $table->decimal('ctr', 5, 2)->default(0)->comment('点击率(自动统计)');
            $table->decimal('conversion_rate', 5, 2)->default(0)->comment('转化率');
            $table->unsignedBigInteger('created_by')->nullable()->comment('上传人');
            $table->timestamps();

            $table->index(['campaign_id', 'status'], 'idx_campaign_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
