<?php

namespace App\Http\Requests\Chart_ofAccount;

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
            'parent_id' => 'nullable',
            'code' => 'required|string|max:255|unique:chart_of_accounts,code',
            'name' => 'required|string|max:255|unique:chart_of_accounts,name',
            'type' => 'nullable|string',
            'sub_type' => 'nullable|string',
            'dr_cr' => 'nullable|string',
        ];
    }
}
