<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ExpensePaymentsExport;
use App\Exports\ExpensePaymentsTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExpensePaymentBulkStatusRequest;
use App\Http\Requests\Admin\ExpensePaymentImportRequest;
use App\Http\Requests\Admin\ExpensePaymentStoreRequest;
use App\Http\Requests\Admin\ExpensePaymentUpdateRequest;
use App\Imports\ExpensePaymentsImport;
use App\Models\Driver;
use App\Models\ExpensePayment;
use App\Models\Vehicle;
use App\Services\ExpensePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExpensePaymentController extends Controller
{
    public function __construct(private readonly ExpensePaymentService $expensePaymentService)
    {
        $this->middleware('permission:view expense payments')->only(['index']);
        $this->middleware('permission:create expense payments')->only(['store']);
        $this->middleware('permission:edit expense payments')->only(['update', 'bulkStatus']);
        $this->middleware('permission:delete expense payments')->only(['destroy']);
        $this->middleware('permission:export expense payments')->only(['export', 'template']);
        $this->middleware('permission:import expense payments')->only(['import']);
    }

    public function index(Request $request): Response
    {
        $filters = $request->only([
            'keyword',
            'status',
            'record_date_from',
            'record_date_to',
            'payment_date_from',
            'payment_date_to',
            'driver_id',
            'vehicle_id',
        ]);

        $perPage = (int) $request->input('per_page', 20);
        $payments = $this->expensePaymentService->paginate($filters, max(5, $perPage));
        $statistics = $this->expensePaymentService->statistics($filters);

        $drivers = Driver::select('id', 'name', 'id_number')
            ->orderBy('name')
            ->get();

        $vehicles = Vehicle::select('id', 'license_number')
            ->orderBy('license_number')
            ->get();

        return Inertia::render('Admin/ExpensePayments/Index', [
            'payments' => $payments,
            'filters' => array_merge([
                'per_page' => $perPage,
                'keyword' => null,
                'status' => null,
                'record_date_from' => null,
                'record_date_to' => null,
                'payment_date_from' => null,
                'payment_date_to' => null,
                'driver_id' => null,
                'vehicle_id' => null,
            ], $filters),
            'statistics' => $statistics,
            'drivers' => $drivers,
            'vehicles' => $vehicles,
            'permissions' => [
                'canCreate' => $request->user()->can('create expense payments'),
                'canEdit' => $request->user()->can('edit expense payments'),
                'canDelete' => $request->user()->can('delete expense payments'),
                'canImport' => $request->user()->can('import expense payments'),
                'canExport' => $request->user()->can('export expense payments'),
            ],
        ]);
    }

    public function store(ExpensePaymentStoreRequest $request): RedirectResponse
    {
        $this->expensePaymentService->create($request->validated());

        return back()->with('success', '支出款項新增成功');
    }

    public function update(ExpensePaymentUpdateRequest $request, ExpensePayment $expensePayment): RedirectResponse
    {
        $this->expensePaymentService->update($expensePayment, $request->validated());

        return back()->with('success', '支出款項更新成功');
    }

    public function destroy(ExpensePayment $expensePayment): RedirectResponse
    {
        $expensePayment->delete();

        return back()->with('success', '支出款項已刪除');
    }

    public function bulkStatus(ExpensePaymentBulkStatusRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $updated = $this->expensePaymentService->bulkUpdateStatus($validated['ids'], $validated);

        return back()->with('success', "已更新 {$updated} 筆款項狀態");
    }

    public function export(Request $request): BinaryFileResponse
    {
        $filters = $request->only([
            'keyword',
            'status',
            'record_date_from',
            'record_date_to',
            'payment_date_from',
            'payment_date_to',
            'driver_id',
            'vehicle_id',
        ]);

        return Excel::download(new ExpensePaymentsExport($filters), 'expense_payments_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function template(): BinaryFileResponse
    {
        return Excel::download(new ExpensePaymentsTemplateExport(), 'expense_payments_template.xlsx');
    }

    public function import(ExpensePaymentImportRequest $request): RedirectResponse
    {
        $import = new ExpensePaymentsImport($this->expensePaymentService);

        Excel::import($import, $request->file('file'));

        $failures = $import->getFailures();
        $successCount = $import->getSuccessCount();

        $failurePayload = collect($failures)->map(static function ($failure) {
            return [
                'row' => method_exists($failure, 'row') ? $failure->row() : null,
                'attribute' => method_exists($failure, 'attribute') ? $failure->attribute() : null,
                'errors' => method_exists($failure, 'errors') ? $failure->errors() : [],
            ];
        })->toArray();

        return back()->with([
            'success' => "成功匯入 {$successCount} 筆資料",
            'importFailures' => $failurePayload,
        ]);
    }
}
