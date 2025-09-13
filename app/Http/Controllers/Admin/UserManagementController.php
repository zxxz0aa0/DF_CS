<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use App\Rules\TaiwanIdNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;

class UserManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view users')->only(['index', 'show']);
        $this->middleware('permission:create users')->only(['create', 'store']);
        $this->middleware('permission:edit users')->only(['edit', 'update']);
        $this->middleware('permission:delete users')->only(['destroy']);
    }

    public function index(Request $request): Response
    {
        $users = User::with('roles')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): Response
    {
        $roles = Role::with(['positions' => function ($query) {
            $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
        }])->get();

        // 常見部門選項
        $departmentOptions = [
            'IT部門', '人事部門', '財務部門', '行政部門', '業務部門',
            '客服部門', '行銷部門', '研發部門', '生產部門', '其他',
        ];

        return Inertia::render('Admin/Users/Create', [
            'roles' => $roles,
            'departmentOptions' => $departmentOptions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'id_number' => ['required', new TaiwanIdNumber],
            'birth_date' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
            'mobile_phone' => ['required', 'regex:/^09\d{8}$/'],
            'home_phone' => ['nullable', 'regex:/^0[2-8]\d{7,8}$/'],
            'address' => 'nullable|string|max:500',
            'department' => 'required|string|max:100',
            'position' => 'required|string|max:100',
            'position_id' => 'required|exists:positions,id',
            'emergency_contact' => 'required|string|max:100',
            'emergency_phone' => ['required', 'regex:/^09\d{8}$/'],
            'roles' => 'array',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'id_number' => $request->id_number,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
            'mobile_phone' => $request->mobile_phone,
            'home_phone' => $request->home_phone,
            'address' => $request->address,
            'department' => $request->department,
            'position' => $request->position,
            'position_id' => $request->position_id, // 職務關聯
            'emergency_contact' => $request->emergency_contact,
            'emergency_phone' => $request->emergency_phone,
        ]);

        // 依職務自動分配對應角色
        if ($request->position_id) {
            $position = Position::find($request->position_id);
            if ($position && $position->role_id) {
                $role = Role::find($position->role_id);
                if ($role) {
                    $user->assignRole($role);
                }
            }
        }

        // 如果有額外指定角色，則追加分配
        if ($request->roles) {
            $user->assignRole($request->roles);
        }

        return redirect()->route('admin.users.index')
            ->with('success', '使用者建立成功。');
    }

    public function show(User $user): Response
    {
        $user->load('roles.permissions');

        // 為了安全考量，將身分證字號部分遮蔽（不再解密，直接使用原值）
        $userData = $user->toArray();
        if ($user->id_number) {
            $raw = $user->id_number;
            $userData['id_number_display'] = substr($raw, 0, 1).'***-***'.substr($raw, -3);
            $userData['id_number_full'] = $raw;
        } else {
            $userData['id_number_display'] = '未提供';
            $userData['id_number_full'] = null;
        }

        return Inertia::render('Admin/Users/Show', [
            'user' => $userData,
        ]);
    }

    public function edit(User $user): Response
    {
        $roles = Role::with(['positions' => function ($query) {
            $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
        }])->get();
        $permissions = Permission::all();
        $user->load('roles');

        // 常見部門選項
        $departmentOptions = [
            'IT部門', '人事部門', '財務部門', '行政部門', '業務部門',
            '客服部門', '行銷部門', '研發部門', '生產部門', '其他',
        ];

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'roles' => $roles,
            'permissions' => $permissions,
            'departmentOptions' => $departmentOptions,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username,'.$user->id,
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'id_number' => ['required', new TaiwanIdNumber],
            'birth_date' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
            'mobile_phone' => ['required', 'regex:/^09\d{8}$/'],
            'home_phone' => ['nullable', 'regex:/^0[2-8]\d{7,8}$/'],
            'address' => 'nullable|string|max:500',
            'department' => 'required|string|max:100',
            'position' => 'required|string|max:100',
            'position_id' => 'required|exists:positions,id',
            'emergency_contact' => 'required|string|max:100',
            'emergency_phone' => ['required', 'regex:/^09\d{8}$/'],
            'roles' => 'array',
        ]);

        $updateData = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'id_number' => $request->id_number,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
            'mobile_phone' => $request->mobile_phone,
            'home_phone' => $request->home_phone,
            'address' => $request->address,
            'department' => $request->department,
            'position' => $request->position,
            'position_id' => $request->position_id, // 職務關聯
            'emergency_contact' => $request->emergency_contact,
            'emergency_phone' => $request->emergency_phone,
        ];

        if ($request->password) {
            $updateData['password'] = Hash::make($request->password);
        }

        // 先留存原職務以便比對
        $originalPositionId = $user->position_id;

        $user->update($updateData);

        // 以目前角色為基底，若有傳入 roles 則以傳入為主
        $desiredRoles = $user->roles()->pluck('name')->toArray();
        if ($request->has('roles')) {
            $desiredRoles = array_values(array_unique($request->roles ?? []));
        }

        // 若職務有變更，整合舊/新職務角色進 desiredRoles
        $positionChanged = $originalPositionId != $request->position_id;
        if ($positionChanged && $request->position_id) {
            $newPosition = Position::find($request->position_id);
            $oldPosition = $originalPositionId ? Position::find($originalPositionId) : null;

            $newRole = ($newPosition && $newPosition->role_id) ? Role::find($newPosition->role_id) : null;
            $oldRole = ($oldPosition && $oldPosition->role_id) ? Role::find($oldPosition->role_id) : null;

            // 安全保護：避免透過職務變更把自己唯一 admin 移掉
            if ($user->id === auth()->id() && $oldRole && $oldRole->name === 'admin' && (! $newRole || $newRole->name !== 'admin')) {
                $adminRole = Role::where('name', 'admin')->first();
                $otherAdminsCount = $adminRole ? $adminRole->users()->where('users.id', '!=', $user->id)->count() : 0;
                if ($otherAdminsCount === 0) {
                    return redirect()->back()->with('error', '系統至少需要一位管理員，無法透過職務變更移除自己 admin 角色。');
                }
            }

            // 移除舊職務角色
            if ($oldRole) {
                $desiredRoles = array_values(array_diff($desiredRoles, [$oldRole->name]));
            }
            // 加入新職務角色
            if ($newRole) {
                $desiredRoles[] = $newRole->name;
            }
        }

        // 角色安全保護（針對表單有傳入 roles 或職務導致變更後）
        if ($user->id === auth()->id() && ! in_array('admin', $desiredRoles)) {
            return redirect()->back()->with('error', '無法移除自己「admin」角色，請由其他管理員調整或保留 admin 身分。');
        }
        if (! in_array('admin', $desiredRoles)) {
            $adminRole = Role::where('name', 'admin')->first();
            $otherAdminsCount = $adminRole ? $adminRole->users()->where('users.id', '!=', $user->id)->count() : 0;
            if ($otherAdminsCount === 0) {
                return redirect()->back()->with('error', '系統至少需要一位管理員，無法移除此帳號的 admin 角色。');
            }
        }

        $user->syncRoles(array_unique($desiredRoles));

        return redirect()->route('admin.users.index')
            ->with('success', '使用者更新成功。');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', '無法刪除自己的帳號。');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', '使用者刪除成功。');
    }
}
