<?php

namespace App\Services;

use App\Models\AccountingRecord;
use App\Models\AccountDetail;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\DriverVehicleAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountingRecordService
{
    /**
     * 搜尋功能
     */
    public function search(string $searchType, string $keyword): array
    {
        $drivers = [];
        $vehicles = [];

        switch ($searchType) {
            case 'name':
                // 搜尋駕駛姓名（查詢所有欄位供詳細頁面使用）
                $drivers = Driver::where('name', 'like', "%{$keyword}%")
                    ->with('companyCategory')
                    ->get();

                // 透過駕駛找車輛（查詢所有欄位供詳細頁面使用）
                if ($drivers->isNotEmpty()) {
                    $driverIds = $drivers->pluck('id');
                    $vehicleIds = DriverVehicleAssignment::whereIn('driver_id', $driverIds)
                        ->pluck('vehicle_id');
                    $vehicles = Vehicle::whereIn('id', $vehicleIds)
                        ->with(['companyCategory', 'company'])
                        ->get();
                }
                break;

            case 'license':
                // 搜尋車牌號碼（查詢所有欄位供詳細頁面使用）
                $vehicles = Vehicle::where('license_number', 'like', "%{$keyword}%")
                    ->with(['companyCategory', 'company'])
                    ->get();

                // 透過車輛找駕駛（查詢所有欄位供詳細頁面使用）
                if ($vehicles->isNotEmpty()) {
                    $vehicleIds = $vehicles->pluck('id');
                    $driverIds = DriverVehicleAssignment::whereIn('vehicle_id', $vehicleIds)
                        ->pluck('driver_id');
                    $drivers = Driver::whereIn('id', $driverIds)
                        ->with('companyCategory')
                        ->get();
                }
                break;

            case 'fleet':
                // 搜尋車隊編號（查詢所有欄位供詳細頁面使用）
                $vehicles = Vehicle::where('fleet_number', 'like', "%{$keyword}%")
                    ->with(['companyCategory', 'company'])
                    ->get();

                // 透過車輛找駕駛（查詢所有欄位供詳細頁面使用）
                if ($vehicles->isNotEmpty()) {
                    $vehicleIds = $vehicles->pluck('id');
                    $driverIds = DriverVehicleAssignment::whereIn('vehicle_id', $vehicleIds)
                        ->pluck('driver_id');
                    $drivers = Driver::whereIn('id', $driverIds)
                        ->with('companyCategory')
                        ->get();
                }
                break;
        }

        return [
            'drivers' => $drivers,
            'vehicles' => $vehicles,
        ];
    }

    /**
     * 取得帳務記錄
     */
    public function getRecords($driverId = null, $vehicleId = null)
    {
        $query = AccountingRecord::with(['accountDetail.mainCategory', 'accountDetail.subCategory']);

        if ($driverId) {
            $query->byDriver($driverId);
        } elseif ($vehicleId) {
            $query->byVehicle($vehicleId);
        }

        return $query->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    /**
     * 計算統計數據
     */
    public function calculateStatistics($records): array
    {
        $debitTotal = 0;
        $creditTotal = 0;

        foreach ($records as $record) {
            $debitTotal += $record->debit_amount ?? 0;
            $creditTotal += $record->credit_amount ?? 0;
        }

        $balance = $debitTotal - $creditTotal;

        return [
            'debit_total' => $debitTotal,
            'credit_total' => $creditTotal,
            'balance' => $balance,
            'balance_is_negative' => $balance < 0,
        ];
    }

    /**
     * 批次建立帳務記錄
     */
    public function batchCreate(array $data): array
    {
        $records = [];

        DB::beginTransaction();

        try {
            foreach ($data['records'] as $recordData) {
                // 檢查重複記錄（警告但允許）
                $this->checkDuplicateWarning($recordData);

                // 取得駕駛資料
                $driver = null;
                if (!empty($recordData['driver_id'])) {
                    $driver = Driver::find($recordData['driver_id']);
                }

                // 取得車輛資料
                $vehicle = null;
                if (!empty($recordData['vehicle_id'])) {
                    $vehicle = Vehicle::find($recordData['vehicle_id']);
                }

                // 建立記錄
                $record = AccountingRecord::create([
                    'driver_id' => $recordData['driver_id'] ?? null,
                    'vehicle_id' => $recordData['vehicle_id'] ?? null,
                    'account_detail_id' => $recordData['account_detail_id'],
                    'transaction_date' => $recordData['transaction_date'],
                    'driver_name' => $driver ? $driver->name : null,
                    'driver_id_number' => $driver ? $driver->id_number : null,
                    'vehicle_license_number' => $vehicle ? $vehicle->license_number : null,
                    'debit_amount' => $recordData['debit_amount'] ?? null,
                    'credit_amount' => $recordData['credit_amount'] ?? null,
                    'note' => $recordData['note'] ?? null,
                ]);

                $records[] = $record;
            }

            DB::commit();

            return $records;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 更新帳務記錄
     */
    public function update(AccountingRecord $record, array $data): AccountingRecord
    {
        DB::beginTransaction();

        try {
            // 如果更改了駕駛，更新冗余欄位
            if (isset($data['driver_id']) && $data['driver_id'] != $record->driver_id) {
                $driver = Driver::find($data['driver_id']);
                $data['driver_name'] = $driver ? $driver->name : null;
                $data['driver_id_number'] = $driver ? $driver->id_number : null;
            }

            // 如果更改了車輛，更新冗余欄位
            if (isset($data['vehicle_id']) && $data['vehicle_id'] != $record->vehicle_id) {
                $vehicle = Vehicle::find($data['vehicle_id']);
                $data['vehicle_license_number'] = $vehicle ? $vehicle->license_number : null;
            }

            $record->update($data);

            DB::commit();

            return $record->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 批次刪除（軟刪除）
     */
    public function batchDelete(array $ids): int
    {
        return AccountingRecord::whereIn('id', $ids)->delete();
    }

    /**
     * 搜尋會計科目
     */
    public function searchAccountDetails(string $keyword): array
    {
        return AccountDetail::with(['mainCategory', 'subCategory'])
            ->where(function($query) use ($keyword) {
                $query->where('account_code', 'like', "{$keyword}%")
                    ->orWhere('account_name', 'like', "%{$keyword}%");
            })
            ->where('is_active', true)
            ->orderBy('account_code')
            ->limit(20)
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'account_code' => $account->account_code,
                    'account_name' => $account->account_name,
                    'main_category_name' => $account->mainCategory->category_name,
                    'sub_category_name' => $account->subCategory->sub_category_name,
                ];
            })
            ->toArray();
    }

    /**
     * 檢查重複記錄（僅警告）
     */
    protected function checkDuplicateWarning(array $data): void
    {
        $duplicate = AccountingRecord::where('driver_id', $data['driver_id'] ?? null)
            ->where('vehicle_id', $data['vehicle_id'] ?? null)
            ->where('account_detail_id', $data['account_detail_id'])
            ->where('transaction_date', $data['transaction_date'])
            ->where('debit_amount', $data['debit_amount'] ?? null)
            ->where('credit_amount', $data['credit_amount'] ?? null)
            ->exists();

        if ($duplicate) {
            Log::warning('可能的重複帳務記錄', $data);
            // 這裡只記錄警告，不阻止新增
        }
    }
}
