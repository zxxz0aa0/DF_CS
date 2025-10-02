<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AccountingRecordRequest;
use App\Models\AccountingRecord;
use App\Services\AccountingRecordService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountingRecordController extends Controller
{
    protected $accountingRecordService;

    public function __construct(AccountingRecordService $accountingRecordService)
    {
        $this->accountingRecordService = $accountingRecordService;
    }

    /**
     * 顯示帳務管理主頁面
     */
    public function index(Request $request)
    {
        // 處理搜尋邏輯
        $searchType = $request->input('search_type'); // name, license, fleet
        $keyword = $request->input('keyword');

        $drivers = [];
        $vehicles = [];
        $records = [];
        $statistics = null;

        if ($keyword) {
            // 根據搜尋類型執行搜尋
            $result = $this->accountingRecordService->search($searchType, $keyword);
            $drivers = $result['drivers'];
            $vehicles = $result['vehicles'];
        }

        // 如果有選擇的駕駛或車輛，載入帳務記錄
        $selectedDriverId = $request->input('driver_id');
        $selectedVehicleId = $request->input('vehicle_id');

        // 如果有搜尋結果但沒有選擇，自動選擇第一個（駕駛和車輛都選）
        if (!$selectedDriverId && !$selectedVehicleId && $keyword) {
            // 同時選擇駕駛和車輛的第一個
            if (!empty($drivers)) {
                $selectedDriverId = $drivers[0]->id;
            }
            if (!empty($vehicles)) {
                $selectedVehicleId = $vehicles[0]->id;
            }
        }

        // 載入帳務記錄
        // 判斷要顯示駕駛還是車輛的帳務（根據使用者最後點選的）
        $viewType = $request->input('view_type'); // 'driver' 或 'vehicle'

        if ($selectedDriverId || $selectedVehicleId) {
            // 如果沒有指定 view_type，預設優先駕駛
            if (!$viewType) {
                $viewType = $selectedDriverId ? 'driver' : 'vehicle';
            }

            // 根據 view_type 決定載入哪個的帳務記錄
            if ($viewType === 'driver' && $selectedDriverId) {
                $records = $this->accountingRecordService->getRecords($selectedDriverId, null);
            } elseif ($viewType === 'vehicle' && $selectedVehicleId) {
                $records = $this->accountingRecordService->getRecords(null, $selectedVehicleId);
            } else {
                $records = [];
            }

            $statistics = $this->accountingRecordService->calculateStatistics($records);
        }

        return Inertia::render('Admin/AccountingRecords/Index', [
            'drivers' => $drivers,
            'vehicles' => $vehicles,
            'records' => $records,
            'statistics' => $statistics,
            'filters' => [
                'search_type' => $searchType,
                'keyword' => $keyword,
                'driver_id' => $selectedDriverId,
                'vehicle_id' => $selectedVehicleId,
            ]
        ]);
    }

    /**
     * 批次儲存帳務記錄
     */
    public function store(AccountingRecordRequest $request)
    {
        try {
            $records = $this->accountingRecordService->batchCreate(
                $request->validated()
            );

            // 保留前端傳來的選擇狀態
            $selectedDriverId = $request->input('driver_id');
            $selectedVehicleId = $request->input('vehicle_id');
            $viewType = $request->input('view_type', 'driver');
            $searchType = $request->input('search_type');
            $keyword = $request->input('keyword');

            // 重新執行搜尋以取得駕駛和車輛列表
            $drivers = [];
            $vehicles = [];
            if ($keyword) {
                $result = $this->accountingRecordService->search($searchType, $keyword);
                $drivers = $result['drivers'];
                $vehicles = $result['vehicles'];
            }

            // 根據 view_type 載入對應的帳務記錄
            $accountingRecords = collect();
            if ($viewType === 'driver' && $selectedDriverId) {
                $accountingRecords = $this->accountingRecordService->getRecords($selectedDriverId, null);
            } elseif ($viewType === 'vehicle' && $selectedVehicleId) {
                $accountingRecords = $this->accountingRecordService->getRecords(null, $selectedVehicleId);
            }

            // 計算統計資訊
            $statistics = $this->accountingRecordService->calculateStatistics($accountingRecords);

            // 返回 Inertia 響應，只更新需要的部分
            return back()->with([
                'records' => ['data' => $accountingRecords->toArray()],
                'statistics' => $statistics,
                'drivers' => $drivers,
                'vehicles' => $vehicles,
                'filters' => [
                    'driver_id' => $selectedDriverId,
                    'vehicle_id' => $selectedVehicleId,
                    'view_type' => $viewType,
                    'search_type' => $searchType,
                    'keyword' => $keyword,
                ],
                'success' => '成功新增 ' . count($records) . ' 筆帳務記錄'
            ]);

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => '新增失敗：' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * 更新單筆帳務記錄
     */
    public function update(AccountingRecordRequest $request, AccountingRecord $accountingRecord)
    {
        try {
            $this->accountingRecordService->update($accountingRecord, $request->validated());

            return back()->with('success', '帳務記錄已更新');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => '更新失敗：' . $e->getMessage()]);
        }
    }

    /**
     * 批次軟刪除帳務記錄
     */
    public function batchDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:accounting_records,id'
        ]);

        try {
            $count = $this->accountingRecordService->batchDelete($request->ids);

            return back()->with('success', "成功刪除 {$count} 筆帳務記錄");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => '刪除失敗：' . $e->getMessage()]);
        }
    }

    /**
     * API: 搜尋會計科目
     */
    public function searchAccountDetails(Request $request)
    {
        $keyword = $request->input('keyword');

        $accounts = $this->accountingRecordService->searchAccountDetails($keyword);

        return response()->json($accounts);
    }
}
