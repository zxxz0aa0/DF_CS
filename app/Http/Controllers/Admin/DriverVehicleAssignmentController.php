<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\DriverVehicleAssignment;
use App\Models\Vehicle;
use App\Models\VehicleLicense;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DriverVehicleAssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view driver vehicle assignments')->only(['index', 'show']);
        $this->middleware('permission:create driver vehicle assignments')->only(['create', 'store']);
        $this->middleware('permission:edit driver vehicle assignments')->only(['edit', 'update']);
        $this->middleware('permission:delete driver vehicle assignments')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DriverVehicleAssignment::with(['driver', 'vehicle']);

        if ($request->filled('driver_search')) {
            $query->whereHas('driver', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->driver_search . '%');
            });
        }

        if ($request->filled('vehicle_search')) {
            $query->whereHas('vehicle', function ($q) use ($request) {
                $q->where('license_number', 'like', '%' . $request->vehicle_search . '%');
            });
        }

        $assignments = $query->orderBy('created_at', 'desc')->paginate(15);

        return Inertia::render('Admin/DriverVehicleAssignments/Index', [
            'assignments' => $assignments,
            'filters' => [
                'driver_search' => $request->driver_search,
                'vehicle_search' => $request->vehicle_search,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $drivers = Driver::active()->orderBy('name')->get(['id', 'name', 'id_number']);
        $vehicles = Vehicle::where('vehicle_status', 'active')->orderBy('license_number')->get(['id', 'license_number', 'brand', 'vehicle_model']);

        return Inertia::render('Admin/DriverVehicleAssignments/Create', [
            'drivers' => $drivers,
            'vehicles' => $vehicles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        try {
            DriverVehicleAssignment::create($validated);

            // 取得駕駛和車輛資料
            $driver = Driver::find($validated['driver_id']);
            $vehicle = Vehicle::find($validated['vehicle_id']);

            // 更新 vehicle_licenses 的 holder_name
            $updatedCount = VehicleLicense::where('license_number', $vehicle->license_number)
                ->where('status', 'active')
                ->update([
                    'holder_name' => $driver->name,
                    'updated_by' => auth()->id(),
                    'updated_at' => now()
                ]);

            // 提供不同的成功訊息
            if ($updatedCount > 0) {
                return redirect()->route('admin.driver-vehicle-assignments.index')
                    ->with('success', "駕駛與車輛綁定成功！車牌 {$vehicle->license_number} 的持有人已更新為 {$driver->name}。");
            } else {
                return redirect()->route('admin.driver-vehicle-assignments.index')
                    ->with('success', '駕駛與車輛綁定成功！')
                    ->with('warning', "未找到車牌 {$vehicle->license_number} 對應的有效牌照記錄。");
            }
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()->withErrors(['driver_id' => '此駕駛與車輛已經綁定過了。']);
            }
            throw $e;
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DriverVehicleAssignment $driverVehicleAssignment)
    {
        $drivers = Driver::active()->orderBy('name')->get(['id', 'name', 'id_number']);
        $vehicles = Vehicle::where('vehicle_status', 'active')->orderBy('license_number')->get(['id', 'license_number', 'brand', 'vehicle_model']);

        return Inertia::render('Admin/DriverVehicleAssignments/Edit', [
            'assignment' => $driverVehicleAssignment->load(['driver', 'vehicle']),
            'drivers' => $drivers,
            'vehicles' => $vehicles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DriverVehicleAssignment $driverVehicleAssignment)
    {
        $validated = $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['updated_by'] = auth()->id();

        try {
            // 記錄舊的車輛和駕駛資料
            $oldVehicle = $driverVehicleAssignment->vehicle;
            $oldDriver = $driverVehicleAssignment->driver;

            // 更新綁定
            $driverVehicleAssignment->update($validated);

            // 取得新的駕駛和車輛資料
            $newDriver = Driver::find($validated['driver_id']);
            $newVehicle = Vehicle::find($validated['vehicle_id']);

            // 如果車輛有變更，需要處理兩台車的牌照持有人
            if ($oldVehicle->id !== $newVehicle->id) {
                // 更新新車輛的牌照持有人為新駕駛
                VehicleLicense::where('license_number', $newVehicle->license_number)
                    ->where('status', 'active')
                    ->update([
                        'holder_name' => $newDriver->name,
                        'updated_by' => auth()->id(),
                        'updated_at' => now()
                    ]);

                // 檢查舊車輛是否還有其他駕駛
                $remainingDrivers = DriverVehicleAssignment::where('vehicle_id', $oldVehicle->id)
                    ->where('id', '!=', $driverVehicleAssignment->id)
                    ->with('driver')
                    ->get();

                if ($remainingDrivers->isNotEmpty()) {
                    // 如果還有其他駕駛，更新為最後一位駕駛的姓名
                    $lastDriver = $remainingDrivers->last()->driver;
                    VehicleLicense::where('license_number', $oldVehicle->license_number)
                        ->where('status', 'active')
                        ->update([
                            'holder_name' => $lastDriver->name,
                            'updated_by' => auth()->id(),
                            'updated_at' => now()
                        ]);
                } else {
                    // 沒有其他駕駛，清空持有人
                    VehicleLicense::where('license_number', $oldVehicle->license_number)
                        ->where('status', 'active')
                        ->update([
                            'holder_name' => null,
                            'updated_by' => auth()->id(),
                            'updated_at' => now()
                        ]);
                }
            } elseif ($oldDriver->id !== $newDriver->id) {
                // 如果只是更換駕駛（車輛未變），直接更新持有人
                VehicleLicense::where('license_number', $newVehicle->license_number)
                    ->where('status', 'active')
                    ->update([
                        'holder_name' => $newDriver->name,
                        'updated_by' => auth()->id(),
                        'updated_at' => now()
                    ]);
            }

            return redirect()->route('admin.driver-vehicle-assignments.index')
                ->with('success', '綁定資料已成功更新！');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()->withErrors(['driver_id' => '此駕駛與車輛已經綁定過了。']);
            }
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DriverVehicleAssignment $driverVehicleAssignment)
    {
        $driverName = $driverVehicleAssignment->driver->name;
        $vehiclePlate = $driverVehicleAssignment->vehicle->license_number;

        $driverVehicleAssignment->delete();

        return redirect()->route('admin.driver-vehicle-assignments.index')
            ->with('success', "已成功解除 {$driverName} 與 {$vehiclePlate} 的綁定！");
    }
}
