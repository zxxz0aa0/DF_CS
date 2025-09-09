<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\TaiwanIdNumber;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
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
        $roles = Role::all();
        $permissions = Permission::all();
        
        // 常見部門選項
        $departmentOptions = [
            'IT部門', '人事部門', '財務部門', '行政部門', '業務部門', 
            '客服部門', '行銷部門', '研發部門', '生產部門', '其他'
        ];
        
        return Inertia::render('Admin/Users/Create', [
            'roles' => $roles,
            'permissions' => $permissions,
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
            'emergency_contact' => $request->emergency_contact,
            'emergency_phone' => $request->emergency_phone,
        ]);

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
            $userData['id_number_display'] = substr($raw, 0, 1) . '***-***' . substr($raw, -3);
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
        $roles = Role::all();
        $permissions = Permission::all();
        $user->load('roles');
        
        // 常見部門選項
        $departmentOptions = [
            'IT部門', '人事部門', '財務部門', '行政部門', '業務部門', 
            '客服部門', '行銷部門', '研發部門', '生產部門', '其他'
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
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'id_number' => ['required', new TaiwanIdNumber],
            'birth_date' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
            'mobile_phone' => ['required', 'regex:/^09\d{8}$/'],
            'home_phone' => ['nullable', 'regex:/^0[2-8]\d{7,8}$/'],
            'address' => 'nullable|string|max:500',
            'department' => 'required|string|max:100',
            'position' => 'required|string|max:100',
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
            'emergency_contact' => $request->emergency_contact,
            'emergency_phone' => $request->emergency_phone,
        ];

        if ($request->password) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        if ($request->has('roles')) {
            $newRoles = $request->roles ?? [];

            // 保護：禁止使用者移除自己的 admin 角色，避免被立即踢出後台
            if ($user->id === auth()->id()) {
                if (!in_array('admin', $newRoles)) {
                    return redirect()->back()
                        ->with('error', '無法移除自己「admin」角色，請由其他管理員調整或保留 admin 身分。');
                }
            }

            // 保護：避免系統沒有任何 admin 使用者
            if (!in_array('admin', $newRoles)) {
                $adminRole = \Spatie\Permission\Models\Role::findByName('admin');
                $otherAdminsCount = $adminRole->users()->where('users.id', '!=', $user->id)->count();
                if ($otherAdminsCount === 0) {
                    return redirect()->back()
                        ->with('error', '系統至少需要一位管理員，無法移除此帳號的 admin 角色。');
                }
            }

            $user->syncRoles($newRoles);
        }

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
