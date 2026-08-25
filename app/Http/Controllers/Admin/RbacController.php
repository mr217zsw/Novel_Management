<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

/**
 * RBAC 权限管理（超级管理员）
 */
class RbacController extends Controller
{
    // ===== 部门管理 =====

    public function departments()
    {
        return response()->success(Department::with('children')->orderBy('sort')->get());
    }

    public function storeDepartment(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'parent_id' => 'nullable|integer',
            'sort' => 'nullable|integer',
        ]);

        $department = Department::create($validated);

        return response()->success($department, '部门创建成功');
    }

    public function updateDepartment(Request $request, int $id)
    {
        $department = Department::findOrFail($id);
        $department->update($request->validate([
            'name' => 'sometimes|string|max:50',
            'parent_id' => 'nullable|integer',
            'sort' => 'nullable|integer',
        ]));

        return response()->success($department, '部门已更新');
    }

    // ===== 角色管理 =====

    public function roles()
    {
        return response()->success(Role::with('department:id,name')->get());
    }

    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => 'required|string|max:50|unique:roles,code',
            'department_id' => 'nullable|exists:departments,id',
            'description' => 'nullable|string',
        ]);

        $role = Role::create($validated);

        return response()->success($role, '角色创建成功');
    }

    public function updateRole(Request $request, int $id)
    {
        $role = Role::findOrFail($id);
        $role->update($request->validate([
            'name' => 'sometimes|string|max:50',
            'department_id' => 'nullable|exists:departments,id',
            'description' => 'nullable|string',
        ]));

        return response()->success($role, '角色已更新');
    }

    // ===== 权限管理 =====

    public function permissions()
    {
        return response()->success(Permission::orderBy('resource')->get());
    }

    public function storePermission(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => 'required|string|max:50|unique:permissions,code',
            'resource' => 'required|string|max:50',
            'action' => 'required|string|max:50',
        ]);

        $permission = Permission::create($validated);

        return response()->success($permission, '权限创建成功');
    }

    // ===== 角色授权 =====

    /**
     * 设置角色权限
     *
     * POST /api/admin/roles/{id}/permissions
     * body: { permission_ids: [1,2,3] }
     */
    public function setRolePermissions(Request $request, int $id)
    {
        $validated = $request->validate([
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $role = Role::findOrFail($id);
        $role->permissions()->sync($validated['permission_ids']);

        // 清空相关用户权限缓存
        $this->clearRoleUserCache($role);

        return response()->success(null, '角色权限已更新');
    }

    /**
     * 获取角色权限
     */
    public function getRolePermissions(int $id)
    {
        $role = Role::findOrFail($id);
        return response()->success($role->permissions()->pluck('permissions.id'));
    }

    // ===== 用户角色 =====

    /**
     * 设置用户角色
     *
     * POST /api/admin/users/{id}/roles
     * body: { role_ids: [1,2] }
     */
    public function setUserRoles(Request $request, int $id)
    {
        $validated = $request->validate([
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $user = User::findOrFail($id);
        $user->roles()->sync($validated['role_ids']);

        Redis::del("user:permissions:{$id}");

        return response()->success(null, '用户角色已更新');
    }

    /**
     * 清除角色下所有用户的权限缓存
     */
    protected function clearRoleUserCache(Role $role): void
    {
        $userIds = DB::table('user_roles')->where('role_id', $role->id)->pluck('user_id');
        foreach ($userIds as $userId) {
            Redis::del("user:permissions:{$userId}");
        }
    }
}
