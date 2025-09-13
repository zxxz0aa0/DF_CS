<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage roles');
    }

    /**
     * 顯示角色列表
     */
    public function index(): Response
    {
        $roles = Role::withCount(['users', 'permissions'])
            ->with('permissions:id,name')
            ->get();

        $permissions = Permission::all();

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
            'permissions' => $permissions,
            'totalUsers' => \App\Models\User::count(),
            'totalPermissions' => $permissions->count(),
        ]);
    }

    /**
     * 顯示角色編輯頁面
     */
    public function edit(Role $role): Response
    {
        $role->load('users:id,name,email');

        return Inertia::render('Admin/Roles/Edit', [
            'role' => $role,
        ]);
    }

    /**
     * 更新角色資訊
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($role->id),
            ],
        ]);

        // 更新角色名稱（不再處理權限）
        $role->update([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', "角色「{$role->name}」已成功更新。權限管理請前往職務管理頁面設定。");
    }

    /**
     * 建立新角色
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
        ]);

        $role = Role::create([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', "角色「{$role->name}」已成功建立。請前往職務管理頁面為此角色建立職務並分配權限。");
    }

    /**
     * 刪除角色
     */
    public function destroy(Role $role): RedirectResponse
    {
        // 檢查是否為系統預設角色
        if (in_array($role->name, ['admin', 'user'])) {
            return redirect()->route('admin.roles.index')
                ->with('error', '無法刪除系統預設角色。');
        }

        // 檢查是否有使用者使用此角色
        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', "角色「{$role->name}」仍有使用者使用，無法刪除。");
        }

        $roleName = $role->name;
        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', "角色「{$roleName}」已成功刪除。");
    }

    /**
     * 權限管理頁面
     */
    public function permissions(): Response
    {
        $permissions = Permission::withCount('roles')->get();

        // 按模組分組權限
        $groupedPermissions = $permissions->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);

            return end($parts);
        });

        return Inertia::render('Admin/Permissions/Index', [
            'permissions' => $permissions,
            'groupedPermissions' => $groupedPermissions,
            'totalRoles' => Role::count(),
        ]);
    }

    /**
     * 建立新權限
     */
    public function storePermission(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
        ]);

        Permission::create([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.permissions.index')
            ->with('success', "權限「{$request->name}」已成功建立。");
    }

    /**
     * 刪除權限
     */
    public function destroyPermission(Permission $permission): RedirectResponse
    {
        // 檢查是否有角色使用此權限
        if ($permission->roles()->count() > 0) {
            return redirect()->route('admin.permissions.index')
                ->with('error', "權限「{$permission->name}」仍有角色使用，無法刪除。");
        }

        $permissionName = $permission->name;
        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', "權限「{$permissionName}」已成功刪除。");
    }

    /**
     * 批量同步角色權限 (API)
     */
    public function syncRolePermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->syncPermissions($request->permissions ?? []);

        return response()->json([
            'message' => '權限已成功更新',
            'role' => $role->fresh(['permissions']),
        ]);
    }

    /**
     * 獲取角色統計資料 (API)
     */
    public function getRoleStats()
    {
        $roles = Role::withCount(['users', 'permissions'])->get();

        $stats = [
            'totalRoles' => $roles->count(),
            'totalUsers' => \App\Models\User::count(),
            'totalPermissions' => Permission::count(),
            'roleDistribution' => $roles->map(function ($role) {
                return [
                    'name' => $role->name,
                    'users_count' => $role->users_count,
                    'permissions_count' => $role->permissions_count,
                ];
            }),
        ];

        return response()->json($stats);
    }
}
