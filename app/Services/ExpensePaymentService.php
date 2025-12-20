<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\ExpensePayment;
use App\Models\Vehicle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpensePaymentService
{
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = ExpensePayment::query()
            ->with(['driver.companyCategory', 'vehicle', 'creator', 'payer'])
            ->orderByDesc('record_date')
            ->orderByDesc('record_time');

        $this->applyFilters($query, $filters);

        return $query->paginate($perPage)->withQueryString();
    }

    public function statistics(array $filters = []): array
    {
        $query = ExpensePayment::query();
        $this->applyFilters($query, $filters);

        $pendingTotal = (clone $query)->where('status', 'pending')->sum('net_amount');
        $paidTotal = (clone $query)->where('status', 'paid')->sum('net_amount');

        return [
            'pending_total' => round((float) $pendingTotal, 2),
            'paid_total' => round((float) $paidTotal, 2),
        ];
    }

    public function create(array $data): ExpensePayment
    {
        $payload = $this->preparePayload($data);

        return DB::transaction(fn () => ExpensePayment::create($payload));
    }

    public function update(ExpensePayment $expensePayment, array $data): ExpensePayment
    {
        $payload = $this->preparePayload($data, $expensePayment);

        return DB::transaction(function () use ($expensePayment, $payload) {
            $expensePayment->update($payload);

            return $expensePayment;
        });
    }

    public function bulkUpdateStatus(array $ids, array $data): int
    {
        $status = Arr::get($data, 'status', 'pending');
        $paymentDate = Arr::get($data, 'payment_date');
        $paymentMethod = Arr::get($data, 'payment_method');
        $note = Arr::get($data, 'note');

        $updatePayload = [
            'status' => $status,
            'payment_method' => $paymentMethod,
        ];

        if (Auth::check()) {
            $updatePayload['updated_by'] = Auth::id();
        }

        $updatePayload['updated_at'] = now();

        if ($note !== null) {
            $updatePayload['note'] = $note;
        }

        if ($status === 'paid') {
            $updatePayload['payment_date'] = $paymentDate;
            $updatePayload['paid_at'] = now();
            $updatePayload['paid_by'] = Auth::id();
        } else {
            $updatePayload['payment_date'] = null;
            $updatePayload['paid_at'] = null;
            $updatePayload['paid_by'] = null;
            $updatePayload['payment_method'] = null;
        }

        return ExpensePayment::whereIn('id', $ids)->update($updatePayload);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $query->when($filters['keyword'] ?? null, function (Builder $builder, $keyword) {
            $builder->keyword($keyword);
        });

        $query->when($filters['status'] ?? null, function (Builder $builder, $status) {
            $builder->status($status);
        });

        $query->when($filters['record_date_from'] ?? null, function (Builder $builder, $start) use ($filters) {
            $end = $filters['record_date_to'] ?? null;
            $builder->recordDateBetween($start, $end);
        }, function (Builder $builder) use ($filters) {
            if ($filters['record_date_to'] ?? null) {
                $builder->recordDateBetween(null, $filters['record_date_to']);
            }
        });

        $query->when($filters['payment_date_from'] ?? null, function (Builder $builder, $start) use ($filters) {
            $end = $filters['payment_date_to'] ?? null;
            $builder->paymentDateBetween($start, $end);
        }, function (Builder $builder) use ($filters) {
            if ($filters['payment_date_to'] ?? null) {
                $builder->paymentDateBetween(null, $filters['payment_date_to']);
            }
        });

        $query->when($filters['driver_id'] ?? null, function (Builder $builder, $driverId) {
            $builder->where('driver_id', $driverId);
        });

        $query->when($filters['vehicle_id'] ?? null, function (Builder $builder, $vehicleId) {
            $builder->where('vehicle_id', $vehicleId);
        });
    }

    protected function preparePayload(array $data, ?ExpensePayment $existing = null): array
    {
        $payload = Arr::only($data, [
            'record_date',
            'record_time',
            'driver_id',
            'vehicle_id',
            'member_code',
            'member_name',
            'vehicle_license_number',
            'item_name',
            'gross_amount',
            'deduction',
            'net_amount',
            'payment_date',
            'payment_method',
            'status',
            'note',
        ]);

        $payload['record_time'] = $this->normalizeTime($payload['record_time'] ?? null);
        $payload['deduction'] = round((float) ($payload['deduction'] ?? 0), 2);
        $payload['gross_amount'] = round((float) ($payload['gross_amount'] ?? 0), 2);
        $payload['net_amount'] = round((float) ($payload['net_amount'] ?? ($payload['gross_amount'] - $payload['deduction'])), 2);

        // 根據 vehicle_license_number 查詢 vehicle_id
        if (! empty($payload['vehicle_license_number'])) {
            $vehicle = Vehicle::where('license_number', $payload['vehicle_license_number'])->first();
            if ($vehicle) {
                $payload['vehicle_id'] = $vehicle->id;
            }
        }

        if (($payload['status'] ?? 'pending') === 'paid') {
            if (empty($payload['payment_date'])) {
                $payload['payment_date'] = Carbon::now()->toDateString();
            }

            $payload['paid_at'] = $existing && $existing->status === 'paid'
                ? ($existing->paid_at ?? now())
                : now();
            $payload['paid_by'] = Auth::id();
        } else {
            $payload['payment_date'] = null;
            $payload['payment_method'] = null;
            $payload['paid_at'] = null;
            $payload['paid_by'] = null;
        }

        $payload['member_code'] = $payload['member_code'] === '' ? null : $payload['member_code'];
        $payload['vehicle_license_number'] = $payload['vehicle_license_number'] === '' ? null : $payload['vehicle_license_number'];
        $payload['payment_method'] = $payload['payment_method'] === '' ? null : $payload['payment_method'];
        $payload['note'] = $payload['note'] === '' ? null : $payload['note'];
        $payload['payment_date'] = $payload['payment_date'] === '' ? null : $payload['payment_date'];

        return $payload;
    }

    protected function normalizeTime(?string $time): ?string
    {
        if (! $time) {
            return null;
        }

        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
            return $time;
        }

        if (preg_match('/^(\d{2}):(\d{2})$/', $time, $matches)) {
            return sprintf('%02d:%02d:00', $matches[1], $matches[2]);
        }

        $carbon = Carbon::parse($time);

        return $carbon->format('H:i:s');
    }
}
