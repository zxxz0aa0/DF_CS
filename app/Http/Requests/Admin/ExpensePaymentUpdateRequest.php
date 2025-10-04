<?php

namespace App\Http\Requests\Admin;

use App\Models\ExpensePayment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpensePaymentUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('edit expense payments') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var ExpensePayment|null $expensePayment */
        $expensePayment = $this->route('expense_payment');
        $expensePaymentId = $expensePayment?->id;

        $uniqueRule = Rule::unique('expense_payments')->ignore($expensePaymentId)->where(function ($query) {
            $query->where('record_date', $this->input('record_date'))
                ->where('record_time', $this->input('record_time'));

            if ($this->filled('driver_id')) {
                $query->where('driver_id', $this->input('driver_id'));
            } else {
                $query->whereNull('driver_id');

                if ($this->filled('member_code')) {
                    $query->where('member_code', $this->input('member_code'));
                } else {
                    $query->whereNull('member_code');
                }
            }

            $query->whereNull('deleted_at');
        });

        return [
            'record_date' => ['required', 'date'],
            'record_time' => ['required', 'date_format:H:i', $uniqueRule],
            'driver_id' => ['nullable', 'exists:drivers,id'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'member_code' => ['nullable', 'string', 'max:50'],
            'member_name' => ['required', 'string', 'max:100'],
            'vehicle_license_number' => ['nullable', 'string', 'max:20'],
            'item_name' => ['required', 'string', 'max:120'],
            'gross_amount' => ['required', 'numeric', 'min:0'],
            'deduction' => ['nullable', 'numeric', 'min:0'],
            'net_amount' => ['required', 'numeric', 'min:0'],
            'payment_date' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'string', 'max:30'],
            'status' => ['required', Rule::in(['pending', 'paid'])],
            'note' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'deduction' => $this->input('deduction', 0),
            'driver_id' => $this->input('driver_id') ?: null,
            'vehicle_id' => $this->input('vehicle_id') ?: null,
            'member_code' => $this->filled('member_code') ? $this->input('member_code') : null,
            'payment_method' => $this->filled('payment_method') ? $this->input('payment_method') : null,
            'note' => $this->filled('note') ? $this->input('note') : null,
        ]);

        if (! $this->filled('net_amount') && $this->filled('gross_amount')) {
            $gross = (float) $this->input('gross_amount');
            $deduction = (float) $this->input('deduction', 0);
            $this->merge([
                'net_amount' => $gross - $deduction,
            ]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->filled('driver_id') && ! $this->filled('member_code')) {
                $validator->errors()->add('member_code', '請提供隊員編號或選擇隊員。');
            }

            $gross = (float) $this->input('gross_amount', 0);
            $deduction = (float) $this->input('deduction', 0);
            $net = (float) $this->input('net_amount', 0);

            if ($deduction > $gross) {
                $validator->errors()->add('deduction', '應扣款不可大於支付金額。');
            }

            if (abs(($gross - $deduction) - $net) > 0.01) {
                $validator->errors()->add('net_amount', '實付金額須等於支付金額減應扣款。');
            }

            if ($this->input('status') === 'paid' && ! $this->filled('payment_date')) {
                $validator->errors()->add('payment_date', '已支付狀態需填寫支付日期。');
            }
        });
    }
}
