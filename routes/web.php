<?php

use App\Http\Controllers\Admin\CompanyCategoryController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\DriverVehicleAssignmentController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\Admin\VehicleLicenseController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    // 如果用戶已登入，跳轉到儀表板；未登入則跳轉到登入頁面
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 管理員路由（改為權限導向：有管理權限即可進入）
Route::prefix('admin')->name('admin.')->middleware(['auth', 'permission:view admin dashboard'])->group(function () {
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

    // 職務管理路由
    Route::resource('positions', PositionController::class);
    Route::put('positions/{position}/sync-permissions', [PositionController::class, 'syncPermissions'])->name('positions.sync-permissions');
    Route::get('api/positions/stats', [PositionController::class, 'getStats'])->name('api.positions.stats');

    // API 路由
    Route::put('/api/roles/{role}/permissions', [RolePermissionController::class, 'syncRolePermissions'])->name('api.roles.sync-permissions');
    Route::get('/api/roles/stats', [RolePermissionController::class, 'getRoleStats'])->name('api.roles.stats');

    // 公司管理路由
    Route::resource('company-categories', CompanyCategoryController::class);
    Route::resource('companies', CompanyController::class);

    // 駕駛管理路由 (靜態路由要放在 resource 之前)
    Route::get('drivers/export', [DriverController::class, 'export'])->name('drivers.export');
    Route::post('drivers/import', [DriverController::class, 'import'])->name('drivers.import');
    Route::get('drivers/expiring-licenses', [DriverController::class, 'expiringLicenses'])->name('drivers.expiring-licenses');
    Route::put('drivers/{driver}/toggle-status', [DriverController::class, 'toggleStatus'])->name('drivers.toggle-status');
    Route::resource('drivers', DriverController::class);

    // 車輛牌照管理路由 (靜態路由要放在 resource 之前)
    Route::post('vehicle-licenses/{vehicle_license}/revoke', [VehicleLicenseController::class, 'revoke'])->name('vehicle-licenses.revoke');
    Route::post('vehicle-licenses/{vehicle_license}/replace', [VehicleLicenseController::class, 'replace'])->name('vehicle-licenses.replace');
    Route::resource('vehicle-licenses', VehicleLicenseController::class);

    // 車輛管理路由 (靜態路由要放在 resource 之前)
    Route::get('vehicles/export', [VehicleController::class, 'export'])->name('vehicles.export');
    Route::post('vehicles/import', [VehicleController::class, 'import'])->name('vehicles.import');
    Route::get('vehicles/template', [VehicleController::class, 'template'])->name('vehicles.template');
    Route::post('vehicles/{vehicle}/deregister', [VehicleController::class, 'deregister'])->name('vehicles.deregister');
    Route::post('vehicles/{vehicle}/reactivate', [VehicleController::class, 'reactivate'])->name('vehicles.reactivate');
    Route::get('vehicles/{vehicle}/audit-logs', [VehicleController::class, 'auditLogs'])->name('vehicles.audit-logs');
    Route::get('api/vehicles/replacement-licenses', [VehicleController::class, 'getReplacementLicenses'])->name('api.vehicles.replacement-licenses');
    Route::get('api/vehicles/company-owners', [VehicleController::class, 'getCompanyOwners'])->name('api.vehicles.company-owners');
    Route::resource('vehicles', VehicleController::class);

    // 廠商管理路由
    Route::resource('vendors', VendorController::class);

    // 駕駛車輛綁定管理路由
    Route::resource('driver-vehicle-assignments', DriverVehicleAssignmentController::class);
});

require __DIR__.'/auth.php';
