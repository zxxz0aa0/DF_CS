<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountingRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // 批次新增
        if ($this->isMethod('post') && $this->has('records')) {
            return [
                'records' => 'required|array|min:1',
                'records.*.driver_id' => 'nullable|exists:drivers,id',
                'records.*.vehicle_id' => 'nullable|exists:vehicles,id',
                'records.*.account_detail_id' => 'required|exists:account_details,id',
                'records.*.transaction_date' => 'required|date',
                'records.*.debit_amount' => 'nullable|numeric|min:0|max:9999999999.99',
                'records.*.credit_amount' => 'nullable|numeric|min:0|max:9999999999.99',
                'records.*.note' => 'nullable|string|max:1000',
            ];
        }

        // 單筆更新
        return [
            'driver_id' => 'nullable|exists:drivers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'account_detail_id' => 'required|exists:account_details,id',
            'transaction_date' => 'required|date',
            'debit_amount' => 'nullable|numeric|min:0|max:9999999999.99',
            'credit_amount' => 'nullable|numeric|min:0|max:9999999999.99',
            'note' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'records.required' => '至少需要新增一筆記錄',
            'records.*.account_detail_id.required' => '會計科目為必填欄位',
            'records.*.transaction_date.required' => '交易日期為必填欄位',
            'records.*.debit_amount.numeric' => '借方金額必須為數字',
            'records.*.credit_amount.numeric' => '貸方金額必須為數字',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('records')) {
                // 批次驗證：確保借方或貸方至少有一個有值
                foreach ($this->records as $index => $record) {
                    $debit = $record['debit_amount'] ?? 0;
                    $credit = $record['credit_amount'] ?? 0;

                    if ($debit == 0 && $credit == 0) {
                        $validator->errors()->add(
                            "records.{$index}.debit_amount",
                            '借方金額或貸方金額至少需要填寫一個'
                        );
                    }
                }
            } else {
                // 單筆驗證
                $debit = $this->debit_amount ?? 0;
                $credit = $this->credit_amount ?? 0;

                if ($debit == 0 && $credit == 0) {
                    $validator->errors()->add(
                        'debit_amount',
                        '借方金額或貸方金額至少需要填寫一個'
                    );
                }
            }
        });
    }
}
