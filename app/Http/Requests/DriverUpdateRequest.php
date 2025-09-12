<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DriverUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $driverId = $this->route('driver')->id;
        
        return [
            'name' => 'required|string|max:100',
            'id_number' => 'required|string|size:10|unique:drivers,id_number,' . $driverId . '|regex:/^[A-Z][0-9]{9}$/',
            'company_category_id' => 'nullable|exists:company_categories,id',
            'birthday' => 'nullable|date|before:today',
            'contact_address' => 'nullable|string|max:1000',
            'residence_address' => 'nullable|string|max:1000',
            'home_phone' => 'nullable|string|max:20|regex:/^0[0-9-]{8,12}$/',
            'mobile_phone1' => 'nullable|string|max:20|regex:/^09[0-9]{8}$/',
            'mobile_phone2' => 'nullable|string|max:20|regex:/^09[0-9]{8}$/',
            'emergency_contact' => 'nullable|string|max:100',
            'emergency_phone' => 'nullable|string|max:20',
            'registration_date' => 'required|date',
            'deregistration_date' => 'nullable|date|after:registration_date',
            'fleet_join_date' => 'nullable|date',
            'fleet_leave_date' => 'nullable|date|after:fleet_join_date',
            'license_expire_date' => 'nullable|date',
            'professional_license_expire_date' => 'nullable|date',
            'status' => 'required|in:open,close',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => '姓名為必填欄位',
            'name.max' => '姓名長度不能超過 100 個字元',
            'id_number.required' => '身分證字號為必填欄位',
            'id_number.size' => '身分證字號必須為 10 位字元',
            'id_number.unique' => '此身分證字號已存在',
            'id_number.regex' => '身分證字號格式不正確',
            'company_category_id.exists' => '選擇的公司類別不存在',
            'birthday.date' => '生日必須為有效日期',
            'birthday.before' => '生日必須為今天之前的日期',
            'home_phone.regex' => '住家電話格式不正確',
            'mobile_phone1.regex' => '手機號碼1格式不正確',
            'mobile_phone2.regex' => '手機號碼2格式不正確',
            'registration_date.required' => '入籍日期為必填欄位',
            'registration_date.date' => '入籍日期必須為有效日期',
            'deregistration_date.after' => '退籍日期必須在入籍日期之後',
            'fleet_leave_date.after' => '退出車隊日期必須在加入車隊日期之後',
            'license_expire_date.after' => '駕照到期日必須為未來日期',
            'professional_license_expire_date.after' => '執登到期日必須為未來日期',
            'status.required' => '狀態為必選欄位',
            'status.in' => '狀態值無效',
        ];
    }
}
