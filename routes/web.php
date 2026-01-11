<?php

use App\Http\Controllers\Admin\AccountDetailController;
use App\Http\Controllers\Admin\AccountingRecordController;
use App\Http\Controllers\Admin\AccountMainCategoryController;
use App\Http\Controllers\Admin\AccountSubCategoryController;
use App\Http\Controllers\Admin\CollectionController;
use App\Http\Controllers\Admin\CompanyCategoryController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\DriverVehicleAssignmentController;
use App\Http\Controllers\Admin\ExpensePaymentController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\QuickSearchController;
use App\Http\Controllers\Admin\RecurringCostController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\Admin\VehicleLicenseController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\Report\DailyTransactionReportController;
use App\Http\Controllers\Admin\Report\ReportConfigurationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    // 如果用戶已登入，根據角色跳轉；未登入則跳轉到登入頁面
    if (Auth::check()) {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        // 檢查是否有 admin 權限，有的話跳到快速搜尋
        if ($user && ($user->hasRole('admin') || $user->can('view admin dashboard'))) {
            return redirect()->route('admin.quick-search.index');
        }
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    /** @var \App\Models\User $user */
    $user = Auth::user();
    // 如果是 admin，重定向到快速搜尋
    if ($user && ($user->hasRole('admin') || $user->can('view admin dashboard'))) {
        return redirect()->route('admin.quick-search.index');
    }
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

    // 文件管理路由
    Route::resource('documents', DocumentController::class)->middleware('permission:view documents');
    Route::delete('document-files/{file}', [DocumentController::class, 'deleteFile'])
        ->name('documents.files.delete')
        ->middleware('permission:delete documents');
    Route::get('document-files/{file}/download', [DocumentController::class, 'downloadFile'])
        ->name('documents.files.download')
        ->middleware('permission:download documents');

    // 會計科目管理路由
    Route::prefix('accounts')->name('accounts.')->middleware('permission:view accounts')->group(function () {

        // 會計總類管理
        Route::put('main-categories/{mainCategory}/toggle-status', [AccountMainCategoryController::class, 'toggleStatus'])
            ->name('main-categories.toggle-status')
            ->middleware('permission:edit accounts');
        Route::get('main-categories/export', [AccountMainCategoryController::class, 'export'])
            ->name('main-categories.export')
            ->middleware('permission:export accounts');
        Route::resource('main-categories', AccountMainCategoryController::class);

        // 會計子分類管理
        Route::put('sub-categories/{subCategory}/toggle-status', [AccountSubCategoryController::class, 'toggleStatus'])
            ->name('sub-categories.toggle-status')
            ->middleware('permission:edit accounts');
        Route::get('sub-categories/export', [AccountSubCategoryController::class, 'export'])
            ->name('sub-categories.export')
            ->middleware('permission:export accounts');
        Route::get('sub-categories/import', [AccountSubCategoryController::class, 'import'])
            ->name('sub-categories.import')
            ->middleware('permission:import accounts');
        Route::post('sub-categories/import', [AccountSubCategoryController::class, 'importStore'])
            ->name('sub-categories.import.store')
            ->middleware('permission:import accounts');
        Route::get('sub-categories/template', [AccountSubCategoryController::class, 'template'])
            ->name('sub-categories.template')
            ->middleware('permission:export accounts');
        Route::resource('sub-categories', AccountSubCategoryController::class);

        // 會計科目明細管理 (靜態路由要放在 resource 之前)
        Route::get('details/export', [AccountDetailController::class, 'export'])
            ->name('account.details.export')
            ->middleware('permission:export accounts');

        Route::get('details/import', [AccountDetailController::class, 'importForm'])
            ->name('account.details.import')
            ->middleware('permission:import accounts');

        Route::post('details/import', [AccountDetailController::class, 'import'])
            ->name('account.details.import.store')
            ->middleware('permission:import accounts');

        Route::get('details/template', [AccountDetailController::class, 'template'])
            ->name('account.details.template')
            ->middleware('permission:export accounts');

        Route::put('details/{accountDetail}/toggle-status', [AccountDetailController::class, 'toggleStatus'])
            ->name('details.toggle-status')
            ->middleware('permission:edit accounts');

        // 會計科目明細 CRUD 路由
        Route::get('details', [AccountDetailController::class, 'index'])
            ->name('account.details.index');

        Route::get('details/create', [AccountDetailController::class, 'create'])
            ->name('account.details.create')
            ->middleware('permission:create accounts');

        Route::post('details', [AccountDetailController::class, 'store'])
            ->name('account.details.store')
            ->middleware('permission:create accounts');

        Route::get('details/{detail}', [AccountDetailController::class, 'show'])
            ->name('account.details.show');

        Route::get('details/{detail}/edit', [AccountDetailController::class, 'edit'])
            ->name('account.details.edit')
            ->middleware('permission:edit accounts');

        Route::put('details/{detail}', [AccountDetailController::class, 'update'])
            ->name('account.details.update')
            ->middleware('permission:edit accounts');

        Route::delete('details/{detail}', [AccountDetailController::class, 'destroy'])
            ->name('account.details.destroy')
            ->middleware('permission:delete accounts');

        // API 路由
        Route::prefix('api')->name('api.')->group(function () {
            Route::get('sub-categories/by-main/{mainId}', [AccountDetailController::class, 'getSubCategories'])
                ->name('sub-categories.by-main');

            Route::get('details/validate-code', [AccountDetailController::class, 'validateCode'])
                ->name('details.validate-code')
                ->middleware('permission:create accounts|edit accounts');

            Route::get('details/next-code', [AccountDetailController::class, 'getNextCode'])
                ->name('details.next-code')
                ->middleware('permission:create accounts|edit accounts');
        });
    });

    // 帳務管理路由
    Route::prefix('accounting')->name('accounting.')->middleware('permission:view accounting')->group(function () {

        // 主頁面（搜尋、列表、新增）
        Route::get('records', [AccountingRecordController::class, 'index'])
            ->name('records.index');

        // 批次新增
        Route::post('records', [AccountingRecordController::class, 'store'])
            ->name('records.store')
            ->middleware('permission:create accounting');

        // 單筆更新
        Route::put('records/{accountingRecord}', [AccountingRecordController::class, 'update'])
            ->name('records.update')
            ->middleware('permission:edit accounting');

        // 批次刪除
        Route::delete('records/batch', [AccountingRecordController::class, 'batchDestroy'])
            ->name('records.batch-destroy')
            ->middleware('permission:delete accounting');

        // API 路由
        Route::prefix('api')->name('api.')->group(function () {
            // 搜尋會計科目
            Route::get('account-details/search', [AccountingRecordController::class, 'searchAccountDetails'])
                ->name('account-details.search');

            // 取得經常性費用組合詳細資料（帳務頁使用）
            Route::get('recurring-costs/{recurringCost}', [RecurringCostController::class, 'show'])
                ->name('recurring-costs.show');
        });
    });

    // 支出款項管理路由
    Route::prefix('expense-payments')->name('expense-payments.')
        ->middleware('permission:view expense payments')
        ->group(function () {
            Route::get('/', [ExpensePaymentController::class, 'index'])->name('index');
            Route::post('/', [ExpensePaymentController::class, 'store'])->name('store');
            Route::put('{expense_payment}', [ExpensePaymentController::class, 'update'])->name('update');
            Route::delete('{expense_payment}', [ExpensePaymentController::class, 'destroy'])->name('destroy');

            Route::post('bulk-status', [ExpensePaymentController::class, 'bulkStatus'])->name('bulk-status');
            Route::get('export', [ExpensePaymentController::class, 'export'])->name('export');
            Route::get('print', [ExpensePaymentController::class, 'print'])->name('print');
            Route::get('template', [ExpensePaymentController::class, 'template'])->name('template');
            Route::post('import', [ExpensePaymentController::class, 'import'])->name('import');
        });

    // 催帳管理路由
    Route::prefix('collections')->name('collections.')
        ->middleware('permission:view collections')
        ->group(function () {
            Route::get('/', [CollectionController::class, 'index'])->name('index');
            Route::get('/{driver}', [CollectionController::class, 'show'])->name('show');
        });

    // 快速搜尋路由
    Route::prefix('quick-search')->name('quick-search.')->middleware('permission:view quick search')->group(function () {
        Route::get('/', [QuickSearchController::class, 'index'])->name('index');
        Route::get('/driver/{driver}', [QuickSearchController::class, 'showDriver'])->name('driver');
        Route::get('/vehicle/{vehicle}', [QuickSearchController::class, 'showVehicle'])->name('vehicle');
    });

    // 經常性費用管理路由
    Route::prefix('recurring-costs')->name('recurring-costs.')->middleware('permission:view recurring costs')->group(function () {
        Route::get('/', [RecurringCostController::class, 'index'])->name('index');
        Route::get('/create', [RecurringCostController::class, 'create'])->name('create')->middleware('permission:create recurring costs');
        Route::post('/', [RecurringCostController::class, 'store'])->name('store')->middleware('permission:create recurring costs');
        Route::get('/{recurringCost}', [RecurringCostController::class, 'show'])->name('show');
        Route::get('/{recurringCost}/edit', [RecurringCostController::class, 'edit'])->name('edit')->middleware('permission:edit recurring costs');
        Route::put('/{recurringCost}', [RecurringCostController::class, 'update'])->name('update')->middleware('permission:edit recurring costs');
        Route::delete('/{recurringCost}', [RecurringCostController::class, 'destroy'])->name('destroy')->middleware('permission:delete recurring costs');
        Route::post('/batch-apply', [RecurringCostController::class, 'batchApply'])->name('batch-apply')->middleware('permission:edit recurring costs');
    });

    // 報表管理路由
    Route::prefix('reports')->name('reports.')->middleware('permission:view reports')->group(function () {

        // 每日交易明細報表
        Route::get('/daily-transaction', [DailyTransactionReportController::class, 'index'])
            ->name('daily-transaction.index');

        Route::get('/daily-transaction/preview', [DailyTransactionReportController::class, 'preview'])
            ->name('daily-transaction.preview');

        Route::get('/daily-transaction/export', [DailyTransactionReportController::class, 'export'])
            ->name('daily-transaction.export')
            ->middleware('permission:export reports');

        // 報表組合管理
        Route::post('/configurations', [ReportConfigurationController::class, 'store'])
            ->name('configurations.store')
            ->middleware('permission:create report configurations');

        Route::put('/configurations/{reportConfiguration}', [ReportConfigurationController::class, 'update'])
            ->name('configurations.update')
            ->middleware('permission:edit report configurations');

        Route::delete('/configurations/{reportConfiguration}', [ReportConfigurationController::class, 'destroy'])
            ->name('configurations.destroy')
            ->middleware('permission:delete report configurations');
    });
});

require __DIR__.'/auth.php';
