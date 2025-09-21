<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DriverStoreRequest;
use App\Http\Requests\DriverUpdateRequest;
use App\Models\CompanyCategory;
use App\Models\Driver;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DriverController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view drivers')->only(['index', 'show']);
        $this->middleware('permission:create drivers')->only(['create', 'store']);
        $this->middleware('permission:edit drivers')->only(['edit', 'update']);
        $this->middleware('permission:delete drivers')->only(['destroy']);
        $this->middleware('permission:export drivers')->only(['export']);
        $this->middleware('permission:import drivers')->only(['import']);
        $this->middleware('permission:view expiring licenses')->only(['expiringLicenses']);
        $this->middleware('permission:edit drivers')->only(['toggleStatus']);
    }
    public function index(Request $request)
    {
        $query = Driver::with(['companyCategory']);

        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('company_category_id') && $request->company_category_id) {
            $query->where('company_category_id', $request->company_category_id);
        }

        if ($request->has('license_expiring') && $request->license_expiring) {
            $query->expiringLicenses(30);
        }

        $drivers = $query->orderBy('created_at', 'desc')->paginate(15);

        $companyCategories = CompanyCategory::all();

        return Inertia::render('Admin/Drivers/Index', [
            'drivers' => $drivers,
            'companyCategories' => $companyCategories,
            'filters' => $request->only(['search', 'status', 'company_category_id', 'license_expiring']),
        ]);
    }

    public function create()
    {
        $companyCategories = CompanyCategory::all();

        return Inertia::render('Admin/Drivers/Create', [
            'companyCategories' => $companyCategories,
        ]);
    }

    public function store(DriverStoreRequest $request)
    {
        Driver::create($request->validated());

        return redirect()->route('admin.drivers.index')
            ->with('success', '駕駛資料新增成功！');
    }

    public function show(Driver $driver)
    {
        $driver->load(['companyCategory']);

        return Inertia::render('Admin/Drivers/Show', [
            'driver' => $driver,
        ]);
    }

    public function edit(Driver $driver)
    {
        $driver->load(['companyCategory']);
        $companyCategories = CompanyCategory::all();

        return Inertia::render('Admin/Drivers/Edit', [
            'driver' => $driver,
            'companyCategories' => $companyCategories,
        ]);
    }

    public function update(DriverUpdateRequest $request, Driver $driver)
    {
        $driver->update($request->validated());

        return redirect()->route('admin.drivers.index')
            ->with('success', '駕駛資料更新成功！');
    }

    public function destroy(Driver $driver)
    {
        $driver->delete();

        return redirect()->route('admin.drivers.index')
            ->with('success', '駕駛資料刪除成功！');
    }

    public function toggleStatus(Request $request, Driver $driver)
    {
        $request->validate([
            'status' => 'required|in:open,close'
        ]);

        $driver->update([
            'status' => $request->status
        ]);

        $statusText = $request->status === 'open' ? '復籍' : '退籍';

        return redirect()->route('admin.drivers.index')
            ->with('success', "駕駛 {$driver->name} 已成功{$statusText}！");
    }

    public function expiringLicenses(Request $request)
    {
        $days = $request->get('days', 30);

        $drivers = Driver::with(['companyCategory'])
            ->expiringLicenses($days)
            ->active()
            ->get()
            ->map(function ($driver) {
                return [
                    'id' => $driver->id,
                    'name' => $driver->name,
                    'id_number' => $driver->id_number,
                    'company_category' => $driver->companyCategory?->name,
                    'mobile_phone1' => $driver->mobile_phone1,
                    'home_phone' => $driver->home_phone,
                    'license_expire_date' => $driver->license_expire_date,
                    'professional_license_expire_date' => $driver->professional_license_expire_date,
                    'license_days_remaining' => $driver->license_days_remaining,
                    'professional_license_days_remaining' => $driver->professional_license_days_remaining,
                ];
            });

        return Inertia::render('Admin/Drivers/ExpiringLicenses', [
            'drivers' => $drivers,
            'total' => $drivers->count(),
            'filters' => $request->only(['days', 'licenseType', 'status', 'company']),
        ]);
    }

    public function export(Request $request)
    {
        // TODO: 實作 Excel 匯出功能
        return response()->json(['message' => 'Excel 匯出功能開發中']);
    }

    public function import(Request $request)
    {
        // TODO: 實作 Excel 匯入功能
        return response()->json(['message' => 'Excel 匯入功能開發中']);
    }
}
