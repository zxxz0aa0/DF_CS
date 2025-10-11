<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountDetail;
use App\Models\Driver;
use App\Models\RecurringCostTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecurringCostController extends Controller
{
    /**
     * 列表頁
     */
    public function index(Request $request)
    {
        $templates = RecurringCostTemplate::query()
            ->with(['items.accountDetail', 'creator'])
            ->when($request->search, function ($query, $search) {
                $query->search($search);
            })
            ->withCount('drivers')
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/RecurringCosts/Index', [
            'templates' => $templates,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * 新增頁面
     */
    public function create()
    {
        $accountDetails = AccountDetail::active()
            ->ordered()
            ->get(['id', 'account_code', 'account_name']);

        return Inertia::render('Admin/RecurringCosts/Create', [
            'accountDetails' => $accountDetails,
        ]);
    }

    /**
     * 儲存新增
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.account_detail_id' => 'required|exists:account_details,id',
            'items.*.amount' => 'required|integer|min:0',
            'items.*.note' => 'nullable|string|max:255',
        ]);

        $template = RecurringCostTemplate::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'created_by' => auth()->id(),
        ]);

        foreach ($validated['items'] as $index => $item) {
            $template->items()->create([
                'account_detail_id' => $item['account_detail_id'],
                'amount' => $item['amount'],
                'note' => $item['note'] ?? null,
                'sort_order' => $index,
            ]);
        }

        // 計算並更新總金額
        $template->update(['total_amount' => $template->calculateTotalAmount()]);

        return redirect()->route('admin.recurring-costs.index')
            ->with('success', '經常性費用組合已建立');
    }

    /**
     * 編輯頁面
     */
    public function edit(RecurringCostTemplate $recurringCost)
    {
        $recurringCost->load('items.accountDetail');

        $accountDetails = AccountDetail::active()
            ->ordered()
            ->get(['id', 'account_code', 'account_name']);

        return Inertia::render('Admin/RecurringCosts/Edit', [
            'template' => $recurringCost,
            'accountDetails' => $accountDetails,
        ]);
    }

    /**
     * 更新
     */
    public function update(Request $request, RecurringCostTemplate $recurringCost)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.account_detail_id' => 'required|exists:account_details,id',
            'items.*.amount' => 'required|integer|min:0',
            'items.*.note' => 'nullable|string|max:255',
        ]);

        $recurringCost->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'updated_by' => auth()->id(),
        ]);

        // 刪除舊項目，建立新項目
        $recurringCost->items()->delete();

        foreach ($validated['items'] as $index => $item) {
            $recurringCost->items()->create([
                'account_detail_id' => $item['account_detail_id'],
                'amount' => $item['amount'],
                'note' => $item['note'] ?? null,
                'sort_order' => $index,
            ]);
        }

        // 更新總金額
        $recurringCost->update(['total_amount' => $recurringCost->calculateTotalAmount()]);

        return redirect()->route('admin.recurring-costs.index')
            ->with('success', '經常性費用組合已更新');
    }

    /**
     * 刪除
     */
    public function destroy(RecurringCostTemplate $recurringCost)
    {
        $recurringCost->delete();

        return redirect()->route('admin.recurring-costs.index')
            ->with('success', '經常性費用組合已刪除');
    }

    /**
     * 批量套用
     */
    public function batchApply(Request $request)
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:recurring_cost_templates,id',
            'driver_ids' => 'required|array|min:1',
            'driver_ids.*' => 'exists:drivers,id',
        ]);

        Driver::whereIn('id', $validated['driver_ids'])
            ->update(['recurring_cost_id' => $validated['template_id']]);

        $count = count($validated['driver_ids']);

        return back()->with('success', "已成功將組合套用至 {$count} 位駕駛");
    }
}
