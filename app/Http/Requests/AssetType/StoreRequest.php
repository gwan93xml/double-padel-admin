<?php

namespace App\Http\Requests\AssetType;

use App\Models\AssetType;
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
        return AssetType::RULES;
    }

    public function messages(): array
    {
        return AssetType::MESSAGES;
    }
}
