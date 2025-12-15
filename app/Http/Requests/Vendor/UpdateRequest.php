<?php

namespace App\Http\Requests\Vendor;

use App\Models\Vendor;
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
            ...Vendor::RULES,
            'code' => 'required|string|max:255|unique:vendors,code,' . $this->route('vendor')->id . ',id',
        ];
    }

    public function messages()
    {
        return Vendor::MESSAGES;
    }
}
