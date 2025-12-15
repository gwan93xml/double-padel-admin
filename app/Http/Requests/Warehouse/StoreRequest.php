<?php

namespace App\Http\Requests\Warehouse;

use App\Models\Warehouse;
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
        return [...Warehouse::RULES];
    }
    
    public function messages()
    {
        return Warehouse::MESSAGES;
    }
}
