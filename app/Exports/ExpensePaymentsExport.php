<?php

namespace App\Exports;

use App\Models\ExpensePayment;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Excel;

class ExpensePaymentsExport implements FromQuery, WithHeadings, WithMapping, Responsable, ShouldAutoSize
{
    use Exportable;

    public string $fileName = 'expense_payments.xlsx';
    public string $writerType = Excel::XLSX;

    public function __construct(private readonly array $filters = [])
    {
    }

    public function query()
    {
        $query = ExpensePayment::query()->with(['driver', 'vehicle']);

        if ($keyword = $this->filters['keyword'] ?? null) {
            $query->keyword($keyword);
        }

        if ($status = $this->filters['status'] ?? null) {
            $query->status($status);
        }

        $recordFrom = $this->filters['record_date_from'] ?? null;
        $recordTo = $this->filters['record_date_to'] ?? null;
        if ($recordFrom || $recordTo) {
            $query->recordDateBetween($recordFrom, $recordTo);
        }

        $paymentFrom = $this->filters['payment_date_from'] ?? null;
        $paymentTo = $this->filters['payment_date_to'] ?? null;
        if ($paymentFrom || $paymentTo) {
            $query->paymentDateBetween($paymentFrom, $paymentTo);
        }

        if ($driverId = $this->filters['driver_id'] ?? null) {
            $query->where('driver_id', $driverId);
        }

        if ($vehicleId = $this->filters['vehicle_id'] ?? null) {
            $query->where('vehicle_id', $vehicleId);
        }

        return $query->orderByDesc('record_date')->orderByDesc('record_time');
    }

    public function headings(): array
    {
        return [
            '紀錄日期',
            '紀錄時間',
            '隊員編號',
            '隊員姓名',
            '車牌號碼',
            '款項名稱',
            '支付金額',
            '應扣款',
            '實付金額',
            '狀態',
            '支付日期',
            '支付方式',
            '備註',
        ];
    }

    public function map($expensePayment): array
    {
        return [
            optional($expensePayment->record_date)->format('Y-m-d'),
            $expensePayment->record_time,
            $expensePayment->member_code,
            $expensePayment->member_name,
            $expensePayment->vehicle_license_number,
            $expensePayment->item_name,
            $expensePayment->gross_amount,
            $expensePayment->deduction,
            $expensePayment->net_amount,
            $expensePayment->status === 'paid' ? '已支付' : '未支付',
            optional($expensePayment->payment_date)->format('Y-m-d'),
            $expensePayment->payment_method,
            $expensePayment->note,
        ];
    }
}
