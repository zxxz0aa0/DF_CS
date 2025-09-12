<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;

class PositionController extends Controller
{
    /**
     * 顯示職務列表
     */
    public function index(Request $request): Response
    {
        $query = Position::with(['role', 'users'])
            ->withCount(['users', 'permissions']);

        // 搜尋
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 角色篩選
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        // 狀態篩選
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $positions = $query->ordered()->paginate(15);
        $roles = Role::all();

        return Inertia::render('Admin/Positions/Index', [
            'positions' => $positions,
            'roles' => $roles,
            'filters' => $request->only(['search', 'role_id', 'is_active']),
        ]);
    }

    /**
     * 顯示新增職務表單
     */
    public function create(): Response
    {
        $roles = Role::all();
        $permissions = Permission::all();

        // 按模組分組權限
        $groupedPermissions = $permissions->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);
            return end($parts);
        });

        return Inertia::render('Admin/Positions/Create', [
            'roles' => $roles,
            'permissions' => $permissions,
            'groupedPermissions' => $groupedPermissions,
        ]);
    }

    /**
     * 儲存新職務
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:positions,code',
            'description' => 'nullable|string|max:1000',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ], [
            'name.required' => '職務名稱為必填欄位',
            'code.required' => '職務代碼為必填欄位',
            'code.unique' => '此職務代碼已存在',
            'role_id.required' => '請選擇所屬角色',
            'role_id.exists' => '選擇的角色不存在',
        ]);

        $position = Position::create([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'role_id' => $request->role_id,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->integer('sort_order', 0),
        ]);

        // 分配權限
        if ($request->permissions) {
            $position->syncPermissions($request->permissions);
        }

        return redirect()->route('admin.positions.index')
            ->with('success', "職務「{$position->name}」已成功建立。");
    }

    /**
     * 顯示職務詳情
     */
    public function show(Position $position): Response
    {
        $position->load(['role', 'users', 'permissions']);

        return Inertia::render('Admin/Positions/Show', [
            'position' => $position,
        ]);
    }

    /**
     * 顯示編輯職務表單
     */
    public function edit(Position $position): Response
    {
        $position->load(['role', 'permissions']);
        $roles = Role::all();
        $permissions = Permission::all();

        // 按模組分組權限
        $groupedPermissions = $permissions->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);
            return end($parts);
        });

        return Inertia::render('Admin/Positions/Edit', [
            'position' => $position,
            'roles' => $roles,
            'permissions' => $permissions,
            'groupedPermissions' => $groupedPermissions,
            'positionPermissions' => $position->permissions->pluck('id')->toArray(),
        ]);
    }

    /**
     * 更新職務
     */
    public function update(Request $request, Position $position): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('positions', 'code')->ignore($position->id),
            ],
            'description' => 'nullable|string|max:1000',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ], [
            'name.required' => '職務名稱為必填欄位',
            'code.required' => '職務代碼為必填欄位',
            'code.unique' => '此職務代碼已存在',
            'role_id.required' => '請選擇所屬角色',
            'role_id.exists' => '選擇的角色不存在',
        ]);

        $position->update([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'role_id' => $request->role_id,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->integer('sort_order', 0),
        ]);

        // 同步權限
        $position->syncPermissions($request->permissions ?? []);

        return redirect()->route('admin.positions.index')
            ->with('success', "職務「{$position->name}」已成功更新。");
    }

    /**
     * 刪除職務
     */
    public function destroy(Position $position): RedirectResponse
    {
        // 檢查是否有使用者使用此職務
        if ($position->users()->count() > 0) {
            return redirect()->route('admin.positions.index')
                ->with('error', "職務「{$position->name}」仍有使用者使用，無法刪除。");
        }

        $positionName = $position->name;
        $position->delete();

        return redirect()->route('admin.positions.index')
            ->with('success', "職務「{$positionName}」已成功刪除。");
    }

    /**
     * 批量同步職務權限 (API)
     */
    public function syncPermissions(Request $request, Position $position)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $position->syncPermissions($request->permissions ?? []);

        return response()->json([
            'message' => '權限已成功更新',
            'position' => $position->fresh(['permissions']),
        ]);
    }

    /**
     * 獲取職務統計資料 (API)
     */
    public function getStats()
    {
        $positions = Position::withCount(['users', 'permissions'])->get();
        
        $stats = [
            'totalPositions' => $positions->count(),
            'activePositions' => $positions->where('is_active', true)->count(),
            'totalUsers' => \App\Models\User::whereNotNull('position_id')->count(),
            'positionDistribution' => $positions->map(function ($position) {
                return [
                    'name' => $position->name,
                    'role' => $position->role?->name,
                    'users_count' => $position->users_count,
                    'permissions_count' => $position->permissions_count,
                    'is_active' => $position->is_active,
                ];
            }),
        ];

        return response()->json($stats);
    }
}