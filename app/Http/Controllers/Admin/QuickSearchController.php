<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\DriverVehicleAssignment;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuickSearchController extends Controller
{
    /**
     * 顯示快速搜尋頁面
     */
    public function index(Request $request): Response
    {
        $keyword = $request->input('keyword');
        $type = $request->input('type', 'all');

        $drivers = [];
        $vehicles = [];

        if ($keyword) {
            $searchResult = $this->performSearch($type, $keyword);
            $drivers = $searchResult['drivers'];
            $vehicles = $searchResult['vehicles'];
        }

        return Inertia::render('Admin/QuickSearch/Index', [
            'drivers' => $drivers,
            'vehicles' => $vehicles,
            'filters' => [
                'keyword' => $keyword,
                'type' => $type,
            ],
        ]);
    }

    /**
     * 執行搜尋（使用 Eloquent 關聯優化效能）
     */
    private function performSearch(string $type, string $keyword): array
    {
        $drivers = collect();
        $vehicles = collect();

        if ($type === 'all' || $type === 'driver') {
            // 搜尋駕駛並預載入關聯資料（避免 N+1 查詢）
            $drivers = Driver::search($keyword)
                ->with([
                    'companyCategory',
                    'vehicles' => function ($query) {
                        $query->with(['companyCategory', 'company']);
                    },
                ])
                ->get();

            // 如果搜尋類型為 all，收集從駕駛找到的車輛
            if ($type === 'all' && $drivers->isNotEmpty()) {
                $vehicles = $drivers->pluck('vehicles')->flatten()->unique('id');
            }
        }

        if ($type === 'all' || $type === 'vehicle') {
            // 搜尋車輛並預載入關聯資料
            $vehicleQuery = Vehicle::search($keyword)
                ->with([
                    'companyCategory',
                    'company',
                    'drivers' => function ($query) {
                        $query->with('companyCategory');
                    },
                ]);

            // 如果 type 為 all 且已經有從駕駛找到的車輛，則排除這些車輛避免重複
            if ($type === 'all' && $vehicles->isNotEmpty()) {
                $existingVehicleIds = $vehicles->pluck('id');
                $vehicleQuery->whereNotIn('id', $existingVehicleIds);
            }

            $searchedVehicles = $vehicleQuery->get();

            // 合併車輛列表
            if ($type === 'all') {
                $vehicles = $vehicles->merge($searchedVehicles)->unique('id')->values();

                // 如果搜尋類型為 all，收集從車輛找到的駕駛並合併到駕駛列表
                if ($searchedVehicles->isNotEmpty()) {
                    $searchedDrivers = $searchedVehicles->pluck('drivers')->flatten()->unique('id');
                    $drivers = $drivers->merge($searchedDrivers)->unique('id')->values();
                }
            } else {
                $vehicles = $searchedVehicles;
            }
        }

        return [
            'drivers' => $drivers->values()->toArray(),
            'vehicles' => $vehicles->values()->toArray(),
        ];
    }

    /**
     * 顯示駕駛詳細資料（包含綁定車輛）
     */
    public function showDriver(Driver $driver)
    {
        return response()->json(
            $driver->load([
                'companyCategory',
                'vehicles' => function ($query) {
                    $query->with(['companyCategory', 'company']);
                },
                'recurringCostTemplate.items.accountDetail',
            ])
        );
    }

    /**
     * 顯示車輛詳細資料（包含綁定駕駛）
     */
    public function showVehicle(Vehicle $vehicle)
    {
        return response()->json(
            $vehicle->load([
                'companyCategory',
                'company',
                'drivers' => function ($query) {
                    $query->with('companyCategory');
                },
            ])
        );
    }
}
