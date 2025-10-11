<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyCategory;
use App\Models\DriverBalanceSummary;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CollectionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view collections');
    }

    /**
     * 顯示催帳管理頁面
     */
    public function index(Request $request)
    {
        $query = DriverBalanceSummary::query()
            ->debtors() // 只顯示有欠款的駕駛
            ->with('driver:id,name,mobile_phone1,status,company_category_id')
            ->with('driver.companyCategory:id,name');

        // 搜尋功能
        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        // 公司種類篩選
        if ($request->has('company_category_id') && $request->company_category_id) {
            $query->byCompanyCategory($request->company_category_id);
        }

        // 排序（預設依欠款金額由多到少）
        $sortBy = $request->get('sort_by', 'balance');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $debtors = $query->paginate(20);

        // 取得所有公司種類
        $companyCategories = CompanyCategory::orderBy('name')->get();

        // 統計資訊
        $statistics = [
            'total_debtors' => DriverBalanceSummary::debtors()->count(),
            'total_debt_amount' => DriverBalanceSummary::debtors()->sum('balance'),
        ];

        return Inertia::render('Admin/Collections/Index', [
            'debtors' => $debtors,
            'statistics' => $statistics,
            'companyCategories' => $companyCategories,
            'filters' => $request->only(['search', 'company_category_id', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * 查看駕駛的詳細帳務記錄
     */
    public function show(int $driverId)
    {
        $summary = DriverBalanceSummary::with('driver')->findOrFail($driverId);

        // 重導向到帳務管理頁面，並帶上駕駛篩選
        return redirect()->route('admin.accounting.records.index', [
            'driver_id' => $driverId,
        ]);
    }
}
