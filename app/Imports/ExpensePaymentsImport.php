<?php

namespace App\Imports;

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
            'net_amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:pending,paid'],
            'payment_date' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'string', 'max:30'],
            'note' => ['nullable', 'string'],
        ];
    }

    protected function transformRow(array $row): array
    {
        $recordDate = $this->parseDate($row['record_date'] ?? null);
        $paymentDate = $this->parseDate($row['payment_date'] ?? null);
        $status = strtolower((string) ($row['status'] ?? 'pending'));
        $status = in_array($status, ['paid', '已支付'], true) ? 'paid' : 'pending';

        $gross = (float) ($row['gross_amount'] ?? 0);
        $deduction = (float) ($row['deduction'] ?? 0);
        $net = $row['net_amount'] ?? ($gross - $deduction);

        $recordTime = $this->normalizeTime($row['record_time'] ?? null);

        if ($status === 'paid' && ! $paymentDate) {
            $paymentDate = $recordDate;
        }

        return [
            'record_date' => $recordDate,
            'record_time' => $recordTime,
            'driver_id' => Arr::get($row, 'driver_id'),
            'vehicle_id' => Arr::get($row, 'vehicle_id'),
            'member_code' => Arr::get($row, 'member_code'),
            'member_name' => Arr::get($row, 'member_name'),
            'vehicle_license_number' => Arr::get($row, 'vehicle_license_number'),
            'item_name' => Arr::get($row, 'item_name'),
            'gross_amount' => $gross,
            'deduction' => $deduction,
            'net_amount' => $net,
            'status' => $status,
            'payment_date' => $paymentDate,
            'payment_method' => Arr::get($row, 'payment_method'),
            'note' => Arr::get($row, 'note'),
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
}
