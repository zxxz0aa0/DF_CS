<?php

namespace App\Exports;

use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Excel;

class ExpensePaymentsTemplateExport implements FromArray, ShouldAutoSize, Responsable
{
    use Exportable;

    public string $fileName = 'expense_payments_template.xlsx';
    public string $writerType = Excel::XLSX;

    public function array(): array
    {
        return [
            [
                'record_date',
                'record_time',
                'member_code',
                'member_name',
                'vehicle_license_number',
                'item_name',
                'gross_amount',
                'deduction',
                'net_amount',
                'status',
                'payment_date',
                'payment_method',
                'note',
            ],
            [
                '2025-01-01',
                '09:00',
                'D00001',
                '王小明',
                'ABC-1234',
                '油資補助',
                '1200',
                '200',
                '1000',
                'pending',
                '',
                '',
                '備註範例',
            ],
        ];
    }
}
