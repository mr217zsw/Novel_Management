<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 权限体系表：部门 / 角色 / 权限 / 用户角色关联 / 角色权限关联
 */
return new class extends Migration
{
    public function up(): void
    {
        // 部门表（支持多级部门）
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->comment('部门名称');
            $table->unsignedBigInteger('parent_id')->default(0)->comment('父部门ID');
            $table->tinyInteger('sort')->default(0)->comment('排序');
            $table->timestamps();
        });

        // 角色表
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->comment('角色名称');
            $table->string('code', 50)->unique()->comment('角色编码');
            $table->unsignedBigInteger('department_id')->nullable()->comment('所属部门');
            $table->string('description')->nullable()->comment('描述');
            $table->timestamps();
        });

        // 权限表
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->comment('权限名称');
            $table->string('code', 50)->unique()->comment('权限码 novel.create');
            $table->string('resource', 50)->comment('资源 novel');
            $table->string('action', 50)->comment('动作 create');
            $table->timestamps();
        });

        // 用户-角色关联表
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->unsignedBigInteger('role_id')->comment('角色ID');
            $table->timestamps();

            $table->unique(['user_id', 'role_id']);
            $table->index('role_id');
        });

        // 角色-权限关联表
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id')->comment('角色ID');
            $table->unsignedBigInteger('permission_id')->comment('权限ID');
            $table->timestamps();

            $table->unique(['role_id', 'permission_id']);
            $table->index('permission_id');
        });

        // 数据范围：用户-部门关联（用于部门数据隔离）
        Schema::create('user_departments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->unsignedBigInteger('department_id')->comment('部门ID');
            $table->timestamps();

            $table->unique(['user_id', 'department_id']);
            $table->index('department_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_departments');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('departments');
    }
};
