<?php

namespace App\Http\Requests\CashTransfer;

use App\Models\CashTransfer;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
        return CashTransfer::RULES;
    }

    public function messages(): array
    {
        return CashTransfer::MESSAGES;
    }
}
