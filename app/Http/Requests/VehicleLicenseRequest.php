<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VehicleLicenseRequest extends FormRequest
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
        return [
            'company_id' => 'required|exists:companies,id',
            'county' => 'nullable|string|max:50',
            'license_number' => 'nullable|string|max:20',
            'holder_name' => 'nullable|string|max:100',
            'license_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 10),
            'license_month' => 'nullable|integer|min:1|max:12',
            'previous_license_number' => 'nullable|string|max:20',
            'previous_holder_name' => 'nullable|string|max:100',
            'previous_license_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 10),
            'previous_license_month' => 'nullable|integer|min:1|max:12',
            'notes' => 'nullable|string',
            'replacement_date' => 'nullable|date',
            'revocation_date' => 'nullable|date',
            'status' => 'required|in:active,revoked,transferred'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'company_id' => '公司',
            'county' => '縣市',
            'license_number' => '車牌號碼',
            'holder_name' => '使用者名稱',
            'license_year' => '車牌年份',
            'license_month' => '車牌月份',
            'previous_license_number' => '前車牌號碼',
            'previous_holder_name' => '前使用者名稱',
            'previous_license_year' => '前車牌年份',
            'previous_license_month' => '前車牌月份',
            'notes' => '備註',
            'replacement_date' => '替補日期',
            'revocation_date' => '繳銷日期',
            'status' => '狀態'
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'company_id.required' => '請選擇公司',
            'company_id.exists' => '選擇的公司不存在',
            'license_year.min' => '車牌年份不能小於 1900',
            'license_year.max' => '車牌年份不能大於 ' . (date('Y') + 10),
            'license_month.min' => '車牌月份必須在 1-12 之間',
            'license_month.max' => '車牌月份必須在 1-12 之間',
            'previous_license_year.min' => '前車牌年份不能小於 1900',
            'previous_license_year.max' => '前車牌年份不能大於 ' . (date('Y') + 10),
            'previous_license_month.min' => '前車牌月份必須在 1-12 之間',
            'previous_license_month.max' => '前車牌月份必須在 1-12 之間',
            'status.required' => '請選擇狀態',
            'status.in' => '狀態必須是：使用中、已繳銷、已轉移'
        ];
    }
}
