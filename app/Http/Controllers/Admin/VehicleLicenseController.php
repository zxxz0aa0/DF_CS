<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VehicleLicenseRequest;
use App\Http\Resources\VehicleLicenseResource;
use App\Models\Company;
use App\Models\VehicleLicense;
use App\Models\VehicleLicenseAuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VehicleLicenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view vehicle licenses')->only(['index', 'show']);
        $this->middleware('permission:create vehicle licenses')->only(['create', 'store']);
        $this->middleware('permission:edit vehicle licenses')->only(['edit', 'update']);
        $this->middleware('permission:delete vehicle licenses')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = VehicleLicense::with(['company', 'creator', 'updater']);

        // 搜尋篩選
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('license_number', 'like', "%{$search}%")
                    ->orWhere('holder_name', 'like', "%{$search}%")
                    ->orWhere('county', 'like', "%{$search}%");
            });
        }

        if ($request->company_id) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->county) {
            $query->where('county', 'like', "%{$request->county}%");
        }

        // 排序
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $licenses = $query->paginate(15)->withQueryString();

        // 計算統計數據
        $stats = [
            'total_licenses' => VehicleLicense::count(),
            'active_licenses' => VehicleLicense::where('status', 'active')->count(),
            'revoked_licenses' => VehicleLicense::where('status', 'revoked')->count(),
        ];

        return Inertia::render('Admin/VehicleLicenses/Index', [
            'licenses' => VehicleLicenseResource::collection($licenses),
            'companies' => Company::select('id', 'name')->get(),
            'filters' => $request->only(['search', 'company_id', 'status', 'county']),
            'stats' => $stats,
            'can' => [
                'create' => auth()->user()->can('create vehicle licenses'),
                'edit' => auth()->user()->can('edit vehicle licenses'),
                'delete' => auth()->user()->can('delete vehicle licenses'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/VehicleLicenses/Create', [
            'companies' => Company::select('id', 'name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VehicleLicenseRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        $license = VehicleLicense::create($data);

        // 記錄審計日誌
        VehicleLicenseAuditLog::log('created', $license, null, $data);

        return redirect()->route('admin.vehicle-licenses.index')
            ->with('success', '車輛牌照建立成功');
    }

    /**
     * Display the specified resource.
     */
    public function show(VehicleLicense $vehicleLicense): Response
    {
        $vehicleLicense->load(['company', 'creator', 'updater', 'auditLogs.user']);

        return Inertia::render('Admin/VehicleLicenses/Show', [
            'license' => new VehicleLicenseResource($vehicleLicense),
            'auditLogs' => $vehicleLicense->auditLogs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'action_label' => $log->action_label,
                    'old_values' => $log->old_values,
                    'new_values' => $log->new_values,
                    'user' => $log->user ? [
                        'id' => $log->user->id,
                        'name' => $log->user->name,
                    ] : null,
                    'ip_address' => $log->ip_address,
                    'created_at' => $log->created_at?->format('Y-m-d H:i:s'),
                ];
            }),
            'can' => [
                'edit' => auth()->user()->can('edit vehicle licenses'),
                'delete' => auth()->user()->can('delete vehicle licenses'),
                'viewAuditLogs' => auth()->user()->can('view vehicle license audit logs'),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VehicleLicense $vehicleLicense): Response
    {
        return Inertia::render('Admin/VehicleLicenses/Edit', [
            'license' => new VehicleLicenseResource($vehicleLicense->load(['company'])),
            'companies' => Company::select('id', 'name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(VehicleLicenseRequest $request, VehicleLicense $vehicleLicense)
    {
        $oldData = $vehicleLicense->toArray();
        $newData = $request->validated();
        $newData['updated_by'] = auth()->id();

        $vehicleLicense->update($newData);

        // 記錄審計日誌
        VehicleLicenseAuditLog::log('updated', $vehicleLicense, $oldData, $newData);

        return redirect()->route('admin.vehicle-licenses.index')
            ->with('success', '車輛牌照更新成功');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VehicleLicense $vehicleLicense)
    {
        $oldData = $vehicleLicense->toArray();

        // 記錄審計日誌
        VehicleLicenseAuditLog::log('deleted', $vehicleLicense, $oldData, null);

        $vehicleLicense->delete();

        return redirect()->route('admin.vehicle-licenses.index')
            ->with('success', '車輛牌照刪除成功');
    }

    /**
     * 繳銷牌照
     */
    public function revoke(VehicleLicense $vehicle_license)
    {
        if ($vehicle_license->status === 'revoked') {
            return back()->with('error', '此牌照已經繳銷');
        }

        $oldData = $vehicle_license->toArray();

        // 備份當前資料到前資料欄位，然後清空當前資料
        $vehicle_license->update([
            // 備份前資料
            'previous_license_number' => $vehicle_license->license_number,
            'previous_holder_name' => $vehicle_license->holder_name,
            'previous_license_year' => $vehicle_license->license_year,
            'previous_license_month' => $vehicle_license->license_month,

            // 清空當前資料
            'license_number' => null,
            'holder_name' => null,
            'license_year' => null,
            'license_month' => null,

            // 設定狀態和日期
            'status' => 'revoked',
            'revocation_date' => now()->toDateString(),
            'updated_by' => auth()->id(),
        ]);

        // 記錄審計日誌
        VehicleLicenseAuditLog::log('revoked', $vehicle_license, $oldData, $vehicle_license->toArray());

        return back()->with('success', '牌照繳銷成功');
    }

    /**
     * 替補牌照
     */
    public function replace(Request $request, VehicleLicense $vehicle_license)
    {
        if ($vehicle_license->status !== 'revoked') {
            return back()->with('error', '只有已繳銷的牌照才能進行替補');
        }

        // 驗證輸入資料
        $request->validate([
            'license_number' => 'required|string|max:50',
            'holder_name' => 'nullable|string|max:100',
            'license_year' => 'nullable|integer|min:1900|max:'.(now()->year + 10),
            'license_month' => 'nullable|integer|min:1|max:12',
        ]);

        $oldData = $vehicle_license->toArray();

        // 備份當前資料到前資料欄位，並更新新資料
        $vehicle_license->update([
            // 備份前資料
            'previous_license_number' => $vehicle_license->license_number,
            'previous_holder_name' => $vehicle_license->holder_name,
            'previous_license_year' => $vehicle_license->license_year,
            'previous_license_month' => $vehicle_license->license_month,

            // 更新新資料
            'license_number' => $request->license_number,
            'holder_name' => $request->holder_name,
            'license_year' => $request->license_year,
            'license_month' => $request->license_month,
            'status' => 'active',
            'replacement_date' => now()->toDateString(),
            'revocation_date' => null,
            'updated_by' => auth()->id(),
        ]);

        // 記錄審計日誌
        VehicleLicenseAuditLog::log('replaced', $vehicle_license, $oldData, $vehicle_license->toArray());

        return back()->with('success', '牌照替補成功');
    }
}
