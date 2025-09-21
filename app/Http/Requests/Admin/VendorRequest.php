<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class VendorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('vendor.create') || auth()->user()->can('vendor.edit');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:1000',
            'service_content' => 'nullable|string|max:2000',
            'note' => 'nullable|string|max:2000',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.max' => '廠商名稱不得超過 255 個字元',
            'phone.max' => '廠商電話不得超過 50 個字元',
            'address.max' => '廠商地址不得超過 1000 個字元',
            'service_content.max' => '服務內容不得超過 2000 個字元',
            'note.max' => '備註不得超過 2000 個字元',
        ];
    }
}
