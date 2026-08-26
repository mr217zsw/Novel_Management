<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 权限体系初始化
 *
 * 创建基础部门、角色和权限码，并分配超级管理员角色。
 */
class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 部门
        $departments = [
            ['name' => '管理层', 'parent_id' => 0, 'sort' => 1],
            ['name' => '投流部', 'parent_id' => 0, 'sort' => 2],
            ['name' => '内容部', 'parent_id' => 0, 'sort' => 3],
            ['name' => '财务部', 'parent_id' => 0, 'sort' => 4],
            ['name' => '运营部', 'parent_id' => 0, 'sort' => 5],
        ];
        foreach ($departments as $d) {
            Department::firstOrCreate(['name' => $d['name']], $d);
        }

        // 权限码定义（resource.action）
        $permissionDefs = [
            // 渠道
            ['渠道查看', 'channel.view', 'channel', 'view'],
            ['渠道创建', 'channel.create', 'channel', 'create'],
            ['渠道编辑', 'channel.update', 'channel', 'update'],
            ['渠道删除', 'channel.delete', 'channel', 'delete'],
            // 投放计划
            ['计划查看', 'campaign.view', 'campaign', 'view'],
            ['计划创建', 'campaign.create', 'campaign', 'create'],
            ['计划编辑', 'campaign.update', 'campaign', 'update'],
            ['计划删除', 'campaign.delete', 'campaign', 'delete'],
            // 素材
            ['素材查看', 'material.view', 'material', 'view'],
            ['素材创建', 'material.create', 'material', 'create'],
            ['素材审核', 'material.audit', 'material', 'audit'],
            ['素材删除', 'material.delete', 'material', 'delete'],
            // 书籍
            ['书籍查看', 'novel.view', 'novel', 'view'],
            ['书籍创建', 'novel.create', 'novel', 'create'],
            ['书籍编辑', 'novel.update', 'novel', 'update'],
            ['书籍删除', 'novel.delete', 'novel', 'delete'],
            ['书籍审核', 'novel.audit', 'novel', 'audit'],
            // 章节
            ['章节创建', 'chapter.create', 'chapter', 'create'],
            ['章节审核', 'chapter.audit', 'chapter', 'audit'],
            // 版权
            ['版权登记', 'copyright.create', 'copyright', 'create'],
            ['版权付款', 'copyright.pay', 'copyright', 'pay'],
            // 作者
            ['作者创建', 'author.create', 'author', 'create'],
            ['作者编辑', 'author.update', 'author', 'update'],
            // 订单
            ['订单查看', 'order.view', 'order', 'view'],
            ['订单退款', 'order.refund', 'order', 'refund'],
            // 数据分析
            ['数据看板', 'analytics.view', 'analytics', 'view'],
        ];

        $permissionIds = [];
        foreach ($permissionDefs as [$name, $code, $resource, $action]) {
            $permission = Permission::firstOrCreate(
                ['code' => $code],
                ['name' => $name, 'resource' => $resource, 'action' => $action]
            );
            $permissionIds[] = $permission->id;
        }

        // 角色
        $roles = [
            ['超级管理员', 'super_admin', '管理层', '拥有所有权限'],
            ['投放专员', 'ad_specialist', '投流部', '渠道/计划/素材管理'],
            ['投放经理', 'ad_manager', '投流部', '投放审批/ROI分析'],
            ['版权经理', 'copyright_manager', '内容部', '版权采购/合同'],
            ['编辑', 'editor', '内容部', '书籍/章节管理'],
            ['财务', 'finance', '财务部', '订单/对账/结算'],
            ['运营', 'operator', '运营部', '用户运营/活动/数据'],
        ];

        $superAdminRole = null;
        foreach ($roles as [$name, $code, $deptName, $desc]) {
            $dept = Department::where('name', $deptName)->first();
            $role = Role::firstOrCreate(
                ['code' => $code],
                ['name' => $name, 'department_id' => $dept?->id, 'description' => $desc]
            );

            if ($code === 'super_admin') {
                $superAdminRole = $role;
                // 超管绑定所有权限
                $role->permissions()->sync($permissionIds);
            } elseif (in_array($code, ['ad_specialist', 'ad_manager'])) {
                // 投放相关权限
                $role->permissions()->sync(
                    Permission::whereIn('resource', ['channel', 'campaign', 'material'])
                        ->pluck('id')
                );
            } elseif ($code === 'finance') {
                $role->permissions()->sync(
                    Permission::where('resource', 'order')->pluck('id')
                );
            }
        }

        // 绑定默认管理员账号（由 AdminUserSeeder 创建后绑定）
        $admin = \App\Models\User::where('phone', '13800000000')->first();
        if ($admin && $superAdminRole) {
            DB::table('user_roles')->updateOrInsert(
                ['user_id' => $admin->id, 'role_id' => $superAdminRole->id],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
