<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VehicleStoreRequest;
use App\Http\Requests\VehicleUpdateRequest;
use App\Models\Company;
use App\Models\CompanyCategory;
use App\Models\Vehicle;
use App\Models\VehicleConfigSetting;
use App\Models\VehicleLicense;
use App\Exports\VehiclesExport;
use App\Exports\VehicleTemplateExport;
use App\Imports\VehiclesImport;
use App\Models\VehicleAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Inertia\Inertia;

class VehicleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view vehicles')->only(['index', 'show']);
        $this->middleware('permission:create vehicles')->only(['create', 'store']);
        $this->middleware('permission:edit vehicles')->only(['edit', 'update']);
        $this->middleware('permission:delete vehicles')->only(['destroy']);
        $this->middleware('permission:manage vehicle status')->only(['deregister', 'reactivate']);
    }

    public function index(Request $request)
    {
        $query = Vehicle::with(['companyCategory', 'company']);

        // 搜尋
        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        // 狀態篩選 (預設顯示在籍)
        $status = $request->get('status', 'active');
        if ($status !== '') {
            $query->where('vehicle_status', $status);
        }

        // 公司類別篩選
        if ($request->has('company_category_id') && $request->company_category_id) {
            $query->byCompanyCategory($request->company_category_id);
        }

        // 公司篩選
        if ($request->has('company_id') && $request->company_id) {
            $query->byCompany($request->company_id);
        }

        // 即將到期檢驗
        if ($request->has('expiring_inspection') && $request->expiring_inspection) {
            $query->expiringInspection(30);
        }

        $vehicles = $query->orderBy('created_at', 'desc')->paginate(15);

        $companyCategories = CompanyCategory::all();
        $companies = Company::select('id', 'name', 'category_id')->get();

        return Inertia::render('Admin/Vehicles/Index', [
            'vehicles' => $vehicles,
            'companyCategories' => $companyCategories,
            'companies' => $companies,
            'filters' => $request->only(['search', 'status', 'company_category_id', 'company_id', 'expiring_inspection']),
        ]);
    }

    public function create(Request $request)
    {
        $companyCategories = CompanyCategory::all();
        $companies = Company::select('id', 'name', 'category_id')->get();

        // 檢查是否為換牌模式
        $sourceVehicle = null;
        if ($request->has('source')) {
            $sourceVehicle = Vehicle::find($request->input('source'));

            if ($sourceVehicle) {
                // 轉換民國年
                if ($sourceVehicle->license_issue_year) {
                    $sourceVehicle->license_issue_year_republic = Vehicle::convertWesternToRepublic(
                        $sourceVehicle->license_issue_year
                    );
                }

                if ($sourceVehicle->inspection_year) {
                    $sourceVehicle->inspection_year_republic = Vehicle::convertWesternToRepublic(
                        $sourceVehicle->inspection_year
                    );
                }

                if ($sourceVehicle->registration_year) {
                    $sourceVehicle->registration_year_republic = Vehicle::convertWesternToRepublic(
                        $sourceVehicle->registration_year
                    );
                }

                // 清除不需要複製的欄位
                $sourceVehicle->license_number = null;
                $sourceVehicle->id = null;
                $sourceVehicle->vehicle_status = 'active';
                $sourceVehicle->created_by = null;
                $sourceVehicle->updated_by = null;
                $sourceVehicle->created_at = null;
                $sourceVehicle->updated_at = null;
                $sourceVehicle->deleted_at = null;
                $sourceVehicle->replacement_license = null;

                // 不複製退籍日期
                $sourceVehicle->deregistration_year = null;
                $sourceVehicle->deregistration_month = null;
                $sourceVehicle->deregistration_day = null;
            }
        }

        return Inertia::render('Admin/Vehicles/Create', [
            'companyCategories' => $companyCategories,
            'companies' => $companies,
            'sourceVehicle' => $sourceVehicle,
            'isReplacementMode' => $sourceVehicle !== null,
        ]);
    }

    public function store(VehicleStoreRequest $request)
    {
        $validated = $request->validated();

        // 民國年轉西元年
        if ($validated['license_issue_year_republic'] ?? null) {
            $validated['license_issue_year'] = Vehicle::convertRepublicToWestern($validated['license_issue_year_republic']);
            unset($validated['license_issue_year_republic']);
        }

        if ($validated['inspection_year_republic'] ?? null) {
            $validated['inspection_year'] = Vehicle::convertRepublicToWestern($validated['inspection_year_republic']);
            unset($validated['inspection_year_republic']);
        }

        if ($validated['registration_year_republic'] ?? null) {
            $validated['registration_year'] = Vehicle::convertRepublicToWestern($validated['registration_year_republic']);
            unset($validated['registration_year_republic']);
        } else {
            // 預設為當前日期
            $now = now();
            $validated['registration_year'] = $now->year;
            $validated['registration_month'] = $validated['registration_month'] ?? $now->month;
            $validated['registration_day'] = $validated['registration_day'] ?? $now->day;
        }

        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $vehicle = DB::transaction(function () use ($validated) {
            $vehicle = Vehicle::create($validated);

            // 處理替補車號邏輯
            if (!empty($validated['replacement_license'])) {
                $this->handleReplacementLicense($vehicle, $validated['replacement_license']);
            }

            // 記錄日誌
            VehicleAuditLog::logVehicleAction(
                $vehicle->id,
                'create',
                "新增車輛：{$vehicle->license_number}",
                null,
                $vehicle->toArray()
            );

            return $vehicle;
        });

        return redirect()->route('admin.vehicles.index')->with('success', '車輛新增成功');
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['companyCategory', 'company', 'creator', 'updater']);

        return Inertia::render('Admin/Vehicles/Show', [
            'vehicle' => $vehicle,
        ]);
    }

    public function edit(Vehicle $vehicle)
    {
        $companyCategories = CompanyCategory::all();
        $companies = Company::select('id', 'name', 'category_id')->get();

        return Inertia::render('Admin/Vehicles/Edit', [
            'vehicle' => $vehicle,
            'companyCategories' => $companyCategories,
            'companies' => $companies,
        ]);
    }

    public function update(VehicleUpdateRequest $request, Vehicle $vehicle)
    {
        $validated = $request->validated();

        // 記錄變更前的資料
        $oldValues = $vehicle->toArray();

        // 民國年轉西元年
        if ($validated['license_issue_year_republic'] ?? null) {
            $validated['license_issue_year'] = Vehicle::convertRepublicToWestern($validated['license_issue_year_republic']);
            unset($validated['license_issue_year_republic']);
        }

        if ($validated['inspection_year_republic'] ?? null) {
            $validated['inspection_year'] = Vehicle::convertRepublicToWestern($validated['inspection_year_republic']);
            unset($validated['inspection_year_republic']);
        }

        $validated['updated_by'] = auth()->id();

        $vehicle->update($validated);

        // 記錄日誌
        $changes = array_keys(array_diff_assoc($validated, $oldValues));
        if (!empty($changes)) {
            VehicleAuditLog::logVehicleAction(
                $vehicle->id,
                'update',
                "更新車輛：{$vehicle->license_number}",
                $oldValues,
                $vehicle->fresh()->toArray(),
                $changes
            );
        }

        return redirect()->route('admin.vehicles.show', $vehicle)->with('success', '車輛更新成功');
    }

    public function destroy(Vehicle $vehicle)
    {
        // 記錄日誌
        VehicleAuditLog::logVehicleAction(
            $vehicle->id,
            'delete',
            "刪除車輛：{$vehicle->license_number}",
            $vehicle->toArray(),
            null
        );

        $vehicle->delete();

        return redirect()->route('admin.vehicles.index')->with('success', '車輛刪除成功');
    }

    public function deregister(Vehicle $vehicle)
    {
        $oldValues = $vehicle->toArray();
        $vehicle->deregister();

        // 記錄日誌
        VehicleAuditLog::logVehicleAction(
            $vehicle->id,
            'deregister',
            "車輛退籍：{$vehicle->license_number}",
            $oldValues,
            $vehicle->fresh()->toArray()
        );

        return redirect()->back()->with('success', '車輛退籍成功');
    }

    public function reactivate(Vehicle $vehicle)
    {
        $oldValues = $vehicle->toArray();
        $vehicle->reactivate();

        // 記錄日誌
        VehicleAuditLog::logVehicleAction(
            $vehicle->id,
            'reactivate',
            "車輛復籍：{$vehicle->license_number}",
            $oldValues,
            $vehicle->fresh()->toArray()
        );

        return redirect()->back()->with('success', '車輛復籍成功');
    }

    // API 方法
    public function getReplacementLicenses(Request $request)
    {
        $companyId = (int) $request->input('company_id');

        if (!$companyId) {
            return response()->json([]);
        }

        $company = Company::select('id', 'category_id')->find($companyId);

        if (!$company) {
            return response()->json([]);
        }

        $licenses = VehicleLicense::revoked()
            ->byCompany($companyId)
            ->select(
                'id',
                'license_number',
                'holder_name',
                'previous_license_number',
                'previous_holder_name'
            )
            ->get()
            ->map(function ($license) {
                $replacementNumber = $license->previous_license_number ?: $license->license_number;

                if (empty($replacementNumber)) {
                    return null;
                }

                return [
                    'id' => $license->id,
                    'replacement_number' => $replacementNumber,
                    'holder_name' => $license->previous_holder_name ?: $license->holder_name,
                ];
            })
            ->filter()
            ->values();

        return response()->json($licenses);
    }

    public function getCompanyOwners(Request $request)
    {
        $categoryId = $request->input('company_category_id');

        if (!VehicleConfigSetting::isOwnerDropdownEnabled($categoryId)) {
            return response()->json([]);
        }

        $companies = Company::whereHas('companyCategory', function ($query) use ($categoryId) {
            $query->where('id', $categoryId);
        })->select('id', 'name')->get();

        return response()->json($companies->pluck('name', 'id'));
    }

    // 匯出車輛資料
    public function export(Request $request)
    {
        $filters = $request->only(['search', 'status', 'company_category_id', 'company_id', 'expiring_inspection']);

        return Excel::download(new VehiclesExport($filters), 'vehicles_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
    }

    // 匯入車輛資料
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        try {
            $import = new VehiclesImport();
            Excel::import($import, $request->file('file'));

            $successCount = $import->getSuccessCount();
            $failures = $import->getFailures();

            if (empty($failures)) {
                return back()->with('success', "成功匯入 {$successCount} 筆車輛資料！");
            } else {
                $failureCount = count($failures);
                $message = "匯入完成！成功：{$successCount} 筆，失敗：{$failureCount} 筆。";

                return back()
                    ->with('warning', $message)
                    ->with('import_failures', $failures);
            }

        } catch (\Exception $e) {
            return back()->with('error', '匯入失敗：' . $e->getMessage());
        }
    }

    // 下載匯入範本
    public function template()
    {
        return Excel::download(new VehicleTemplateExport(), 'vehicle_import_template.xlsx');
    }

    // 取得車輛操作日誌
    public function auditLogs(Vehicle $vehicle)
    {
        $logs = $vehicle->auditLogs()
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($logs);
    }

    private function handleReplacementLicense(Vehicle $vehicle, $replacementLicenseId)
    {
        // 如果傳遞的是 ID，使用 ID 查詢；如果是字串，則按原有邏輯查詢
        if (is_numeric($replacementLicenseId)) {
            $vehicleLicense = VehicleLicense::where('id', $replacementLicenseId)
                ->where('company_id', $vehicle->company_id)
                ->where('status', 'revoked')
                ->first();
        } else {
            // 向後相容：如果傳遞的是 replacement_number 字串
            $vehicleLicense = VehicleLicense::where(function($query) use ($replacementLicenseId) {
                $query->where('previous_license_number', $replacementLicenseId)
                      ->orWhere('license_number', $replacementLicenseId);
            })
            ->where('company_id', $vehicle->company_id)
            ->where('status', 'revoked')
            ->first();
        }

        if ($vehicleLicense) {
            $vehicleLicense->update([
                'license_number' => $vehicle->license_number,
                'license_year' => $vehicle->manufacture_year,
                'license_month' => $vehicle->manufacture_month,
                'status' => 'active',
                'replacement_date' => now()->toDateString(),
                'updated_by' => auth()->id(),
            ]);
        }
    }
}
