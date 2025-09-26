<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountMainCategory;
use App\Models\AccountAuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountMainCategoryController extends Controller
{
    /**
     * 顯示會計總類列表
     */
    public function index(Request $request)
    {
        $query = AccountMainCategory::withCount('subCategories');

        // 搜尋篩選
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('category_code', 'like', '%' . $request->search . '%')
                  ->orWhere('category_name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $categories = $query->orderBy('sort_order')
                           ->orderBy('category_code')
                           ->paginate(15)
                           ->withQueryString();

        return Inertia::render('Admin/Accounts/MainCategories/Index', [
            'categories' => $categories,
            'filters' => $request->only(['search', 'is_active']),
        ]);
    }

    /**
     * 顯示建立會計總類表單
     */
    public function create()
    {
        return Inertia::render('Admin/Accounts/MainCategories/Create');
    }

    /**
     * 儲存新的會計總類
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_code' => 'required|string|max:10|unique:account_main_categories',
            'category_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ], [
            'category_code.required' => '總類代碼為必填項目',
            'category_code.unique' => '總類代碼已存在',
            'category_name.required' => '總類名稱為必填項目',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $category = AccountMainCategory::create($validated);

        // 記錄審計日誌
        AccountAuditLog::create([
            'auditable_type' => AccountMainCategory::class,
            'auditable_id' => $category->id,
            'event' => 'created',
            'old_values' => null,
            'new_values' => json_encode($validated),
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'url' => request()->fullUrl(),
        ]);

        return redirect()->route('admin.accounts.main-categories.index')
            ->with('success', '會計總類建立成功');
    }

    /**
     * 顯示會計總類詳細資訊
     */
    public function show(AccountMainCategory $mainCategory)
    {
        $mainCategory->load(['subCategories', 'createdBy', 'updatedBy']);
        $mainCategory->loadCount('subCategories');

        return Inertia::render('Admin/Accounts/MainCategories/Show', [
            'category' => $mainCategory,
        ]);
    }

    /**
     * 顯示編輯會計總類表單
     */
    public function edit(AccountMainCategory $mainCategory)
    {
        return Inertia::render('Admin/Accounts/MainCategories/Edit', [
            'category' => $mainCategory,
        ]);
    }

    /**
     * 更新會計總類
     */
    public function update(Request $request, AccountMainCategory $mainCategory)
    {
        $validated = $request->validate([
            'category_code' => 'required|string|max:10|unique:account_main_categories,category_code,' . $mainCategory->id,
            'category_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ], [
            'category_code.required' => '總類代碼為必填項目',
            'category_code.unique' => '總類代碼已存在',
            'category_name.required' => '總類名稱為必填項目',
        ]);

        $oldValues = $mainCategory->toArray();
        $validated['updated_by'] = auth()->id();

        $mainCategory->update($validated);

        // 記錄審計日誌
        AccountAuditLog::create([
            'auditable_type' => AccountMainCategory::class,
            'auditable_id' => $mainCategory->id,
            'event' => 'updated',
            'old_values' => json_encode($oldValues),
            'new_values' => json_encode($validated),
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'url' => request()->fullUrl(),
        ]);

        return redirect()->route('admin.accounts.main-categories.index')
            ->with('success', '會計總類更新成功');
    }

    /**
     * 刪除會計總類
     */
    public function destroy(AccountMainCategory $mainCategory)
    {
        // 檢查是否有子分類
        if ($mainCategory->subCategories()->count() > 0) {
            return back()->with('error', '此總類底下還有子分類，無法刪除');
        }

        $oldValues = $mainCategory->toArray();

        // 記錄審計日誌
        AccountAuditLog::create([
            'auditable_type' => AccountMainCategory::class,
            'auditable_id' => $mainCategory->id,
            'event' => 'deleted',
            'old_values' => json_encode($oldValues),
            'new_values' => null,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'url' => request()->fullUrl(),
        ]);

        $mainCategory->delete();

        return redirect()->route('admin.accounts.main-categories.index')
            ->with('success', '會計總類刪除成功');
    }

    /**
     * 切換總類狀態
     */
    public function toggleStatus(AccountMainCategory $mainCategory)
    {
        $oldValues = $mainCategory->toArray();

        $mainCategory->update([
            'is_active' => !$mainCategory->is_active,
            'updated_by' => auth()->id(),
        ]);

        $newValues = $mainCategory->toArray();

        // 記錄審計日誌
        AccountAuditLog::create([
            'auditable_type' => AccountMainCategory::class,
            'auditable_id' => $mainCategory->id,
            'event' => 'updated',
            'old_values' => json_encode($oldValues),
            'new_values' => json_encode($newValues),
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'url' => request()->fullUrl(),
        ]);

        $status = $mainCategory->is_active ? '啟用' : '停用';

        return back()->with('success', "總類已{$status}");
    }

    /**
     * 匯出會計總類
     */
    public function export()
    {
        // TODO: 實作匯出功能
        return back()->with('info', '匯出功能開發中');
    }
}