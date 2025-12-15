<?php

namespace App\Http\Requests\AssetType;

use App\Models\AssetType;
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
        return [
            ...AssetType::RULES,
            'name' => 'required|string|max:255|unique:asset_types,name,' . $this->assetType->id,
        ];
    }


    public function messages(): array
    {
        return AssetType::MESSAGES;
    }
}
