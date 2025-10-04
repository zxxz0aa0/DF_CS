<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpensePaymentBulkStatusRequest extends FormRequest
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
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:expense_payments,id'],
            'status' => ['required', Rule::in(['pending', 'paid'])],
            'payment_date' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'string', 'max:30'],
            'note' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('status') === 'paid' && ! $this->filled('payment_date')) {
                $validator->errors()->add('payment_date', '已支付狀態需填寫支付日期。');
            }
        });
    }
}
