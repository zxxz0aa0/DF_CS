<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountDetail;
use App\Models\AccountMainCategory;
use App\Models\AccountSubCategory;
use App\Models\AccountAuditLog;
use App\Http\Requests\AccountDetailRequest;
use App\Services\AccountCodeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountDetailController extends Controller
{
    /**
     * 顯示會計科目列表
     */
    public function index(Request $request)
    {
        $query = AccountDetail::with(['mainCategory', 'subCategory', 'parent']);

        // 搜尋篩選
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('main_category_id')) {
            $query->byMainCategory($request->main_category_id);
        }

        if ($request->filled('sub_category_id')) {
            $query->bySubCategory($request->sub_category_id);
        }

        if ($request->filled('account_type')) {
            $query->byAccountType($request->account_type);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $accounts = $query->ordered()->paginate(15)->withQueryString();

        // 取得篩選選項
        $mainCategories = AccountMainCategory::active()->ordered()->get();
        $subCategories = collect();

        if ($request->filled('main_category_id')) {
            $subCategories = AccountSubCategory::byMainCategory($request->main_category_id)
                ->active()
                ->ordered()
                ->get();
        }

        return Inertia::render('Admin/Accounts/Details/Index', [
            'accounts' => $accounts,
            'mainCategories' => $mainCategories,
            'subCategories' => $subCategories,
            'filters' => $request->only(['search', 'main_category_id', 'sub_category_id', 'account_type', 'is_active']),
        ]);
    }

    /**
     * 顯示建立會計科目表單
     */
    public function create()
    {
        $mainCategories = AccountMainCategory::active()->ordered()->get();

        return Inertia::render('Admin/Accounts/Details/Create', [
            'mainCategories' => $mainCategories,
        ]);
    }

    /**
     * 顯示匯入科目的頁面
     */
    public function importForm()
    {
        return Inertia::render('Admin/Accounts/Details/Import', [
            'importSummary' => session('importSummary'),
        ]);
    }

    /**
     * 儲存新的會計科目
     */
    public function store(AccountDetailRequest $request)
    {
        $validated = $request->validated();

        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        // 如果有父級科目，自動設置層級
        $parentId = $validated['parent_id'] ?? null;
        if ($parentId) {
            $parent = AccountDetail::find($parentId);
            $validated['level'] = ($parent->level ?? 0) + 1;
        } else {
            $validated['parent_id'] = null;
            $validated['level'] = $validated['level'] ?? 1;
        }

        $account = AccountDetail::create($validated);

        // 記錄審計日誌
        AccountAuditLog::log($account, 'created', null, $validated);

        return redirect()->route('admin.accounts.account.details.index')
            ->with('success', '會計科目建立成功');
    }

    /**
     * 顯示會計科目詳細資訊
     */
    public function show(AccountDetail $detail)
    {
        $detail->load(['mainCategory', 'subCategory', 'parent', 'children', 'createdBy', 'updatedBy']);

        return Inertia::render('Admin/Accounts/Details/Show', [
            'account' => $detail,
        ]);
    }

    /**
     * 顯示編輯會計科目表單
     */
    public function edit(AccountDetail $detail)
    {
        $detail->load(['mainCategory', 'subCategory', 'parent']);

        $mainCategories = AccountMainCategory::active()->ordered()->get();
        $subCategories = AccountSubCategory::byMainCategory($detail->main_category_id)
            ->active()
            ->ordered()
            ->get();

        return Inertia::render('Admin/Accounts/Details/Edit', [
            'account' => $detail,
            'mainCategories' => $mainCategories,
            'subCategories' => $subCategories,
        ]);
    }

    /**
     * 更新會計科目
     */
    public function update(AccountDetailRequest $request, AccountDetail $detail)
    {
        $validated = $request->validated();

        $oldValues = $detail->toArray();
        $validated['updated_by'] = auth()->id();

        // 如果有父級科目，自動設置層級
        $parentId = $validated['parent_id'] ?? null;
        if ($parentId) {
            $parent = AccountDetail::find($parentId);
            $validated['level'] = ($parent->level ?? 0) + 1;
        } else {
            $validated['parent_id'] = null;
            $validated['level'] = $validated['level'] ?? 1;
        }

        $detail->update($validated);

        // 記錄審計日誌
        AccountAuditLog::log($detail, 'updated', $oldValues, $validated);

        return redirect()->route('admin.accounts.account.details.index')
            ->with('success', '會計科目更新成功');
    }

    /**
     * 刪除會計科目
     */
    public function destroy(AccountDetail $detail)
    {
        // 檢查是否有子科目
        if ($detail->hasChildren()) {
            return back()->with('error', '此科目底下還有子科目，無法刪除');
        }

        $oldValues = $detail->toArray();

        // 記錄審計日誌
        AccountAuditLog::log($detail, 'deleted', $oldValues, null);

        $detail->delete();

        return redirect()->route('admin.accounts.account.details.index')
            ->with('success', '會計科目刪除成功');
    }

    /**
     * 匯出會計科目 (尚未實作)
     */
    public function export()
    {
        return redirect()
            ->route('admin.accounts.account.details.index')
            ->with('error', '匯出功能尚未實作');
    }

    /**
     * 匯入會計科目檔案 (暫時僅驗證檔案)
     */
    public function import(Request $request)
    {
        $validated = $request->validate([
            'import_file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
            'overwrite_existing' => ['nullable', 'boolean'],
            'skip_inactive' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('import_file');

        $summary = [
            'success' => false,
            'message' => '匯入功能尚未完成，已驗證檔案格式。',
            'details' => [
                '檔案名稱：' . $file->getClientOriginalName(),
                '覆寫既有資料：' . ($request->boolean('overwrite_existing') ? '是' : '否'),
                '略過停用資料：' . ($request->boolean('skip_inactive') ? '是' : '否'),
            ],
        ];

        return redirect()
            ->route('admin.accounts.account.details.import')
            ->with('importSummary', $summary);
    }

    /**
     * 下載匯入範本
     */
    public function template()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="account_details_template.csv"',
        ];

        $columns = [
            'main_category_code',
            'sub_category_code',
            'account_code',
            'account_name',
            'account_name_en',
            'account_type',
            'debit_credit',
            'description',
            'notes',
            'is_active',
        ];

        $callback = static function () use ($columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);
            fclose($handle);
        };

        return response()->streamDownload($callback, 'account_details_template.csv', $headers);
    }

    /**
     * 切換科目狀態
     */
    public function toggleStatus(AccountDetail $detail)
    {
        $oldValues = $detail->toArray();

        $detail->update([
            'is_active' => !$detail->is_active,
            'updated_by' => auth()->id(),
        ]);

        $newValues = $detail->toArray();

        // 記錄審計日誌
        AccountAuditLog::log($detail, 'updated', $oldValues, $newValues);

        $status = $detail->is_active ? '啟用' : '停用';

        return back()->with('success', "科目已{$status}");
    }

    /**
     * API: 取得子分類選項
     */
    public function getSubCategories($mainCategoryId)
    {
        $subCategories = AccountSubCategory::byMainCategory($mainCategoryId)
            ->active()
            ->ordered()
            ->get();

        return response()->json($subCategories);
    }

    /**
     * API: 驗證科目編號
     */
    public function validateCode(Request $request)
    {
        $exists = AccountDetail::where('account_code', $request->code)
            ->when($request->account_id, function ($query, $accountId) {
                $query->where('id', '!=', $accountId);
            })
            ->exists();

        return response()->json([
            'valid' => !$exists,
            'message' => $exists ? '科目編號已存在' : '科目編號可用'
        ]);
    }

    /**
     * API: 取得下一個可用編號
     */
    public function getNextCode(Request $request, AccountCodeService $codeService)
    {
        try {
            $nextCode = $codeService->generateNextCode(
                $request->main_category_id,
                $request->sub_category_id
            );

            $guidance = $codeService->getCodeFormatGuidance(
                $request->main_category_id,
                $request->sub_category_id
            );

            return response()->json([
                'code' => $nextCode,
                'guidance' => $guidance
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
