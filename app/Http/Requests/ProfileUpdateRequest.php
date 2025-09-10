<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required', 'string', 'max:50',
                Rule::unique(User::class, 'username')->ignore($this->user()->id),
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'birth_date' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:male,female,other'],
            'mobile_phone' => ['required', 'regex:/^09\d{8}$/'],
            'home_phone' => ['nullable', 'regex:/^0[2-8]\d{7,8}$/'],
            'address' => ['nullable', 'string', 'max:500'],
        ];
    }
}
