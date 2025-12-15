<?php

namespace App\Http\Requests\CashTransaction;

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
            'bank_account_id' => 'required',
            'chart_of_account_id' => 'required',
            'type' => 'required|in:in,out',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'description' => 'required',
        ];
    }
}
