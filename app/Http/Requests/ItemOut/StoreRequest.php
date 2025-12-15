<?php

namespace App\Http\Requests\ItemOut;

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
            'date' => 'required|date',
            'warehouse_id' => 'required',
            'division_id' => 'required',
            'chart_of_account_id' => 'nullable',
            'remarks' => 'nullable|string|max:255',
            'items' => 'required|array',
            'items.*.item_id' => 'required',
            'items.*.quantity' => 'required',
            'items.*.remarks' => 'nullable',
            'items.*.unit' => 'required',
        ];
    }
}
