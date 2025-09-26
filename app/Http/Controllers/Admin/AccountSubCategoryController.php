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
     * 匯出會計子分類
     */
    public function export()
    {
        // TODO: 實作匯出功能
        return back()->with('info', '匯出功能開發中');
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