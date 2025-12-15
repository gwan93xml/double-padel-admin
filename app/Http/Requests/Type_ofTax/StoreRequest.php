<?php

namespace App\Http\Requests\Type_ofTax;

use App\Models\Type_ofTax;
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
        return Type_ofTax::RULES;
    }

    public function messages()
    {
        return Type_ofTax::MESSAGES;
    }
}
