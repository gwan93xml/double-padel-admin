<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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

            'code' => 'required|string|max:255|unique:companies,code',
            'name' => 'required|string|max:255|unique:companies,name',
            'logo' => 'nullable|image|max:2048',
            'city' => 'nullable|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'sale_code' => 'nullable|string|max:255',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
        ];
    }
}
