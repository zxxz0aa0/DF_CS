<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view company categories')->only(['index', 'show']);
        $this->middleware('permission:create company categories')->only(['create', 'store']);
        $this->middleware('permission:edit company categories')->only(['edit', 'update']);
        $this->middleware('permission:delete company categories')->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CompanyCategory::query();

        if ($request->search) {
            $query->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('description', 'like', '%'.$request->search.'%');
        }

        $categories = $query->withCount('companies')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/CompanyCategories/Index', [
            'categories' => $categories,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/CompanyCategories/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:company_categories',
            'description' => 'nullable|string',
        ]);

        CompanyCategory::create($request->only(['name', 'description']));

        return redirect()->route('admin.company-categories.index')
            ->with('success', '公司類別已成功建立');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompanyCategory $companyCategory)
    {
        return Inertia::render('Admin/CompanyCategories/Edit', [
            'category' => $companyCategory,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CompanyCategory $companyCategory)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:company_categories,name,'.$companyCategory->id,
            'description' => 'nullable|string',
        ]);

        $companyCategory->update($request->only(['name', 'description']));

        return redirect()->route('admin.company-categories.index')
            ->with('success', '公司類別已成功更新');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompanyCategory $companyCategory)
    {
        if ($companyCategory->companies()->count() > 0) {
            return back()->with('error', '無法刪除此類別，因為還有公司使用此類別');
        }

        $companyCategory->delete();

        return back()->with('success', '公司類別已成功刪除');
    }
}
