<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use App\Models\AccountingRecord;
use App\Models\AccountDetail;
use App\Models\Company;
use App\Models\CompanyCategory;
use App\Models\ReportConfiguration;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DailyTransactionReportExport;

class DailyTransactionReportController extends Controller
{
    /**
     * 顯示每日交易明細報表
     */
    public function index(Request $request)
    {
        // 取得篩選參數
        $startDate = $request->input('start_date', now()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $filterConfigId = $request->input('filter_config_id');

        // 初始化篩選條件
        $filters = [
            'categories' => $request->input('categories', []),
            'companies' => $request->input('companies', []),
            'accounts' => $request->input('accounts', []),
        ];

        // 若有 filter_config_id，載入對應設定
        if ($filterConfigId) {
            $config = ReportConfiguration::find($filterConfigId);
            if ($config && $config->report_type === 'daily_transaction') {
                $filters = $config->filters;
            }
        }

        // 建立查詢
        $query = AccountingRecord::with([
            'driver.companyCategory',
            'vehicle.companyCategory',
            'vehicle.company',
            'accountDetail',
        ])
        ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        // 套用公司類別篩選
        if (!empty($filters['categories'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereHas('vehicle', function ($vq) use ($filters) {
                    $vq->whereIn('company_category_id', $filters['categories']);
                })
                ->orWhereHas('driver', function ($dq) use ($filters) {
                    $dq->whereIn('company_category_id', $filters['categories']);
                });
            });
        }

        // 套用公司篩選
        if (!empty($filters['companies'])) {
            $query->where(function ($q) use ($filters) {
                // 檢查是否包含「無所屬公司」選項（-1 或 null）
                $hasNoCompany = in_array(-1, $filters['companies']) || in_array(null, $filters['companies'], true);
                $companyIds = array_filter($filters['companies'], function($id) {
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
        if (!empty($filters['accounts'])) {
            $query->whereIn('account_detail_id', $filters['accounts']);
        }

        // 排序並分頁
        $records = $query->orderBy('created_at', 'desc')
                         ->paginate(50)
                         ->through(function ($record) {
                             // 處理公司類別顯示邏輯：優先車輛，否則駕駛
                             $companyCategory = $record->vehicle && $record->vehicle->companyCategory
                                 ? $record->vehicle->companyCategory->name
                                 : ($record->driver && $record->driver->companyCategory
                                     ? $record->driver->companyCategory->name
                                     : '-');

                             return [
                                 'id' => $record->id,
                                 'created_at' => $record->created_at->format('Y-m-d H:i'),
                                 'company_category' => $companyCategory,
                                 'vehicle_fleet_number' => $record->vehicle_fleet_number ?? '-',
                                 'vehicle_license_number' => $record->vehicle_license_number ?? '-',
                                 'driver_name' => $record->driver_name ?? '-',
                                 'debit_amount' => $record->debit_amount,
                                 'credit_amount' => $record->credit_amount,
                                 'note' => $record->note ?? '',
                                'account_detail_name' => $record->accountDetail ? $record->accountDetail->account_name : '-',
                             ];
                         });

        // 計算總金額（當前篩選範圍）
        $totals = AccountingRecord::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        // 套用相同的篩選條件
        if (!empty($filters['categories'])) {
            $totals->where(function ($q) use ($filters) {
                $q->whereHas('vehicle', function ($vq) use ($filters) {
                    $vq->whereIn('company_category_id', $filters['categories']);
                })
                ->orWhereHas('driver', function ($dq) use ($filters) {
                    $dq->whereIn('company_category_id', $filters['categories']);
                });
            });
        }

        if (!empty($filters['companies'])) {
            $totals->where(function ($q) use ($filters) {
                $hasNoCompany = in_array(-1, $filters['companies']) || in_array(null, $filters['companies'], true);
                $companyIds = array_filter($filters['companies'], function($id) {
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

        if (!empty($filters['accounts'])) {
            $totals->whereIn('account_detail_id', $filters['accounts']);
        }

        $totalDebit = $totals->sum('debit_amount');
        $totalCredit = $totals->sum('credit_amount');

        // 取得已儲存的報表組合
        $reportConfigs = ReportConfiguration::byType('daily_transaction')
                            ->orderBy('created_at', 'desc')
                            ->get()
                            ->map(function ($config) {
                                return [
                                    'id' => $config->id,
                                    'name' => $config->name,
                                    'filters' => $config->filters,
                                ];
                            });

        // 取得篩選器選項資料
        $options = [
            'companyCategories' => CompanyCategory::orderBy('name')->get(['id', 'name']),
            'companies' => Company::orderBy('name')->get(['id', 'name']),
            'accountDetails' => AccountDetail::with(['subCategory.mainCategory'])
                                ->orderBy('account_code')
                                ->get()
                                ->map(function ($account) {
                                    return [
                                        'id' => $account->id,
                                        'code' => $account->account_code,
                                        'name' => $account->account_name,
                                        'full_name' => $account->account_code . ' - ' . $account->account_name,
                                    ];
                                }),
        ];

        return Inertia::render('Admin/Reports/DailyTransaction/Index', [
            'records' => $records,
            'total_debit' => number_format($totalDebit, 2),
            'total_credit' => number_format($totalCredit, 2),
            'report_configs' => $reportConfigs,
            'options' => $options,
            'filters' => array_merge($filters, [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'filter_config_id' => $filterConfigId,
            ]),
            'permissions' => [
                'canExport' => auth()->user()->can('export reports'),
                'canCreateConfig' => auth()->user()->can('create report configurations'),
                'canEditConfig' => auth()->user()->can('edit report configurations'),
                'canDeleteConfig' => auth()->user()->can('delete report configurations'),
            ],
        ]);
    }

    /**
     * 預覽報表（列印格式）
     */
    public function preview(Request $request)
    {
        $startDate = $request->input('start_date', now()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // 解析 JSON 字串格式的篩選條件
        $filters = [
            'categories' => $this->parseJsonInput($request->input('categories', [])),
            'companies' => $this->parseJsonInput($request->input('companies', [])),
            'accounts' => $this->parseJsonInput($request->input('accounts', [])),
        ];

        // 建立查詢（不分頁，取得所有資料）
        $query = AccountingRecord::with([
            'driver.companyCategory',
            'vehicle.companyCategory',
            'vehicle.company',
            'accountDetail',
        ])
        ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        // 套用公司類別篩選
        if (!empty($filters['categories'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereHas('vehicle', function ($vq) use ($filters) {
                    $vq->whereIn('company_category_id', $filters['categories']);
                })
                ->orWhereHas('driver', function ($dq) use ($filters) {
                    $dq->whereIn('company_category_id', $filters['categories']);
                });
            });
        }

        // 套用公司篩選
        if (!empty($filters['companies'])) {
            $query->where(function ($q) use ($filters) {
                $hasNoCompany = in_array(-1, $filters['companies']) || in_array(null, $filters['companies'], true);
                $companyIds = array_filter($filters['companies'], function($id) {
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
        if (!empty($filters['accounts'])) {
            $query->whereIn('account_detail_id', $filters['accounts']);
        }

        // 取得所有記錄（不分頁）
        $records = $query->orderBy('created_at', 'desc')
                         ->get()
                         ->map(function ($record) {
                             $companyCategory = $record->vehicle && $record->vehicle->companyCategory
                                 ? $record->vehicle->companyCategory->name
                                 : ($record->driver && $record->driver->companyCategory
                                     ? $record->driver->companyCategory->name
                                     : '-');

                             return [
                                 'id' => $record->id,
                                 'created_at' => $record->created_at->format('Y-m-d h:i'),
                                 'company_category' => $companyCategory,
                                 'vehicle_fleet_number' => $record->vehicle_fleet_number ?? '-',
                                 'vehicle_license_number' => $record->vehicle_license_number ?? '-',
                                 'driver_name' => $record->driver_name ?? '-',
                                 'debit_amount' => $record->debit_amount,
                                 'credit_amount' => $record->credit_amount,
                                 'note' => $record->note ?? '',
                                 'account_detail_name' => $record->accountDetail ? $record->accountDetail->account_name : '-',
                             ];
                         });

        // 計算總金額
        $totalDebit = $records->sum('debit_amount');
        $totalCredit = $records->sum('credit_amount');

        // 取得篩選條件的名稱（用於顯示）
        $filterNames = [
            'categories' => CompanyCategory::whereIn('id', $filters['categories'])->pluck('name')->toArray(),
            'companies' => Company::whereIn('id', array_filter($filters['companies'], fn($id) => $id !== -1))->pluck('name')->toArray(),
            'accounts' => AccountDetail::whereIn('id', $filters['accounts'])->pluck('account_name')->toArray(),
        ];

        // 若包含「無所屬公司」選項
        if (in_array(-1, $filters['companies'])) {
            $filterNames['companies'][] = '無所屬公司';
        }

        return Inertia::render('Admin/Reports/DailyTransaction/Preview', [
            'records' => $records,
            'total_debit' => number_format($totalDebit, 2),
            'total_credit' => number_format($totalCredit, 2),
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'filter_names' => $filterNames,
            ],
            'today' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * 匯出 Excel
     */
    public function export(Request $request)
    {
        // 驗證權限
        if (!auth()->user()->can('export reports')) {
            abort(403, '無權限匯出報表');
        }

        $startDate = $request->input('start_date', now()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // 解析 JSON 字串格式的篩選條件
        $filters = [
            'categories' => $this->parseJsonInput($request->input('categories', [])),
            'companies' => $this->parseJsonInput($request->input('companies', [])),
            'accounts' => $this->parseJsonInput($request->input('accounts', [])),
        ];

        $fileName = '每日交易明細報表_' . $startDate . '_to_' . $endDate . '.xlsx';

        return Excel::download(
            new DailyTransactionReportExport($startDate, $endDate, $filters),
            $fileName
        );
    }

    /**
     * 解析 JSON 輸入或直接返回陣列
     * 處理前端可能傳遞 JSON 字串或陣列的情況
     */
    private function parseJsonInput($input)
    {
        // 如果已經是陣列,直接返回
        if (is_array($input)) {
            return $input;
        }

        // 如果是 JSON 字串,解析後返回
        if (is_string($input) && !empty($input)) {
            $decoded = json_decode($input, true);
            return is_array($decoded) ? $decoded : [];
        }

        // 其他情況返回空陣列
        return [];
    }
}
