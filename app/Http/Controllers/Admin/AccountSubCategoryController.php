<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountMainCategory;
use App\Models\AccountSubCategory;
use App\Models\AccountAuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountSubCategoryController extends Controller
{
    /**
     * 顯示會計子分類列表
     */
    public function index(Request $request)
    {
        $query = AccountSubCategory::with(['mainCategory'])
                                   ->withCount('accountDetails');

        // 搜尋篩選
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('sub_category_code', 'like', '%' . $request->search . '%')
                  ->orWhere('sub_category_name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('main_category_id')) {
            $query->where('main_category_id', $request->main_category_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $subCategories = $query->orderBy('main_category_id')
                              ->orderBy('sort_order')
                              ->orderBy('sub_category_code')
                              ->paginate(15)
                              ->withQueryString();

        // 取得篩選選項
        $mainCategories = AccountMainCategory::active()->ordered()->get();

        return Inertia::render('Admin/Accounts/SubCategories/Index', [
            'subCategories' => $subCategories,
            'mainCategories' => $mainCategories,
            'filters' => $request->only(['search', 'main_category_id', 'is_active']),
        ]);
    }

    /**
     * 顯示建立會計子分類表單
     */
    public function create()
    {
        $mainCategories = AccountMainCategory::active()->ordered()->get();

        return Inertia::render('Admin/Accounts/SubCategories/Create', [
            'mainCategories' => $mainCategories,
        ]);
    }

    /**
     * 儲存新的會計子分類
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'main_category_id' => 'required|exists:account_main_categories,id',
            'sub_category_code' => 'required|string|max:10',
            'sub_category_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ], [
            'main_category_id.required' => '請選擇所屬總類',
            'main_category_id.exists' => '選擇的總類不存在',
            'sub_category_code.required' => '子分類代碼為必填項目',
            'sub_category_name.required' => '子分類名稱為必填項目',
        ]);

        // 檢查子分類代碼在同一總類下是否唯一
        $exists = AccountSubCategory::where('main_category_id', $validated['main_category_id'])
                                   ->where('sub_category_code', $validated['sub_category_code'])
                                   ->exists();

        if ($exists) {
            return back()->withErrors([
                'sub_category_code' => '在此總類下，子分類代碼已存在'
            ])->withInput();
        }

        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $subCategory = AccountSubCategory::create($validated);

        // 記錄審計日誌
        AccountAuditLog::create([
            'auditable_type' => AccountSubCategory::class,
            'auditable_id' => $subCategory->id,
            'event' => 'created',
            'old_values' => null,
            'new_values' => json_encode($validated),
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'url' => request()->fullUrl(),
        ]);

        return redirect()->route('admin.accounts.sub-categories.index')
            ->with('success', '會計子分類建立成功');
    }

    /**
     * 顯示會計子分類詳細資訊
     */
    public function show(AccountSubCategory $subCategory)
    {
        $subCategory->load(['mainCategory', 'accountDetails', 'createdBy', 'updatedBy']);
        $subCategory->loadCount('accountDetails');

        return Inertia::render('Admin/Accounts/SubCategories/Show', [
            'subCategory' => $subCategory,
        ]);
    }

    /**
     * 顯示編輯會計子分類表單
     */
    public function edit(AccountSubCategory $subCategory)
    {
        $subCategory->load('mainCategory');
        $mainCategories = AccountMainCategory::active()->ordered()->get();

        return Inertia::render('Admin/Accounts/SubCategories/Edit', [
            'subCategory' => $subCategory,
            'mainCategories' => $mainCategories,
        ]);
    }

    /**
     * 更新會計子分類
     */
    public function update(Request $request, AccountSubCategory $subCategory)
    {
        $validated = $request->validate([
            'main_category_id' => 'required|exists:account_main_categories,id',
            'sub_category_code' => 'required|string|max:10',
            'sub_category_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ], [
            'main_category_id.required' => '請選擇所屬總類',
            'main_category_id.exists' => '選擇的總類不存在',
            'sub_category_code.required' => '子分類代碼為必填項目',
            'sub_category_name.required' => '子分類名稱為必填項目',
        ]);

        // 檢查子分類代碼在同一總類下是否唯一（排除自己）
        $exists = AccountSubCategory::where('main_category_id', $validated['main_category_id'])
                                   ->where('sub_category_code', $validated['sub_category_code'])
                                   ->where('id', '!=', $subCategory->id)
                                   ->exists();

        if ($exists) {
            return back()->withErrors([
                'sub_category_code' => '在此總類下，子分類代碼已存在'
            ])->withInput();
        }

        $oldValues = $subCategory->toArray();
        $validated['updated_by'] = auth()->id();

        $subCategory->update($validated);

        // 記錄審計日誌
        AccountAuditLog::create([
            'auditable_type' => AccountSubCategory::class,
            'auditable_id' => $subCategory->id,
            'event' => 'updated',
            'old_values' => json_encode($oldValues),
            'new_values' => json_encode($validated),
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'url' => request()->fullUrl(),
        ]);

        return redirect()->route('admin.accounts.sub-categories.index')
            ->with('success', '會計子分類更新成功');
    }

    /**
     * 刪除會計子分類
     */
    public function destroy(AccountSubCategory $subCategory)
    {
        // 檢查是否有會計科目
        if ($subCategory->accountDetails()->count() > 0) {
            return back()->with('error', '此子分類底下還有會計科目，無法刪除');
        }

        $oldValues = $subCategory->toArray();

        // 記錄審計日誌
        AccountAuditLog::create([
            'auditable_type' => AccountSubCategory::class,
            'auditable_id' => $subCategory->id,
            'event' => 'deleted',
            'old_values' => json_encode($oldValues),
            'new_values' => null,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'url' => request()->fullUrl(),
        ]);

        $subCategory->delete();

        return redirect()->route('admin.accounts.sub-categories.index')
            ->with('success', '會計子分類刪除成功');
    }

    /**
     * 切換子分類狀態
     */
    public function toggleStatus(AccountSubCategory $subCategory)
    {
        $oldValues = $subCategory->toArray();

        $subCategory->update([
            'is_active' => !$subCategory->is_active,
            'updated_by' => auth()->id(),
        ]);

        $newValues = $subCategory->toArray();

        // 記錄審計日誌
        AccountAuditLog::create([
            'auditable_type' => AccountSubCategory::class,
            'auditable_id' => $subCategory->id,
            'event' => 'updated',
            'old_values' => json_encode($oldValues),
            'new_values' => json_encode($newValues),
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'url' => request()->fullUrl(),
        ]);

        $status = $subCategory->is_active ? '啟用' : '停用';

        return back()->with('success', "子分類已{$status}");
    }

    /**
     * 顯示匯入表單
     */
    public function import()
    {
        $mainCategories = AccountMainCategory::active()->ordered()->get();

        return Inertia::render('Admin/Accounts/SubCategories/Import', [
            'mainCategories' => $mainCategories,
        ]);
    }

    /**
     * 處理匯入邏輯
     */
    public function importStore(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB
            'update_existing' => 'boolean',
        ]);

        try {
            $file = $request->file('file');
            $updateExisting = $request->boolean('update_existing', false);

            // 讀取檔案內容
            $data = $this->parseImportFile($file);

            $results = [
                'total' => count($data),
                'success' => 0,
                'errors' => 0,
                'skipped' => 0,
                'error_details' => [],
            ];

            foreach ($data as $index => $row) {
                try {
                    $rowNumber = $index + 2; // 從第2行開始（第1行是標題）

                    // 驗證必要欄位
                    if (empty($row['main_category_code']) || empty($row['sub_category_code']) || empty($row['sub_category_name'])) {
                        $results['errors']++;
                        $results['error_details'][] = "第 {$rowNumber} 行：缺少必要欄位";
                        continue;
                    }

                    // 檢查主分類是否存在
                    $mainCategory = AccountMainCategory::where('category_code', $row['main_category_code'])->first();
                    if (!$mainCategory) {
                        $results['errors']++;
                        $results['error_details'][] = "第 {$rowNumber} 行：主分類代碼 '{$row['main_category_code']}' 不存在";
                        continue;
                    }

                    // 檢查子分類是否已存在
                    $existingSubCategory = AccountSubCategory::where('sub_category_code', $row['sub_category_code'])
                        ->where('main_category_id', $mainCategory->id)
                        ->first();

                    if ($existingSubCategory) {
                        if ($updateExisting) {
                            // 更新現有記錄
                            $oldValues = $existingSubCategory->toArray();

                            $existingSubCategory->update([
                                'sub_category_name' => $row['sub_category_name'],
                                'description' => $row['description'] ?? '',
                                'sort_order' => $row['sort_order'] ?? 0,
                                'is_active' => $this->parseBooleanValue($row['is_active'] ?? 'true'),
                                'updated_by' => auth()->id(),
                            ]);

                            // 記錄審計日誌
                            AccountAuditLog::create([
                                'auditable_type' => AccountSubCategory::class,
                                'auditable_id' => $existingSubCategory->id,
                                'event' => 'updated',
                                'old_values' => json_encode($oldValues),
                                'new_values' => json_encode($existingSubCategory->toArray()),
                                'user_id' => auth()->id(),
                                'ip_address' => request()->ip(),
                                'user_agent' => request()->header('User-Agent'),
                                'url' => request()->fullUrl(),
                            ]);

                            $results['success']++;
                        } else {
                            $results['skipped']++;
                            $results['error_details'][] = "第 {$rowNumber} 行：子分類代碼 '{$row['sub_category_code']}' 已存在（跳過）";
                        }
                    } else {
                        // 創建新記錄
                        $subCategory = AccountSubCategory::create([
                            'main_category_id' => $mainCategory->id,
                            'sub_category_code' => $row['sub_category_code'],
                            'sub_category_name' => $row['sub_category_name'],
                            'description' => $row['description'] ?? '',
                            'sort_order' => $row['sort_order'] ?? 0,
                            'is_active' => $this->parseBooleanValue($row['is_active'] ?? 'true'),
                            'created_by' => auth()->id(),
                            'updated_by' => auth()->id(),
                        ]);

                        // 記錄審計日誌
                        AccountAuditLog::create([
                            'auditable_type' => AccountSubCategory::class,
                            'auditable_id' => $subCategory->id,
                            'event' => 'created',
                            'old_values' => null,
                            'new_values' => json_encode($subCategory->toArray()),
                            'user_id' => auth()->id(),
                            'ip_address' => request()->ip(),
                            'user_agent' => request()->header('User-Agent'),
                            'url' => request()->fullUrl(),
                        ]);

                        $results['success']++;
                    }
                } catch (\Exception $e) {
                    $results['errors']++;
                    $results['error_details'][] = "第 " . ($index + 2) . " 行：" . $e->getMessage();
                }
            }

            $message = "匯入完成！成功：{$results['success']} 筆，錯誤：{$results['errors']} 筆，跳過：{$results['skipped']} 筆";

            return redirect()->route('admin.accounts.sub-categories.index')
                ->with('success', $message)
                ->with('import_results', $results);

        } catch (\Exception $e) {
            return back()->with('error', '匯入失敗：' . $e->getMessage());
        }
    }

    /**
     * 下載匯入模板
     */
    public function template()
    {
        $headers = [
            'main_category_code' => '主分類代碼',
            'sub_category_code' => '子分類代碼',
            'sub_category_name' => '子分類名稱',
            'description' => '說明',
            'sort_order' => '排序',
            'is_active' => '狀態',
        ];

        $sampleData = [
            [
                'main_category_code' => '1000',
                'sub_category_code' => '1100',
                'sub_category_name' => '流動資產',
                'description' => '一年內可變現的資產',
                'sort_order' => 1,
                'is_active' => 'true',
            ],
            [
                'main_category_code' => '1000',
                'sub_category_code' => '1200',
                'sub_category_name' => '非流動資產',
                'description' => '超過一年才能變現的資產',
                'sort_order' => 2,
                'is_active' => 'true',
            ],
        ];

        return $this->generateExcelTemplate($headers, $sampleData, '會計子分類匯入模板');
    }

    /**
     * 匯出會計子分類
     */
    public function export()
    {
        $subCategories = AccountSubCategory::with(['mainCategory'])
            ->orderBy('main_category_id')
            ->orderBy('sort_order')
            ->orderBy('sub_category_code')
            ->get();

        $data = $subCategories->map(function ($subCategory) {
            return [
                'main_category_code' => $subCategory->mainCategory->category_code,
                'main_category_name' => $subCategory->mainCategory->category_name,
                'sub_category_code' => $subCategory->sub_category_code,
                'sub_category_name' => $subCategory->sub_category_name,
                'description' => $subCategory->description,
                'sort_order' => $subCategory->sort_order,
                'is_active' => $subCategory->is_active ? 'true' : 'false',
                'created_at' => $subCategory->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $subCategory->updated_at?->format('Y-m-d H:i:s'),
            ];
        });

        $headers = [
            'main_category_code' => '主分類代碼',
            'main_category_name' => '主分類名稱',
            'sub_category_code' => '子分類代碼',
            'sub_category_name' => '子分類名稱',
            'description' => '說明',
            'sort_order' => '排序',
            'is_active' => '狀態',
            'created_at' => '建立時間',
            'updated_at' => '更新時間',
        ];

        return $this->generateExcelExport($data, $headers, '會計子分類清單');
    }

    /**
     * 解析匯入檔案
     */
    private function parseImportFile($file)
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'csv') {
            return $this->parseCsvFile($file);
        } else {
            return $this->parseExcelFile($file);
        }
    }

    /**
     * 解析 CSV 檔案
     */
    private function parseCsvFile($file)
    {
        $data = [];
        $handle = fopen($file->getRealPath(), 'r');

        // 讀取標題行
        $headers = fgetcsv($handle);
        $headers = array_map('trim', $headers);

        // 建立欄位對應
        $fieldMapping = [
            '主分類代碼' => 'main_category_code',
            'main_category_code' => 'main_category_code',
            '子分類代碼' => 'sub_category_code',
            'sub_category_code' => 'sub_category_code',
            '子分類名稱' => 'sub_category_name',
            'sub_category_name' => 'sub_category_name',
            '說明' => 'description',
            'description' => 'description',
            '排序' => 'sort_order',
            'sort_order' => 'sort_order',
            '狀態' => 'is_active',
            'is_active' => 'is_active',
        ];

        while (($row = fgetcsv($handle)) !== false) {
            $rowData = [];
            foreach ($headers as $index => $header) {
                $fieldName = $fieldMapping[$header] ?? $header;
                $rowData[$fieldName] = isset($row[$index]) ? trim($row[$index]) : '';
            }
            $data[] = $rowData;
        }

        fclose($handle);
        return $data;
    }

    /**
     * 解析 Excel 檔案
     */
    private function parseExcelFile($file)
    {
        // 這裡需要安裝 PhpSpreadsheet 套件
        // 暫時返回空陣列，實際實作需要使用 PhpSpreadsheet
        return [];
    }

    /**
     * 生成 Excel 匯出檔案
     */
    private function generateExcelExport($data, $headers, $filename)
    {
        // 暫時使用 CSV 格式
        return $this->generateCsvExport($data, $headers, $filename);
    }

    /**
     * 生成 Excel 模板
     */
    private function generateExcelTemplate($headers, $sampleData, $filename)
    {
        // 暫時使用 CSV 格式
        return $this->generateCsvTemplate($headers, $sampleData, $filename);
    }

    /**
     * 生成 CSV 匯出檔案
     */
    private function generateCsvExport($data, $headers, $filename)
    {
        $csvData = [];

        // 添加標題行
        $csvData[] = array_values($headers);

        // 添加資料行
        foreach ($data as $row) {
            $csvRow = [];
            foreach (array_keys($headers) as $key) {
                $csvRow[] = $row[$key] ?? '';
            }
            $csvData[] = $csvRow;
        }

        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');

            // 添加 BOM 以支援中文
            fwrite($file, "\xEF\xBB\xBF");

            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ]);
    }

    /**
     * 生成 CSV 模板
     */
    private function generateCsvTemplate($headers, $sampleData, $filename)
    {
        $csvData = [];

        // 添加標題行
        $csvData[] = array_values($headers);

        // 添加範例資料
        foreach ($sampleData as $row) {
            $csvRow = [];
            foreach (array_keys($headers) as $key) {
                $csvRow[] = $row[$key] ?? '';
            }
            $csvData[] = $csvRow;
        }

        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');

            // 添加 BOM 以支援中文
            fwrite($file, "\xEF\xBB\xBF");

            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ]);
    }

    /**
     * 解析布林值
     */
    private function parseBooleanValue($value)
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = strtolower(trim($value));
        return in_array($value, ['true', '1', 'yes', 'on', '是', '啟用', '真']);
    }

    /**
     * API: 根據總類取得子分類
     */
    public function getByMainCategory($mainCategoryId)
    {
        $subCategories = AccountSubCategory::where('main_category_id', $mainCategoryId)
                                          ->active()
                                          ->ordered()
                                          ->get();

        return response()->json($subCategories);
    }
}