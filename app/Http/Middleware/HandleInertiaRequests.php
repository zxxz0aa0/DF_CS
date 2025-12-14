<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                // 供前端根據角色/權限決定側邊欄可見性
                'roles' => fn () => $request->user()?->getRoleNames() ?? [],
                // 修正權限取得邏輯：同時包含職務權限和角色權限
                'permissions' => function () use ($request) {
                    $user = $request->user();
                    if (!$user) {
                        return [];
                    }
                    
                    // 收集所有權限名稱
                    $permissions = collect();
                    
                    // 1. 從職務獲取權限（主要權限來源）
                    if ($user->position_id && $user->position) {
                        $positionPermissions = $user->position->permissions ?? collect();
                        $permissions = $permissions->merge($positionPermissions->pluck('name'));
                    }
                    
                    // 2. 從角色獲取權限（向後相容）
                    $rolePermissions = $user->getPermissionsViaRoles();
                    $permissions = $permissions->merge($rolePermissions->pluck('name'));
                    
                    // 3. 直接分配給用戶的權限
                    $directPermissions = $user->getDirectPermissions();
                    $permissions = $permissions->merge($directPermissions->pluck('name'));
                    
                    return $permissions->unique()->values()->all();
                },
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'importFailures' => fn () => $request->session()->get('importFailures', []),
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
