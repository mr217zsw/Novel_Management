<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * RBAC 权限系统表
 *
 * departments（部门）+ roles（角色）+ permissions（权限）+ 关联表。
 * 支持部门隔离 + 角色权限分配。
 */
return new class extends Migration
{
    public function up(): void
    {
        // 部门表
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->comment('部门名称');
            $table->unsignedBigInteger('parent_id')->default(0)->index()->comment('父部门ID');
            $table->integer('sort')->default(0)->comment('排序');
            $table->timestamps();
        });

        // 角色表
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->comment('角色名称');
            $table->string('code', 50)->unique()->comment('角色编码');
            $table->unsignedBigInteger('department_id')->nullable()->index()->comment('所属部门');
            $table->string('description')->nullable()->comment('描述');
            $table->timestamps();
        });

        // 权限表
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->comment('权限名称');
            $table->string('code', 50)->unique()->comment('权限编码 novel.create');
            $table->string('resource', 50)->comment('资源');
            $table->string('action', 50)->comment('动作');
            $table->timestamps();
        });

        // 用户-角色 关联表
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index()->comment('用户ID');
            $table->unsignedBigInteger('role_id')->index()->comment('角色ID');
            $table->timestamps();

            $table->unique(['user_id', 'role_id']);
        });

        // 角色-权限 关联表
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id')->index()->comment('角色ID');
            $table->unsignedBigInteger('permission_id')->index()->comment('权限ID');
            $table->timestamps();

            $table->unique(['role_id', 'permission_id']);
        });

        // 用户-部门 关联表
        Schema::create('user_departments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index()->comment('用户ID');
            $table->unsignedBigInteger('department_id')->index()->comment('部门ID');
            $table->timestamps();

            $table->unique(['user_id', 'department_id']);
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
