<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view companies')->only(['index', 'show']);
        $this->middleware('permission:create companies')->only(['create', 'store']);
        $this->middleware('permission:edit companies')->only(['edit', 'update']);
        $this->middleware('permission:delete companies')->only(['destroy']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Company::with('category');

        if ($request->search) {
            $query->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('tax_id', 'like', '%'.$request->search.'%')
                ->orWhere('representative', 'like', '%'.$request->search.'%');
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $companies = $query->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $categories = CompanyCategory::orderBy('name')->get();

        return Inertia::render('Admin/Companies/Index', [
            'companies' => $companies,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category_id', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = CompanyCategory::orderBy('name')->get();

        return Inertia::render('Admin/Companies/Create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:company_categories,id',
            'name' => 'required|string|max:200|unique:companies',
            'address' => 'nullable|string',
            'tax_id' => 'nullable|string|max:20|unique:companies',
            'phone' => 'nullable|string|max:50',
            'representative' => 'nullable|string|max:100',
            'email' => 'nullable|email',
            'website' => 'nullable|url',
            'status' => 'required|in:active,inactive',
        ]);

        Company::create($request->all());

        return redirect()->route('admin.companies.index')
            ->with('success', '公司已成功建立');
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company)
    {
        $company->load('category');

        return Inertia::render('Admin/Companies/Show', [
            'company' => $company,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        $categories = CompanyCategory::orderBy('name')->get();

        return Inertia::render('Admin/Companies/Edit', [
            'company' => $company,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        $request->validate([
            'category_id' => 'required|exists:company_categories,id',
            'name' => 'required|string|max:200|unique:companies,name,'.$company->id,
            'address' => 'nullable|string',
            'tax_id' => 'nullable|string|max:20|unique:companies,tax_id,'.$company->id,
            'phone' => 'nullable|string|max:50',
            'representative' => 'nullable|string|max:100',
            'email' => 'nullable|email',
            'website' => 'nullable|url',
            'status' => 'required|in:active,inactive',
        ]);

        $company->update($request->all());

        return redirect()->route('admin.companies.index')
            ->with('success', '公司已成功更新');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        $company->delete();

        return back()->with('success', '公司已成功刪除');
    }
}
