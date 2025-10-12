<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentFile;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view documents')->only(['index', 'show']);
        $this->middleware('permission:create documents')->only(['create', 'store']);
        $this->middleware('permission:edit documents')->only(['edit', 'update']);
        $this->middleware('permission:delete documents')->only(['destroy', 'deleteFile']);
    }

    /**
     * 顯示文件列表
     */
    public function index(Request $request)
    {
        $query = Document::with(['driver', 'vehicle', 'files', 'createdBy']);

        // 搜尋
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $searchType = $request->search_type;

            if ($searchType === 'vehicle') {
                $query->whereHas('vehicle', function ($q) use ($keyword) {
                    $q->where('license_number', 'like', "%{$keyword}%")
                      ->orWhere('fleet_number', 'like', "%{$keyword}%");
                });
            } else {
                $query->whereHas('driver', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('id_number', 'like', "%{$keyword}%");
                });
            }
        }

        // 篩選：文件類別
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        // 篩選：狀態
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 篩選：關聯對象
        if ($request->filled('driver_id')) {
            $query->byDriver($request->driver_id);
        }
        if ($request->filled('vehicle_id')) {
            $query->byVehicle($request->vehicle_id);
        }

        $documents = $query->orderBy('expiry_date', 'asc')
                          ->orderBy('created_at', 'desc')
                          ->paginate(15);

        return Inertia::render('Admin/Documents/Index', [
            'documents' => $documents,
            'filters' => [
                'search_type' => $request->search_type,
                'keyword' => $request->keyword,
                'category' => $request->category,
                'status' => $request->status,
                'driver_id' => $request->driver_id,
                'vehicle_id' => $request->vehicle_id,
            ],
            'stats' => [
                'total' => Document::count(),
                'expiring_soon' => Document::where('status', 'expiring_soon')->count(),
                'expired' => Document::where('status', 'expired')->count(),
            ],
        ]);
    }

    /**
     * 顯示新增表單
     */
    public function create()
    {
        $drivers = Driver::active()->orderBy('name')->get(['id', 'name', 'id_number']);
        $vehicles = Vehicle::active()->orderBy('license_number')->get(['id', 'license_number', 'brand', 'vehicle_model']);

        return Inertia::render('Admin/Documents/Create', [
            'drivers' => $drivers,
            'vehicles' => $vehicles,
        ]);
    }

    /**
     * 儲存新文件
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'driver_id' => 'nullable|exists:drivers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'document_category' => 'required|in:identity,insurance,vehicle',
            'document_name' => 'required|string|max:255',
            'document_number' => 'nullable|string|max:100',
            'insurance_level' => 'nullable|numeric|min:0',
            'insurance_fee' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string|max:2000',
            'files.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB
        ]);

        // 驗證：driver_id 和 vehicle_id 必須二選一
        if (empty($validated['driver_id']) && empty($validated['vehicle_id'])) {
            return back()->withErrors(['driver_id' => '請選擇駕駛或車輛']);
        }
        if (!empty($validated['driver_id']) && !empty($validated['vehicle_id'])) {
            return back()->withErrors(['vehicle_id' => '不可同時選擇駕駛和車輛']);
        }

        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        // 建立文件
        $document = Document::create($validated);

        // 上傳檔案
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $index => $file) {
                $path = $file->store('documents', 'public');

                DocumentFile::create([
                    'document_id' => $document->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize(),
                    'sort_order' => $index,
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        return redirect()->route('admin.documents.index')
            ->with('success', '文件已成功建立！');
    }

    /**
     * 顯示文件詳情
     */
    public function show(Document $document)
    {
        $document->load(['driver', 'vehicle', 'files', 'createdBy', 'updatedBy']);

        return Inertia::render('Admin/Documents/Show', [
            'document' => $document,
        ]);
    }

    /**
     * 顯示編輯表單
     */
    public function edit(Document $document)
    {
        $drivers = Driver::active()->orderBy('name')->get(['id', 'name', 'id_number']);
        $vehicles = Vehicle::active()->orderBy('license_number')->get(['id', 'license_number', 'brand', 'vehicle_model']);

        // 載入關聯資料
        $document->load(['driver', 'vehicle', 'files']);

        // 格式化日期為 HTML input[type="date"] 可接受的格式 (YYYY-MM-DD)
        $documentData = $document->toArray();
        $documentData['start_date'] = $document->start_date?->format('Y-m-d');
        $documentData['expiry_date'] = $document->expiry_date?->format('Y-m-d');

        return Inertia::render('Admin/Documents/Edit', [
            'document' => $documentData,
            'drivers' => $drivers,
            'vehicles' => $vehicles,
        ]);
    }

    /**
     * 更新文件
     */
    public function update(Request $request, Document $document)
    {
        $validated = $request->validate([
            'driver_id' => 'nullable|exists:drivers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'document_category' => 'required|in:identity,insurance,vehicle',
            'document_name' => 'required|string|max:255',
            'document_number' => 'nullable|string|max:100',
            'insurance_level' => 'nullable|numeric|min:0',
            'insurance_fee' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string|max:2000',
            'files.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        // 驗證：driver_id 和 vehicle_id 必須二選一
        if (empty($validated['driver_id']) && empty($validated['vehicle_id'])) {
            return back()->withErrors(['driver_id' => '請選擇駕駛或車輛']);
        }
        if (!empty($validated['driver_id']) && !empty($validated['vehicle_id'])) {
            return back()->withErrors(['vehicle_id' => '不可同時選擇駕駛和車輛']);
        }

        $validated['updated_by'] = auth()->id();
        $document->update($validated);

        // 新增檔案
        if ($request->hasFile('files')) {
            $maxOrder = $document->files()->max('sort_order') ?? -1;

            foreach ($request->file('files') as $index => $file) {
                $path = $file->store('documents', 'public');

                DocumentFile::create([
                    'document_id' => $document->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize(),
                    'sort_order' => $maxOrder + $index + 1,
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        return redirect()->route('admin.documents.index')
            ->with('success', '文件已成功更新！');
    }

    /**
     * 刪除文件
     */
    public function destroy(Document $document)
    {
        $documentName = $document->document_name;

        // 刪除所有關聯檔案
        foreach ($document->files as $file) {
            if (Storage::exists($file->file_path)) {
                Storage::delete($file->file_path);
            }
        }

        $document->delete();

        return redirect()->route('admin.documents.index')
            ->with('success', "文件「{$documentName}」已成功刪除！");
    }

    /**
     * 刪除單一檔案
     */
    public function deleteFile(DocumentFile $file)
    {
        if (Storage::exists($file->file_path)) {
            Storage::delete($file->file_path);
        }

        $file->delete();

        return back()->with('success', '檔案已成功刪除！');
    }

    /**
     * 下載檔案
     */
    public function downloadFile(DocumentFile $file)
    {
        if (!Storage::disk('public')->exists($file->file_path)) {
            abort(404, '檔案不存在');
        }

        $filePath = Storage::disk('public')->path($file->file_path);
        $mimeType = Storage::disk('public')->mimeType($file->file_path);

        // 圖片和 PDF 使用內嵌顯示（inline），其他檔案強制下載
        $displayInline = in_array($file->file_type, ['jpg', 'jpeg', 'png', 'gif', 'pdf']);

        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => $displayInline
                ? 'inline; filename="' . $file->file_name . '"'
                : 'attachment; filename="' . $file->file_name . '"',
        ]);
    }
}
