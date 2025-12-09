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
