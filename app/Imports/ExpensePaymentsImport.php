<?php

namespace App\Imports;

use App\Models\ExpensePayment;
use App\Services\ExpensePaymentService;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class ExpensePaymentsImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure
{
    protected array $failures = [];
    protected int $successCount = 0;

    public function __construct(private readonly ExpensePaymentService $expensePaymentService)
    {
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // header row offset

            $data = $this->transformRow($row->toArray());
            $validator = Validator::make($data, $this->rules());

            if ($validator->fails()) {
                $this->failures[] = new Failure($rowNumber, 'validation', $validator->errors()->all());
                continue;
            }

            // 檢查重複資料
            if ($this->isDuplicate($data)) {
                $this->failures[] = new Failure(
                    $rowNumber,
                    'duplicate',
                    ['此記錄已存在：隊員編號=' . ($data['member_code'] ?? '未提供') . '，紀錄日期=' . $data['record_date'] . '，紀錄時間=' . $data['record_time']]
                );
                continue;
            }

            try {
                $this->expensePaymentService->create($data);
                $this->successCount++;
            } catch (\Throwable $throwable) {
                $this->failures[] = new Failure($rowNumber, 'exception', [$throwable->getMessage()]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'record_date' => ['required', 'date'],
            'record_time' => ['required', 'date_format:H:i'],
            'member_code' => ['nullable', 'string', 'max:50'],
            'member_name' => ['required', 'string', 'max:100'],
            'vehicle_license_number' => ['nullable', 'string', 'max:20'],
            'item_name' => ['required', 'string', 'max:120'],
            'gross_amount' => ['required', 'numeric', 'min:0'],
            'deduction' => ['nullable', 'numeric', 'min:0'],
            'net_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:pending,paid'],
            'payment_date' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'string', 'max:30'],
            'note' => ['nullable', 'string'],
        ];
    }

    protected function transformRow(array $row): array
    {
        // 支援中文欄位名稱的對應
        $recordDate = $this->parseDate($row['record_date'] ?? $row['紀錄日期'] ?? null);
        $paymentDate = $this->parseDate($row['payment_date'] ?? $row['支付日期'] ?? null);
        $status = strtolower((string) ($row['status'] ?? $row['狀態'] ?? 'pending'));
        $status = in_array($status, ['paid', '已支付'], true) ? 'paid' : 'pending';

        $gross = (float) ($row['gross_amount'] ?? $row['支付金額'] ?? 0);
        $deduction = (float) ($row['deduction'] ?? $row['應扣款'] ?? 0);
        $net = $row['net_amount'] ?? $row['實付金額'] ?? ($gross - $deduction);

        $recordTime = $this->normalizeTime($row['record_time'] ?? $row['紀錄時間'] ?? null);

        if ($status === 'paid' && ! $paymentDate) {
            $paymentDate = $recordDate;
        }

        return [
            'record_date' => $recordDate,
            'record_time' => $recordTime,
            'member_code' => Arr::get($row, 'member_code') ?? Arr::get($row, '隊員編號'),
            'member_name' => Arr::get($row, 'member_name') ?? Arr::get($row, '隊員姓名'),
            'vehicle_license_number' => Arr::get($row, 'vehicle_license_number') ?? Arr::get($row, '車牌號碼'),
            'item_name' => Arr::get($row, 'item_name') ?? Arr::get($row, '款項名稱'),
            'gross_amount' => $gross,
            'deduction' => $deduction,
            'net_amount' => $net,
            'status' => $status,
            'payment_date' => $paymentDate,
            'payment_method' => Arr::get($row, 'payment_method') ?? Arr::get($row, '支付方式'),
            'note' => Arr::get($row, 'note') ?? Arr::get($row, '備註'),
        ];
    }

    protected function parseDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }

    protected function normalizeTime(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (preg_match('/^(\d{1,2}):(\d{2})$/', $value, $matches)) {
            return sprintf('%02d:%02d', (int) $matches[1], (int) $matches[2]);
        }

        if (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $value, $matches)) {
            return sprintf('%02d:%02d', (int) $matches[1], (int) $matches[2]);
        }

        try {
            return Carbon::parse($value)->format('H:i');
        } catch (\Exception) {
            return null;
        }
    }

    public function onFailure(Failure ...$failures)
    {
        $this->failures = array_merge($this->failures, $failures);
    }

    public function getFailures(): array
    {
        return $this->failures;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    protected function isDuplicate(array $data): bool
    {
        $query = ExpensePayment::where('record_date', $data['record_date'])
            ->where('record_time', $data['record_time']);

        // 使用 member_code 進行查詢（若存在的話）
        if (! empty($data['member_code'])) {
            $query->where('member_code', $data['member_code']);
        }

        return $query->exists();
    }
}
