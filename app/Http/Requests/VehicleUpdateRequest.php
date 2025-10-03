<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\VehicleLicense;
use App\Models\VehicleConfigSetting;

class VehicleUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('edit vehicles');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $vehicleId = $this->route('vehicle')->id;

        return [
            'company_category_id' => 'required|exists:company_categories,id',
            'company_id' => 'required|exists:companies,id',
            'license_number' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Z0-9\-]+$/',
                Rule::unique('vehicles', 'license_number')->ignore($vehicleId),
            ],
            // 可為號碼字串或直接使用 revoked 行牌的 ID（前端目前傳號碼字串，保留相容性）
            'replacement_license' => 'nullable|max:20',
            'vehicle_type' => 'nullable|string|max:50',
            'owner_name' => 'required|string|max:100',
            'address' => 'nullable|string|max:500',
            'brand' => 'nullable|string|max:50',
            'manufacture_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'manufacture_month' => 'nullable|integer|min:1|max:12',
            'vehicle_form' => 'nullable|string|max:50',
            'engine_displacement' => 'nullable|numeric|min:0|max:99999.99',
            'fuel_type' => 'nullable|string|max:20',
            'vehicle_model' => 'nullable|string|max:100',
            'vehicle_style' => 'nullable|string|max:100',
            'engine_number' => 'nullable|string|max:50',
            'chassis_number' => 'nullable|string|max:50',
            'passenger_capacity' => 'nullable|integer|min:0|max:255',
            'vehicle_color' => 'nullable|string|max:30',
            'license_issue_year_republic' => 'nullable|integer|min:1|max:200',
            'license_issue_month' => 'nullable|integer|min:1|max:12',
            'license_issue_day' => 'nullable|integer|min:1|max:31',
            'inspection_year_republic' => 'nullable|integer|min:1|max:200',
            'inspection_month' => 'nullable|integer|min:1|max:12',
            'inspection_day' => 'nullable|integer|min:1|max:31',
            'property_type' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'fleet_name' => 'nullable|string|max:100',
            'fleet_category' => 'nullable|string|max:50',
            'fleet_number' => 'nullable|string|max:20',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'company_category_id.required' => '公司類別為必填項目',
            'company_category_id.exists' => '選擇的公司類別不存在',
            'company_id.required' => '公司名稱為必填項目',
            'company_id.exists' => '選擇的公司不存在',
            'license_number.required' => '車牌號碼為必填項目',
            'license_number.unique' => '此車牌號碼已存在',
            'license_number.regex' => '車牌號碼格式不正確',
            'owner_name.required' => '車主名稱為必填項目',
            'owner_name.max' => '車主名稱不能超過100個字元',
            'manufacture_year.min' => '出廠年份不能小於1900年',
            'manufacture_year.max' => '出廠年份不能超過' . (date('Y') + 1) . '年',
            'manufacture_month.min' => '出廠月份必須在1-12之間',
            'manufacture_month.max' => '出廠月份必須在1-12之間',
            'engine_displacement.numeric' => '排氣量必須為數字',
            'engine_displacement.min' => '排氣量不能為負數',
            'passenger_capacity.integer' => '載運人數必須為整數',
            'passenger_capacity.min' => '載運人數不能為負數',
            'license_issue_year_republic.min' => '發照年份不能小於民國1年',
            'license_issue_year_republic.max' => '發照年份不能超過民國200年',
            'license_issue_month.min' => '發照月份必須在1-12之間',
            'license_issue_month.max' => '發照月份必須在1-12之間',
            'license_issue_day.min' => '發照日期必須在1-31之間',
            'license_issue_day.max' => '發照日期必須在1-31之間',
            'inspection_year_republic.min' => '檢驗年份不能小於民國1年',
            'inspection_year_republic.max' => '檢驗年份不能超過民國200年',
            'inspection_month.min' => '檢驗月份必須在1-12之間',
            'inspection_month.max' => '檢驗月份必須在1-12之間',
            'inspection_day.min' => '檢驗日期必須在1-31之間',
            'inspection_day.max' => '檢驗日期必須在1-31之間',
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
            $replacementLicense = $this->input('replacement_license');
            $companyId = $this->input('company_id');

            if (!empty($replacementLicense) && $companyId) {
                // 檢查是否為有效的替補車號 ID
                if (is_numeric($replacementLicense)) {
                    $vehicleLicense = VehicleLicense::where('id', $replacementLicense)
                        ->where('company_id', $companyId)
                        ->where('status', 'revoked')
                        ->first();

                    if (!$vehicleLicense) {
                        $validator->errors()->add('replacement_license', '選擇的替補車號無效或不屬於該公司');
                    }
                } else {
                    // 向後相容：檢查字串格式的替補車號
                    $vehicleLicense = VehicleLicense::where(function($query) use ($replacementLicense) {
                        $query->where('previous_license_number', $replacementLicense)
                              ->orWhere('license_number', $replacementLicense);
                    })
                    ->where('company_id', $companyId)
                    ->where('status', 'revoked')
                    ->first();

                    if (!$vehicleLicense) {
                        $validator->errors()->add('replacement_license', '選擇的替補車號無效或不屬於該公司');
                    }
                }
            }
        });
    }

    /**
     * Get custom attribute names for validation errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'company_category_id' => '公司類別',
            'company_id' => '公司名稱',
            'license_number' => '車牌號碼',
            'replacement_license' => '替補車號',
            'vehicle_type' => '車輛類型',
            'owner_name' => '車主名稱',
            'address' => '地址',
            'brand' => '車輛廠牌',
            'manufacture_year' => '出廠年',
            'manufacture_month' => '出廠月',
            'vehicle_form' => '車輛形式',
            'engine_displacement' => '排氣量',
            'fuel_type' => '燃料種類',
            'vehicle_model' => '車輛款式',
            'vehicle_style' => '車輛樣式',
            'engine_number' => '引擎號碼',
            'chassis_number' => '車身號碼',
            'passenger_capacity' => '載運人數',
            'vehicle_color' => '車輛顏色',
            'license_issue_year_republic' => '發照年',
            'license_issue_month' => '發照月',
            'license_issue_day' => '發照日',
            'inspection_year_republic' => '檢驗年',
            'inspection_month' => '檢驗月',
            'inspection_day' => '檢驗日',
            'property_type' => '產權類別',
            'notes' => '備註',
            'fleet_name' => '車隊名稱',
            'fleet_category' => '車隊類別',
            'fleet_number' => '車隊編號',
        ];
    }
}
