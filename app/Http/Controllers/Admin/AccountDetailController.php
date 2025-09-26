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
     * 儲存新的會計科目
     */
    public function store(AccountDetailRequest $request)
    {
        $validated = $request->validated();

        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        // 如果有父級科目，自動設置層級
        if ($validated['parent_id']) {
            $parent = AccountDetail::find($validated['parent_id']);
            $validated['level'] = $parent->level + 1;
        }

        $account = AccountDetail::create($validated);

        // 記錄審計日誌
        AccountAuditLog::log($account, 'created', null, $validated);

        return redirect()->route('admin.account-details.index')
            ->with('success', '會計科目建立成功');
    }

    /**
     * 顯示會計科目詳細資訊
     */
    public function show(AccountDetail $accountDetail)
    {
        $accountDetail->load(['mainCategory', 'subCategory', 'parent', 'children', 'createdBy', 'updatedBy']);

        return Inertia::render('Admin/Accounts/Details/Show', [
            'account' => $accountDetail,
        ]);
    }

    /**
     * 顯示編輯會計科目表單
     */
    public function edit(AccountDetail $accountDetail)
    {
        $accountDetail->load(['mainCategory', 'subCategory', 'parent']);

        $mainCategories = AccountMainCategory::active()->ordered()->get();
        $subCategories = AccountSubCategory::byMainCategory($accountDetail->main_category_id)
            ->active()
            ->ordered()
            ->get();

        return Inertia::render('Admin/Accounts/Details/Edit', [
            'account' => $accountDetail,
            'mainCategories' => $mainCategories,
            'subCategories' => $subCategories,
        ]);
    }

    /**
     * 更新會計科目
     */
    public function update(AccountDetailRequest $request, AccountDetail $accountDetail)
    {
        $validated = $request->validated();

        $oldValues = $accountDetail->toArray();
        $validated['updated_by'] = auth()->id();

        // 如果有父級科目，自動設置層級
        if ($validated['parent_id']) {
            $parent = AccountDetail::find($validated['parent_id']);
            $validated['level'] = $parent->level + 1;
        }

        $accountDetail->update($validated);

        // 記錄審計日誌
        AccountAuditLog::log($accountDetail, 'updated', $oldValues, $validated);

        return redirect()->route('admin.account-details.index')
            ->with('success', '會計科目更新成功');
    }

    /**
     * 刪除會計科目
     */
    public function destroy(AccountDetail $accountDetail)
    {
        // 檢查是否有子科目
        if ($accountDetail->hasChildren()) {
            return back()->with('error', '此科目底下還有子科目，無法刪除');
        }

        $oldValues = $accountDetail->toArray();

        // 記錄審計日誌
        AccountAuditLog::log($accountDetail, 'deleted', $oldValues, null);

        $accountDetail->delete();

        return redirect()->route('admin.account-details.index')
            ->with('success', '會計科目刪除成功');
    }

    /**
     * 切換科目狀態
     */
    public function toggleStatus(AccountDetail $accountDetail)
    {
        $oldValues = $accountDetail->toArray();

        $accountDetail->update([
            'is_active' => !$accountDetail->is_active,
            'updated_by' => auth()->id(),
        ]);

        $newValues = $accountDetail->toArray();

        // 記錄審計日誌
        AccountAuditLog::log($accountDetail, 'updated', $oldValues, $newValues);

        $status = $accountDetail->is_active ? '啟用' : '停用';

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