<?php

namespace App\Exports;

use App\Models\AccountingRecord;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Excel;

class DailyTransactionReportExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    public string $fileName = 'daily_transaction_report.xlsx';
    public string $writerType = Excel::XLSX;

    public function __construct(
        private readonly string $startDate,
        private readonly string $endDate,
        private readonly array $filters = []
    ) {
    }

    public function query()
    {
        // 建立查詢
        $query = AccountingRecord::with([
            'driver.companyCategory',
            'vehicle.companyCategory',
            'vehicle.company',
            'accountDetail',
        ])
        ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);

        // 套用公司類別篩選
        if (!empty($this->filters['categories'])) {
            // 確保 categories 是陣列格式
            $categories = $this->ensureArray($this->filters['categories']);

            $query->where(function ($q) use ($categories) {
                $q->whereHas('vehicle', function ($vq) use ($categories) {
                    $vq->whereIn('company_category_id', $categories);
                })
                ->orWhereHas('driver', function ($dq) use ($categories) {
                    $dq->whereIn('company_category_id', $categories);
                });
            });
        }

        // 套用公司篩選
        if (!empty($this->filters['companies'])) {
            // 確保 companies 是陣列格式
            $companies = $this->ensureArray($this->filters['companies']);

            $query->where(function ($q) use ($companies) {
                // 檢查是否包含「無所屬公司」選項
                $hasNoCompany = in_array(-1, $companies) || in_array(null, $companies, true);
                $companyIds = array_filter($companies, function($id) {
                    return $id !== -1 && $id !== null;
                });

                if (!empty($companyIds)) {
                    $q->whereHas('vehicle', function ($vq) use ($companyIds) {
                        $vq->whereIn('company_id', $companyIds);
                    });
                }

                if ($hasNoCompany) {
                    $q->orWhereNull('vehicle_id')
                      ->orWhereHas('vehicle', function ($vq) {
                          $vq->whereNull('company_id');
                      });
                }
            });
        }

        // 套用會計科目篩選
        if (!empty($this->filters['accounts'])) {
            // 確保 accounts 是陣列格式
            $accounts = $this->ensureArray($this->filters['accounts']);
            $query->whereIn('account_detail_id', $accounts);
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * 確保資料為陣列格式
     * 處理字串、逗號分隔字串、陣列等多種輸入格式
     */
    private function ensureArray(mixed $value): array
    {
        // 如果已經是陣列,直接返回
        if (is_array($value)) {
            return $value;
        }

        // 如果是字串,嘗試以逗號分隔
        if (is_string($value)) {
            // 移除空白並分割
            $result = array_map('trim', explode(',', $value));
            // 過濾空值
            return array_filter($result, function($item) {
                return $item !== '' && $item !== null;
            });
        }

        // 其他類型(數字等)轉為單元素陣列
        return [$value];
    }

    public function headings(): array
    {
        return [
            '交易日期',
            '公司類別',
            '車隊編號',
            '車牌號碼',
            '駕駛姓名',
            '會計科目',
            '借方金額',
            '貸方金額',
            '備註',
        ];
    }

    public function map($record): array
    {
        // 處理公司類別顯示邏輯：優先車輛，否則駕駛
        $companyCategory = $record->vehicle && $record->vehicle->companyCategory
            ? $record->vehicle->companyCategory->name
            : ($record->driver && $record->driver->companyCategory
                ? $record->driver->companyCategory->name
                : '-');

        return [
            $record->created_at->format('Y-m-d'),
            $companyCategory,
            $record->vehicle_fleet_number ?? '-',
            $record->vehicle_license_number ?? '-',
            $record->driver_name ?? '-',
            $record->accountDetail ? $record->accountDetail->account_name : '-',
            $record->debit_amount ?? '0.00',
            $record->credit_amount ?? '0.00',
            $record->note ?? '',
        ];
    }
}
