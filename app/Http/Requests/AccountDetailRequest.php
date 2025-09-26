<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountDetailRequest extends FormRequest
{
    /**
     * 判斷使用者是否有權限發出此請求
     */
    public function authorize(): bool
    {
        return true; // 在控制器中已經透過middleware處理權限
    }

    /**
     * 取得適用於該請求的驗證規則
     */
    public function rules(): array
    {
        $accountId = $this->route('detail') ? $this->route('detail')->id : null;

        return [
            'main_category_id' => [
                'required',
                'integer',
                'exists:account_main_categories,id'
            ],
            'sub_category_id' => [
                'required',
                'integer',
                'exists:account_sub_categories,id',
                // 驗證子分類是否屬於所選擇的總類
                Rule::exists('account_sub_categories', 'id')
                    ->where('main_category_id', $this->main_category_id)
            ],
            'account_code' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9]+$/', // 只允許數字
                Rule::unique('account_details', 'account_code')->ignore($accountId)
            ],
            'account_name' => [
                'required',
                'string',
                'max:150'
            ],
            'account_name_en' => [
                'nullable',
                'string',
                'max:150'
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'account_type' => [
                'required',
                'string',
                'in:asset,liability,equity,revenue,expense'
            ],
            'debit_credit' => [
                'required',
                'string',
                'in:debit,credit'
            ],
            'is_summary' => [
                'nullable',
                'boolean'
            ],
            'parent_id' => [
                'nullable',
                'integer',
                'exists:account_details,id',
                // 防止自己成為自己的父級
                function ($attribute, $value, $fail) use ($accountId) {
                    if ($value && $accountId && $value == $accountId) {
                        $fail('科目不能將自己設為上級科目');
                    }
                },
                // 防止循環參照
                function ($attribute, $value, $fail) use ($accountId) {
                    if ($value && $accountId) {
                        $this->validateNoCircularReference($value, $accountId, $fail);
                    }
                }
            ],
            'level' => [
                'nullable',
                'integer',
                'min:1',
                'max:10'
            ],
            'sort_order' => [
                'nullable',
                'integer',
                'min:0'
            ],
            'notes' => [
                'nullable',
                'string',
                'max:2000'
            ]
        ];
    }

    /**
     * 取得驗證錯誤的自訂屬性名稱
     */
    public function attributes(): array
    {
        return [
            'main_category_id' => '會計科目總類',
            'sub_category_id' => '會計科目子分類',
            'account_code' => '科目編號',
            'account_name' => '科目名稱',
            'account_name_en' => '英文科目名稱',
            'description' => '科目說明',
            'account_type' => '科目性質',
            'debit_credit' => '借貸性質',
            'is_summary' => '統馭科目',
            'parent_id' => '上級科目',
            'level' => '科目層級',
            'sort_order' => '排序順序',
            'notes' => '備註'
        ];
    }

    /**
     * 取得驗證錯誤的自訂訊息
     */
    public function messages(): array
    {
        return [
            'main_category_id.required' => '請選擇會計科目總類',
            'main_category_id.exists' => '選擇的會計科目總類不存在',
            'sub_category_id.required' => '請選擇會計科目子分類',
            'sub_category_id.exists' => '選擇的會計科目子分類不存在或不屬於所選總類',
            'account_code.required' => '請輸入科目編號',
            'account_code.unique' => '科目編號已存在',
            'account_code.regex' => '科目編號只能包含數字',
            'account_code.max' => '科目編號不能超過20個字元',
            'account_name.required' => '請輸入科目名稱',
            'account_name.max' => '科目名稱不能超過150個字元',
            'account_name_en.max' => '英文科目名稱不能超過150個字元',
            'description.max' => '科目說明不能超過1000個字元',
            'account_type.required' => '請選擇科目性質',
            'account_type.in' => '科目性質選擇無效',
            'debit_credit.required' => '請選擇借貸性質',
            'debit_credit.in' => '借貸性質選擇無效',
            'parent_id.exists' => '選擇的上級科目不存在',
            'level.min' => '科目層級不能小於1',
            'level.max' => '科目層級不能超過10',
            'sort_order.min' => '排序順序不能小於0',
            'notes.max' => '備註不能超過2000個字元'
        ];
    }

    /**
     * 驗證借貸性質是否與科目性質匹配
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $accountType = $this->input('account_type');
            $debitCredit = $this->input('debit_credit');

            if ($accountType && $debitCredit) {
                $expectedDebitCredit = $this->getExpectedDebitCredit($accountType);

                if ($debitCredit !== $expectedDebitCredit) {
                    $validator->errors()->add(
                        'debit_credit',
                        "選擇的科目性質通常應該使用{$this->getDebitCreditName($expectedDebitCredit)}性質"
                    );
                }
            }
        });
    }

    /**
     * 根據科目性質取得預期的借貸性質
     */
    private function getExpectedDebitCredit($accountType): string
    {
        $mapping = [
            'asset' => 'debit',    // 資產：借方
            'expense' => 'debit',  // 費用：借方
            'liability' => 'credit', // 負債：貸方
            'equity' => 'credit',   // 權益：貸方
            'revenue' => 'credit'   // 收入：貸方
        ];

        return $mapping[$accountType] ?? 'debit';
    }

    /**
     * 取得借貸性質的中文名稱
     */
    private function getDebitCreditName($debitCredit): string
    {
        return $debitCredit === 'debit' ? '借方' : '貸方';
    }

    /**
     * 驗證沒有循環參照
     */
    private function validateNoCircularReference($parentId, $currentId, $fail)
    {
        $visited = [];
        $checkId = $parentId;

        while ($checkId && !in_array($checkId, $visited)) {
            if ($checkId == $currentId) {
                $fail('不能選擇會造成循環參照的上級科目');
                return;
            }

            $visited[] = $checkId;

            // 取得下一個父級ID
            $parent = \App\Models\AccountDetail::find($checkId);
            $checkId = $parent ? $parent->parent_id : null;
        }
    }
}