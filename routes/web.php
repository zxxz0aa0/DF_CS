<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\RolePermissionController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 管理員路由
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserManagementController::class);
    
    // 角色權限管理路由
    Route::get('/roles', [RolePermissionController::class, 'index'])->name('roles.index');
    Route::post('/roles', [RolePermissionController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}/edit', [RolePermissionController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{role}', [RolePermissionController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RolePermissionController::class, 'destroy'])->name('roles.destroy');
    
    // 權限管理路由
    Route::get('/permissions', [RolePermissionController::class, 'permissions'])->name('permissions.index');
    Route::post('/permissions', [RolePermissionController::class, 'storePermission'])->name('permissions.store');
    Route::delete('/permissions/{permission}', [RolePermissionController::class, 'destroyPermission'])->name('permissions.destroy');
    
    // API 路由
    Route::put('/api/roles/{role}/permissions', [RolePermissionController::class, 'syncRolePermissions'])->name('api.roles.sync-permissions');
    Route::get('/api/roles/stats', [RolePermissionController::class, 'getRoleStats'])->name('api.roles.stats');
});

require __DIR__.'/auth.php';
