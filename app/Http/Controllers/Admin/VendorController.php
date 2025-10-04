<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VendorRequest;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VendorController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view vendors')->only(['index', 'show']);
        $this->middleware('permission:create vendors')->only(['create', 'store']);
        $this->middleware('permission:edit vendors')->only(['edit', 'update']);
        $this->middleware('permission:delete vendors')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Vendor::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $vendors = $query->orderBy('created_at', 'desc')->paginate(15);

        return Inertia::render('Admin/Vendors/Index', [
            'vendors' => $vendors,
            'filters' => [
                'search' => $request->search,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Vendors/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VendorRequest $request)
    {
        Vendor::create($request->validated());

        return redirect()->route('admin.vendors.index')
            ->with('success', '廠商資料已成功新增');
    }

    /**
     * Display the specified resource.
     */
    public function show(Vendor $vendor)
    {
        return Inertia::render('Admin/Vendors/Show', [
            'vendor' => $vendor,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vendor $vendor)
    {
        return Inertia::render('Admin/Vendors/Edit', [
            'vendor' => $vendor,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(VendorRequest $request, Vendor $vendor)
    {
        $vendor->update($request->validated());

        return redirect()->route('admin.vendors.index')
            ->with('success', '廠商資料已成功更新');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vendor $vendor)
    {
        $vendor->delete();

        return redirect()->route('admin.vendors.index')
            ->with('success', '廠商資料已成功刪除');
    }
}
